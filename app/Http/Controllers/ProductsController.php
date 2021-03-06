<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
        $builder = Product::query()->where('on_sale',true);
        if ($search = $request->input('search','')) {
            $like = '%'.$search.'%';
            $builder->where(function ($query) use ($like) {
                $query->where('title','like',$like)
                      ->orWhere('description','like',$like)
                      ->orwhereHas('skus',function ($query) use ($like) {
                          $query->where('title','like',$like)
                                ->orWhere('description','like',$like);
                      });
            });
        }
        if ($order = $request->input('order', '')) {
            // 是否是以 _asc 或者 _desc 结尾
            if (preg_match('/^(.+)_(asc|desc)$/', $order, $m)) {
                // 如果字符串的开头是这 3 个字符串之一，说明是一个合法的排序值
                if (in_array($m[1], ['price', 'sold_count', 'rating'])) {
                    // 根据传入的排序值来构造排序参数
                    $builder->orderBy($m[1], $m[2]);
                }
            }
        }
        $filters = [
          'search' => $search,
          'order'  => $order
        ];
        $products = $builder->paginate(16);
       return view('products.index',compact('products','filters'));
    }
    public function show(Request $request,Product $product)
    {
        if (!$product->on_sale) {
            throw new InvalidRequestException('该商品还未上架');
        }
        return view('products.show',compact('product'));
    }

    public function favor(Request $request,Product $product)
    {
        $user = $request->user();
        if ($user->favoriteProducts()->find($product->id)) {
            return [];
        }
        $user->favoriteProducts()->attach($product);
        return [];
    }

    public function disfavor(Request $request,Product $product)
    {
        $user = $request->user();
        $user->favoriteProducts()->detach($product);
        return [];
    }
    public function favorites(Request $request)
    {
        $products = $request->user()->favoriteProducts()->paginate(16);
        return view('products.favorites',compact('products'));
    }
}

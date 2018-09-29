<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCartRequest;
use App\Models\CartItem;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function add (AddCartRequest $request)
    {
        $user = $request->user();
        $skuId = $request->input('sku_id');
        $amount = $request->input('amount');

        if ($cart = CartItem::query()->where('product_sku_id',$skuId)->first()) {
            $cart->update([
                'amount' => $cart->amount + $amount,
            ]);
        } else {
            //创建一个新的购物车记录
            $cart = new CartItem([
                'amount' => $amount
            ]);
            $cart->user()->associate($user);
            $cart->productSku()->associate($skuId);
            $cart->save();
        }
    }
}

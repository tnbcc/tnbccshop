<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Notifications\EmailVerificationNotification;
class EmailVerificationController extends Controller
{
    public function verify(Request $request)
    {
      $token = $request->input('token');
      $email = $request->input('email');

      if (!$token || !$email ) {
          throw new \Exception('验证链接不正确');
      }

      if ($token != \Cache::get('email_verification_'.$email)) {
            throw new \Exception('验证链接不正确或已过期');
      }

      if (!$user = User::where('email',$email)->first()) {
           throw new \Exception('用户不存在');
      }

      \Cache::forget('email_verification_'.$email);

        $user->update(['email_verified' => true]);

        // 最后告知用户邮箱验证成功。
        return view('pages.success', ['msg' => '邮箱验证成功']);

    }

    public function send(Request $request)
    {
        $user = $request->user();
        // 判断用户是否已经激活
        if ($user->email_verified) {
            throw new \Exception('你已经验证过邮箱了');
        }
        // 调用 notify() 方法用来发送我们定义好的通知类
        $user->notify(new EmailVerificationNotification());

        return view('pages.success', ['msg' => '邮件发送成功']);
    }
}

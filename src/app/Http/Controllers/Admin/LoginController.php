<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     $this->middleware('guest:admin')->except('logout');
    // }

    //
    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate(
            [
                // 入力内容のチェック
                'email' => ['required', 'email'],
                'password' => ['required'],
            ],
            [
                'email.required' => 'メールアドレスを入力してください。',
                'email.email' => 'メールアドレスの形式で入力してください。',
                'password.required' => 'パスワードを入力してください。',
            ]
        );

        if (Auth::guard('admin')->attempt($credentials)) {
            $request->session()->regenerate();
            // 管理者ホーム画面へリダイレクト
            return redirect()->intended('/admin/home');
        }

        // return redirect('/admin/login')->withErrors([
        return back()->withErrors([
            'login_error' => 'メールアドレスかパスワードが間違っています。',
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}

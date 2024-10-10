<?php

namespace App\Http\Middleware;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }

    //ログインしていない時にダッシュボードを踏むと、admin/loginへ飛ぶ
    protected function unauthenticated($request, array $guards)
    {
        throw new AuthenticationException(
            'Unauthenticated.',
            $guards,
            $this->redirectToOriginal($request, $guards)
        );
    }

    protected function redirectToOriginal($request, array $guards)
    {
        foreach ($guards as $guard) {
            if ($guard === 'admin') {
                return route('admin.login');
            }
        }
    }
}

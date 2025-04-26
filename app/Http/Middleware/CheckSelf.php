<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckSelf
{
    public function handle(Request $request, Closure $next)
    {
        // Lấy token từ request
        $requestToken = $request->bearerToken();

        $user = Auth::user();

        // Cho phép admin vượt qua kiểm tra
        if ($user->role === 'admin') {
            return $next($request);
        }

        // Kiểm tra xem token trong request có khớp với token của người dùng hiện tại không
        if ($requestToken && $user->api_token != $requestToken) {
            return response()->json([
                'message' => 'Bạn không có quyền thực hiện hành động này!',
            ], 403);
        }

        return $next($request);
    }
}
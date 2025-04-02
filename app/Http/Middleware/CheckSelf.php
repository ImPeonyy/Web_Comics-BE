<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckSelf
{
    public function handle(Request $request, Closure $next)
    {
        // Lấy ID từ route (ví dụ: users/{id})
        $id = $request->route('id') ?? $request->input('id');

        $user = Auth::user();

        // Cho phép admin vượt qua kiểm tra
        if ($user->role === 'admin') {
            return $next($request);
        }

        // Kiểm tra xem ID trong request có trùng với ID của người dùng hiện tại không
        if ($id && $user->id != $id) {
            return response()->json([
                'message' => 'Bạn không có quyền thực hiện hành động này!',
            ], 403);
        }

        return $next($request);
    }
}
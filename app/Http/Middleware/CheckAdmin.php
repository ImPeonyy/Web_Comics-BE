<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::user()->role === 'admin') {
            return $next($request);
        }

        return response()->json([
            'message' => 'Bạn không có quyền truy cập (chỉ dành cho admin)',
        ], 403);
    }
}
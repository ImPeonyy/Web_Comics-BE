<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Favorite;
use App\Models\User;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    // Lấy danh sách favorites của người dùng hiện tại (dùng token)
    public function getCurrentUserFavorites()
    {
        $user = Auth::user();

        $favorites = Favorite::where('user_id', $user->id)
                            ->with('comic')
                            ->get();

        return response()->json([
            'message' => 'Danh sách truyện yêu thích của bạn',
            'favorites' => $favorites,
        ], 200);
    }

    // Thêm truyện vào danh sách yêu thích của người dùng hiện tại
    public function addFavorite(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'comic_id' => 'required|exists:comics,id',
        ]);

        // Kiểm tra xem truyện đã có trong danh sách yêu thích chưa
        $existingFavorite = Favorite::where('user_id', $user->id)
                                   ->where('comic_id', $validated['comic_id'])
                                   ->first();

        if ($existingFavorite) {
            return response()->json([
                'message' => 'Truyện này đã có trong danh sách yêu thích',
            ], 409); // 409 Conflict
        }

        $favorite = Favorite::create([
            'user_id' => $user->id,
            'comic_id' => $validated['comic_id'],
        ]);

        return response()->json([
            'message' => 'Đã thêm truyện vào danh sách yêu thích',
            'favorite' => $favorite,
        ], 201);
    }

    // Xóa truyện khỏi danh sách yêu thích của người dùng hiện tại
    public function removeFavorite(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'comic_id' => 'required|exists:comics,id',
        ]);

        $favorite = Favorite::where('user_id', $user->id)
                           ->where('comic_id', $validated['comic_id'])
                           ->first();

        if (!$favorite) {
            return response()->json([
                'message' => 'Truyện này không có trong danh sách yêu thích của bạn',
            ], 404);
        }

        $favorite->delete();

        return response()->json([
            'message' => 'Đã xóa truyện khỏi danh sách yêu thích',
        ], 200);
    }
}
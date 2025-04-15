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
    public function getCurrentUserFavorites(Request $request)
    {
        $user = Auth::user();

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $favorites = Favorite::where('user_id', $user->id)
            ->with([
                'comic' => function ($query) {
                    $query->with([
                        'statistics',
                        'genres',
                        'chapters' => function ($query) {
                            $query->orderBy('created_at', 'desc')->take(3);
                        },
                        'favorites'
                    ]);
                }
            ])
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => $favorites->items(),
            'pagination' => [
                'current_page' => $favorites->currentPage(),
                'last_page' => $favorites->lastPage(),
                'per_page' => $favorites->perPage(),
                'total' => $favorites->total(),
                'from' => $favorites->firstItem(),
                'to' => $favorites->lastItem()
            ]
        ], 200);
    }

    public function getHomePageFavorites(Request $request)
    {
        $user = Auth::user();

        $favorites = Favorite::where('user_id', $user->id)
            ->with([
                'comic' => function ($query) {
                    $query->with([
                        'statistics',
                        'genres',
                        'favorites',
                        'chapters' => function ($query): void {
                            $query->orderBy('chapter_order', 'desc')->take(1);
                        },
                    ]);
                }
            ])
            ->get();

        return response()->json([
            'data' => $favorites,
        ], 200);
    }

    // Thêm truyện vào danh sách yêu thích của người dùng hiện tại
    public function addFavorite($comicId)
    {
        $user = Auth::user();

        // Kiểm tra xem truyện đã có trong danh sách yêu thích chưa
        $existingFavorite = Favorite::where('user_id', $user->id)
            ->where('comic_id', $comicId)
            ->first();

        if ($existingFavorite) {
            return response()->json([
                'message' => 'Truyện này đã có trong danh sách yêu thích',
            ], 409); // 409 Conflict
        }

        $favorite = Favorite::create([
            'user_id' => $user->id,
            'comic_id' => $comicId,
        ]);

        return response()->json([
            'message' => 'Đã thêm truyện vào danh sách yêu thích',
            'favorite' => $favorite,
        ], 201);
    }

    // Xóa truyện khỏi danh sách yêu thích của người dùng hiện tại
    public function removeFavorite($comicId)
    {
        $user = Auth::user();

        $favorite = Favorite::where('user_id', $user->id)
            ->where('comic_id', $comicId)
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

    // Kiểm tra trạng thái yêu thích của một truyện cho người dùng cụ thể
    public function getFavoriteByComicAndUser(Request $request)
    {
        $validated = $request->validate([
            'comic_id' => 'required|exists:comics,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $favorite = Favorite::where('user_id', $validated['user_id'])
            ->where('comic_id', $validated['comic_id'])
            ->first();

        return response()->json([
            'is_favorite' => $favorite ? true : false,
            'favorite' => $favorite,
        ], 200);
    }
}
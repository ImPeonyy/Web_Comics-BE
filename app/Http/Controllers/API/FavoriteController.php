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

        $perPage = $request->input('per_page', 12);
        $page = $request->input('page', 1);

        $favorites = Favorite::where('user_id', $user->id)
            ->with([
                'comic' => function ($query) {
                    $query->with([
                        'genres',
                        'chapters' => function ($query) {
                            $query->orderBy('chapter_order', 'desc')->take(3);
                        }
                    ])
                        ->selectRaw('comics.*, COALESCE(SUM(statistics.view_count), 0) as totalViews')
                        ->leftJoin('statistics', 'comics.id', '=', 'statistics.comic_id')
                        ->groupBy('comics.id');
                }
            ])
            ->paginate($perPage, ['*'], 'page', $page);

        // Thêm trường isFav vào mỗi comic
        $favorites->getCollection()->transform(function ($favorite) use ($user) {
            $favorite->comic->isFav = true; // Vì đây là danh sách yêu thích nên mặc định là true
            return $favorite;
        });

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
                        'genres',
                        'chapters' => function ($query) {
                            $query->orderBy('chapter_order', 'desc')->take(1);
                        }
                    ])
                        ->selectRaw('comics.*, COALESCE(SUM(statistics.view_count), 0) as totalViews')
                        ->leftJoin('statistics', 'comics.id', '=', 'statistics.comic_id')
                        ->groupBy('comics.id');
                }
            ])
            ->get();

        // Thêm trường isFav vào mỗi comic
        $favorites->transform(function ($favorite) use ($user) {
            $favorite->comic->isFav = true; // Vì đây là danh sách yêu thích nên mặc định là true
            return $favorite;
        });

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
}
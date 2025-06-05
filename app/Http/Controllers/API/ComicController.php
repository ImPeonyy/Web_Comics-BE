<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Comic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class ComicController extends Controller
{
    // Lấy tất cả comics
    public function getAllComics(Request $request)
    {
        $perPage = $request->input('per_page', 12); // Mặc định 12 items mỗi trang
        $page = $request->input('page', 1); // Mặc định trang 1

        $user = null;
        $token = request()->bearerToken();
        if ($token) {
            $user = \Laravel\Sanctum\PersonalAccessToken::findToken($token)?->tokenable;
        }
        $comics = Comic::with([
            'genres',
            'chapters' => function ($query) {
                $query->orderBy('chapter_order', 'desc')->take(3);
            }
        ])
            ->selectRaw('comics.*, COALESCE(SUM(statistics.view_count), 0) as totalViews')
            ->leftJoin('statistics', 'comics.id', '=', 'statistics.comic_id')
            ->groupBy('comics.id')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        if ($user) {
            $comics->getCollection()->transform(function ($comic) use ($user) {
                $comic->isFav = $comic->favorites()->where('user_id', $user->id)->exists();
                return $comic;
            });
        }
        return response()->json([
            'data' => $comics->items(),
            'pagination' => [
                'current_page' => $comics->currentPage(),
                'last_page' => $comics->lastPage(),
                'per_page' => $comics->perPage(),
                'total' => $comics->total(),
                'from' => $comics->firstItem(),
                'to' => $comics->lastItem()
            ]
        ], 200);
    }

    // Lấy 1 comic theo ID
    public function getComicById($id)
    {
        $user = null;
        $token = request()->bearerToken();
        if ($token) {
            $user = \Laravel\Sanctum\PersonalAccessToken::findToken($token)?->tokenable;
        }
        $comic = Comic::with(['genres'])
            ->selectRaw('comics.*, COALESCE(SUM(statistics.view_count), 0) as totalViews')
            ->leftJoin('statistics', 'comics.id', '=', 'statistics.comic_id')
            ->where('comics.id', $id)
            ->groupBy('comics.id')
            ->first();

        if ($user) {
            $comic->isFav = $comic->favorites()->where('user_id', $user->id)->exists();
        }

        if (!$comic) {
            return response()->json([
                'message' => 'Comic không tồn tại'
            ], 404);
        }

        return response()->json([
            'data' => $comic,
        ], 200);
    }

    // Filter Comics với các tham số
    public function filterComics(Request $request)
    {
        $user = null;
        $token = request()->bearerToken();
        if ($token) {
            $user = \Laravel\Sanctum\PersonalAccessToken::findToken($token)?->tokenable;
        }

        $perPage = $request->input('per_page', 12); // Mặc định 12 items mỗi trang
        $page = $request->input('page', 1); // Mặc định trang 1

        $query = Comic::with([
            'genres',
            'chapters' => function ($query) {
                $query->orderBy('chapter_order', 'desc')->take(3);
            }
        ])
            ->selectRaw('comics.*, COALESCE(SUM(statistics.view_count), 0) as totalViews')
            ->leftJoin('statistics', 'comics.id', '=', 'statistics.comic_id')
            ->groupBy('comics.id');

        // Kiểm tra nếu không có param nào thì trả về tất cả comics
        if (!$request->hasAny(['keyword', 'genres', 'status', 'sortBy'])) {
            $comics = $query->orderBy('created_at', 'asc')->paginate($perPage, ['*'], 'page', $page);

            if ($user) {
                $comics->getCollection()->transform(function ($comic) use ($user) {
                    $comic->isFav = $comic->favorites()->where('user_id', $user->id)->exists();
                    return $comic;
                });
            }

            return response()->json([
                'data' => $comics->items(),
                'pagination' => [
                    'current_page' => $comics->currentPage(),
                    'last_page' => $comics->lastPage(),
                    'per_page' => $comics->perPage(),
                    'total' => $comics->total(),
                    'from' => $comics->firstItem(),
                    'to' => $comics->lastItem()
                ]
            ], 200);
        }

        // Tìm kiếm theo keyword
        if ($request->has('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%")
                    ->orWhere('author', 'like', "%{$keyword}%");
            });
        }

        // Lọc theo genre
        if ($request->has('genres')) {
            $query->whereHas('genres', function ($q) use ($request) {
                $q->where('id', $request->genres);
            });
        }

        // Lọc theo status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Sắp xếp
        if ($request->has('sortBy')) {
            $sortBy = $request->sortBy;
            $direction = $request->has('direction') ? $request->direction : 'asc';

            switch ($sortBy) {
                case 'name':
                    $query->orderBy('title', $direction);
                    break;
                case 'created_at':
                    $query->orderBy('created_at', $direction);
                    break;
                case 'updated_at':
                    $query->orderBy('updated_at', $direction);
                    break;
                case 'views':
                    $query->orderBy('totalViews', $direction);
                    break;
                default:
                    $query->orderBy('created_at', 'asc');
            }
        } else {
            $query->orderBy('created_at', 'asc');
        }

        // Lấy kết quả
        $comics = $query->paginate($perPage, ['*'], 'page', $page);

        if ($user) {
            $comics->getCollection()->transform(function ($comic) use ($user) {
                $comic->isFav = $comic->favorites()->where('user_id', $user->id)->exists();
                return $comic;
            });
        }

        return response()->json([
            'data' => $comics->items(),
            'pagination' => [
                'current_page' => $comics->currentPage(),
                'last_page' => $comics->lastPage(),
                'per_page' => $comics->perPage(),
                'total' => $comics->total(),
                'from' => $comics->firstItem(),
                'to' => $comics->lastItem()
            ]
        ], 200);
    }

    // Lấy ngẫu nhiên 1 truyện với 3 chapter mới nhất
    public function getRandomComic()
    {
        $user = null;
        $token = request()->bearerToken();
        if ($token) {
            $user = \Laravel\Sanctum\PersonalAccessToken::findToken($token)?->tokenable;
        }

        $comic = Comic::with(['genres'])
            ->selectRaw('comics.*, COALESCE(SUM(statistics.view_count), 0) as totalViews')
            ->leftJoin('statistics', 'comics.id', '=', 'statistics.comic_id')
            ->groupBy('comics.id')
            ->inRandomOrder()
            ->first();

        if (!$comic) {
            return response()->json([
                'data' => null
            ], 200);
        }

        if ($user) {
            $comic->isFav = $comic->favorites()->where('user_id', $user->id)->exists();
        }

        return response()->json([
            'data' => $comic
        ], 200);
    }

    public function searchOnChange(Request $request)
    {
        $user = null;
        $token = request()->bearerToken();
        if ($token) {
            $user = \Laravel\Sanctum\PersonalAccessToken::findToken($token)?->tokenable;
        }

        $query = Comic::with(['genres'])
            ->selectRaw('comics.*, COALESCE(SUM(statistics.view_count), 0) as totalViews')
            ->leftJoin('statistics', 'comics.id', '=', 'statistics.comic_id')
            ->groupBy('comics.id');

        $keyword = $request->keyword;
        $query->where(function ($q) use ($keyword) {
            $q->where('title', 'like', "%{$keyword}%")
                ->orWhere('description', 'like', "%{$keyword}%")
                ->orWhere('author', 'like', "%{$keyword}%");
        });

        $comics = $query->get();

        if ($user) {
            $comics->transform(function ($comic) use ($user) {
                $comic->isFav = $comic->favorites()->where('user_id', $user->id)->exists();
                return $comic;
            });
        }

        return response()->json([
            'data' => $comics
        ], 200);
    }

    // Lấy tất cả comics cho admin
    public function getAllComicsAdmin(Request $request)
    {
        $comics = Comic::with([
            'genres',
            'chapters' => function ($query) {
                $query->orderBy('chapter_order', 'desc');
            }
        ])
            ->selectRaw('comics.*, 
                COALESCE(SUM(statistics.view_count), 0) as totalViews,
                COUNT(DISTINCT favorites.id) as totalFavorites,
                COUNT(DISTINCT chapters.id) as totalChapters')
            ->leftJoin('statistics', 'comics.id', '=', 'statistics.comic_id')
            ->leftJoin('favorites', 'comics.id', '=', 'favorites.comic_id')
            ->leftJoin('chapters', 'comics.id', '=', 'chapters.comic_id')
            ->groupBy('comics.id')
            ->orderBy('created_at', 'desc')
            ->get();

        // Lấy chapter mới nhất cho mỗi truyện
        $comics->transform(function ($comic) {
            $comic->latestChapter = $comic->chapters->first();
            return $comic;
        });

        return response()->json([
            'data' => $comics
        ], 200);
    }

    // Cập nhật thông tin truyện
    public function updateComic(Request $request, $id)
    {
        // Kiểm tra xem truyện có tồn tại không
        $comic = Comic::find($id);
        if (!$comic) {
            return response()->json([
                'message' => 'Truyện không tồn tại'
            ], 404);
        }

        // Validate dữ liệu đầu vào
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'string',
            'author' => 'required|string|max:255',
            'status' => 'required|in:Ongoing,Completed,Hiatus',
            'genres' => 'required|array',
            'genres.*' => 'exists:genres,id'
        ]);

        // Cập nhật thông tin cơ bản của truyện
        $comic->update([
            'title' => $request->title,
            'description' => $request->description,
            'author' => $request->author,
            'status' => $request->status
        ]);

        // Cập nhật danh sách thể loại
        $comic->genres()->sync($request->genres);

        // Lấy lại thông tin truyện đã cập nhật
        $updatedComic = Comic::with(['genres'])->find($id);

        return response()->json([
            'message' => 'Cập nhật truyện thành công',
            'data' => $updatedComic
        ], 200);
    }

    // Xóa truyện
    public function deleteComic($id)
    {
        $comic = Comic::find($id);
        if (!$comic) {
            return response()->json([
                'message' => 'Truyện không tồn tại'
            ], 404);
        }

        $comic->delete();

        return response()->json([
            'message' => 'Xóa truyện thành công'
        ], 200);
    }
}

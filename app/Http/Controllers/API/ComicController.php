<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Comic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class ComicController extends Controller
{
    // Lấy tất cả comics
    public function index(Request $request)
    {

        $perPage = $request->input('per_page', 10); // Mặc định 10 items mỗi trang
        $page = $request->input('page', 1); // Mặc định trang 1

        $comics = Comic::with([
            'statistics',
            'genres',
            'chapters' => function ($query) {
                $query->orderBy('created_at', 'desc')->take(3);
            }
        ])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

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

    // Lấy tất cả comics với auth
    public function indexAuth(Request $request)
    {
        $user = Auth::user();
        $perPage = $request->input('per_page', 10); // Mặc định 10 items mỗi trang
        $page = $request->input('page', 1); // Mặc định trang 1

        $comics = Comic::with([
            'statistics',
            'favorites',
            'genres',
            'chapters' => function ($query) {
                $query->orderBy('created_at', 'desc')->take(3);
            }
        ])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

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
    public function show($id)
    {
        $comic = Comic::with(['statistics', 'favorites', 'genres'])->find($id);

        if (!$comic) {
            return response()->json([
                'message' => 'Comic không tồn tại'
            ], 404);
        }

        return response()->json([
            'data' => $comic
        ], 200);
    }

    // Lấy comics theo status
    public function getByStatus($status)
    {
        // Kiểm tra status hợp lệ
        if (!in_array($status, ['ongoing', 'completed'])) {
            return response()->json([
                'message' => 'Status không hợp lệ. Chỉ chấp nhận "ongoing" hoặc "completed"'
            ], 400);
        }

        $comics = Comic::where('status', $status)->get();

        if ($comics->isEmpty()) {
            return response()->json([
                'message' => "Không có comic nào với status '$status'"
            ], 404);
        }

        return response()->json([
            'message' => "Danh sách comics với status '$status'",
            'data' => $comics
        ], 200);
    }

    // Filter Comics với các tham số
    public function filterComics(Request $request)
    {
        $query = Comic::query();

        // Kiểm tra nếu không có param nào thì trả về tất cả comics
        if (!$request->hasAny(['keyword', 'genres', 'status', 'sortBy'])) {
            $comics = Comic::with([
                'statistics',
                'favorites',
                'genres',
                'chapters' => function ($query) {
                    $query->orderBy('created_at', 'desc')->take(3);
                }
            ])->orderBy('created_at', 'desc')->get();

            return response()->json([
                'data' => $comics
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
            $direction = $request->has('direction') ? $request->direction : 'desc';

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
                    $query->withCount('statistics as views')
                        ->orderBy('views', $direction);
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Lấy kết quả với các quan hệ
        $comics = $query->with([
            'statistics',
            'favorites',
            'genres',
            'chapters' => function ($query) {
                $query->orderBy('created_at', 'desc')->take(3);
            }
        ])->get();

        // Kiểm tra nếu không có kết quả
        if ($comics->isEmpty()) {
            return response()->json([
                'data' => []
            ], 200);
        }

        return response()->json([
            'data' => $comics
        ], 200);
    }

    // Lấy ngẫu nhiên 1 truyện với 3 chapter mới nhất
    public function getRandomComic()
    {
        // Lấy ngẫu nhiên 1 truyện với đầy đủ thông tin
        $comic = Comic::with([
            'statistics',
            'favorites',
            'genres',
            'chapters' => function ($query) {
                $query->orderBy('created_at', 'desc')->take(3);
            }
        ])->inRandomOrder()->first();

        if (!$comic) {
            return response()->json([
                'data' => null
            ], 200);
        }

        return response()->json([
            'data' => $comic
        ], 200);
    }

    public function searchOnChange(Request $request)
    {
        $query = Comic::query();
        $keyword = $request->keyword;
        $query->where(function ($q) use ($keyword) {
            $q->where('title', 'like', "%{$keyword}%")
                ->orWhere('description', 'like', "%{$keyword}%")
                ->orWhere('author', 'like', "%{$keyword}%");
        });

        $comics = $query->with([
            'statistics',
            'favorites',
            'genres',
        ])->get();

        return response()->json([
            'data' => $comics
        ], 200);
    }
}
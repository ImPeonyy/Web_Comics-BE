<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\History;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    // Lấy danh sách history của người dùng hiện tại
    public function getChapterHistory(Request $request)
    {
        $user = Auth::user();

        $history = History::where('user_id', $user->id)
            ->get();

        return response()->json([
            'message' => 'Danh sách lịch sử đọc của bạn',
            'history' => $history,
        ], 200);
    }

    public function getCurrentUserHistory(Request $request)
    {
        $user = Auth::user();

        $perPage = $request->input('per_page', 12);
        $page = $request->input('page', 1);

        $history = History::where('user_id', $user->id)
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
                },
                'chapter'
            ])
            ->select('history.*')
            ->whereIn('chapter_id', function ($query) use ($user) {
                $query->selectRaw('MAX(chapter_id)')
                    ->from('history')
                    ->where('user_id', $user->id)
                    ->groupBy('comic_id');
            })
            ->orderBy('updated_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        // Thêm trường isFav vào mỗi comic
        $history->getCollection()->transform(function ($item) use ($user) {
            $item->comic->isFav = $item->comic->favorites()->where('user_id', $user->id)->exists();
            return $item;
        });

        return response()->json([
            'data' => $history->items(),
            'pagination' => [
                'current_page' => $history->currentPage(),
                'last_page' => $history->lastPage(),
                'per_page' => $history->perPage(),
                'total' => $history->total(),
                'from' => $history->firstItem(),
                'to' => $history->lastItem()
            ]
        ], 200);
    }

    public function getHomePageHistory(Request $request)
    {
        $user = Auth::user();

        $history = History::where('user_id', $user->id)
            ->with([
                'comic' => function ($query) {
                    $query->with([
                        'genres'
                    ])
                        ->selectRaw('comics.*, COALESCE(SUM(statistics.view_count), 0) as totalViews')
                        ->leftJoin('statistics', 'comics.id', '=', 'statistics.comic_id')
                        ->groupBy('comics.id');
                },
                'chapter'
            ])
            ->select('history.*')
            ->whereIn('chapter_id', function ($query) use ($user) {
                $query->selectRaw('MAX(chapter_id)')
                    ->from('history')
                    ->where('user_id', $user->id)
                    ->groupBy('comic_id');
            })
            ->orderBy('updated_at', 'desc')
            ->get();

        // Thêm trường isFav vào mỗi comic
        $history->transform(function ($item) use ($user) {
            $item->comic->isFav = $item->comic->favorites()->where('user_id', $user->id)->exists();
            return $item;
        });

        return response()->json([
            'data' => $history,
        ], 200);
    }

    // Thêm chapter vào lịch sử đọc của người dùng hiện tại
    public function addHistory(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'comic_id' => 'required|exists:comics,id',
            'chapter_id' => 'required|exists:chapters,id',
        ]);

        // Kiểm tra xem chapter này đã có trong lịch sử chưa
        $existingHistory = History::where('user_id', $user->id)
            ->where('comic_id', $validated['comic_id'])
            ->where('chapter_id', $validated['chapter_id'])
            ->first();

        if ($existingHistory) {
            // Cập nhật thời gian last_read_at nếu đã tồn tại
            $existingHistory->update(['updated_at' => now()]);
            return response()->json([
                'message' => 'Đã cập nhật lịch sử đọc',
                'history' => $existingHistory,
            ], 200);
        }

        $history = History::create([
            'user_id' => $user->id,
            'comic_id' => $validated['comic_id'],
            'chapter_id' => $validated['chapter_id'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Đã thêm vào lịch sử đọc',
            'history' => $history,
        ], 201);
    }

    // Xóa chapter khỏi lịch sử đọc của người dùng hiện tại
    public function removeHistory(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'comic_id' => 'required|exists:comics,id',
            'chapter_id' => 'required|exists:chapters,id',
        ]);

        $history = History::where('user_id', $user->id)
            ->where('comic_id', $validated['comic_id'])
            ->where('chapter_id', $validated['chapter_id'])
            ->first();

        if (!$history) {
            return response()->json([
                'message' => 'Chapter này không có trong lịch sử đọc của bạn',
            ], 404);
        }

        $history->delete();

        return response()->json([
            'message' => 'Đã xóa chapter khỏi lịch sử đọc',
        ], 200);
    }
}
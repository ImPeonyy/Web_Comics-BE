<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\History;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    // Lấy danh sách history của người dùng hiện tại
    public function getCurrentUserHistory()
    {
        $user = Auth::user();

        $history = History::where('user_id', $user->id)
                         ->with(['comic', 'chapter'])
                         ->orderBy('last_read_at', 'desc') // Sắp xếp theo thời gian đọc gần nhất
                         ->get();

        return response()->json([
            'message' => 'Danh sách lịch sử đọc của bạn',
            'history' => $history,
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
            $existingHistory->update(['last_read_at' => now()]);
            return response()->json([
                'message' => 'Đã cập nhật lịch sử đọc',
                'history' => $existingHistory,
            ], 200);
        }

        $history = History::create([
            'user_id' => $user->id,
            'comic_id' => $validated['comic_id'],
            'chapter_id' => $validated['chapter_id'],
            'last_read_at' => now(),
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
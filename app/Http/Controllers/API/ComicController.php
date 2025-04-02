<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Comic;
use Illuminate\Http\Request;

class ComicController extends Controller
{
    // Lấy tất cả comics
    public function index()
    {
        $comics = Comic::all();
        return response()->json([
            'data' => $comics
        ], 200);
    }

    // Lấy 1 comic theo ID
    public function show($id)
    {
        $comic = Comic::find($id);

        if (!$comic) {
            return response()->json([
                'message' => 'Comic không tồn tại'
            ], 404);
        }

        return response()->json([
            'message' => 'Thông tin comic',
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

    // Các phương thức khác từ --api (store, update, destroy) không cần thiết nên tôi bỏ qua
}
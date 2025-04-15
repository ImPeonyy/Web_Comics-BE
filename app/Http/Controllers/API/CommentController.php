<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class CommentController extends Controller
{
    public function getCommentsByComic($comicId)
    {
        try {
            $comments = Comment::with('user')
                ->where('comic_id', $comicId)
                ->orderBy('created_at', 'desc')
                ->get();

            $commentCount = $comments->count();

            return response()->json([
                'data' => [
                    'comments' => $comments,
                    'total_comments' => $commentCount
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra khi lấy danh sách bình luận'
            ], 500);
        }
    }

    public function postComment(Request $request)
    {
        try {
            $validated = $request->validate([
                'comic_id' => 'required|exists:comics,id',
                'content' => 'required|string|min:1|max:1000'
            ]);

            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Bạn cần đăng nhập để bình luận'
                ], 401);
            }

            $comment = new Comment();
            $comment->user_id = $user->id;
            $comment->comic_id = $validated['comic_id'];
            $comment->content = $validated['content'];
            $comment->created_at = now();
            $comment->updated_at = now();
            $comment->save();

            $comment->load('user');

            return response()->json([
                'status' => 'success',
                'message' => 'Bình luận đã được gửi thành công',
                'data' => $comment
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra khi gửi bình luận'
            ], 500);
        }
    }

}
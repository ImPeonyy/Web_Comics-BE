<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index() { return response()->json(Comment::all()); }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'comic_id' => 'required|exists:comics,id',
            'content' => 'required|string',
        ]);
        $comment = Comment::create($validated);
        return response()->json($comment, 201);
    }

    public function show($id) { return response()->json(Comment::findOrFail($id)); }

    public function update(Request $request, $id)
    {
        $comment = Comment::findOrFail($id);
        $validated = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'comic_id' => 'sometimes|exists:comics,id',
            'content' => 'sometimes|string',
        ]);
        $comment->update($validated);
        return response()->json($comment);
    }

    public function destroy($id) { Comment::findOrFail($id)->delete(); return response()->json(null, 204); }
}
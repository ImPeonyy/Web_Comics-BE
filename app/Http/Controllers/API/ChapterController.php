<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use Illuminate\Http\Request;

class ChapterController extends Controller
{
    public function index() { return response()->json(Chapter::all()); }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'comic_id' => 'required|exists:comics,id',
            'chapter_order' => 'required|numeric',
            'title' => 'required|string|max:255',
        ]);
        $chapter = Chapter::create($validated);
        return response()->json($chapter, 201);
    }

    public function show($id) { return response()->json(Chapter::findOrFail($id)); }

    public function update(Request $request, $id)
    {
        $chapter = Chapter::findOrFail($id);
        $validated = $request->validate([
            'comic_id' => 'sometimes|exists:comics,id',
            'chapter_order' => 'sometimes|numeric',
            'title' => 'sometimes|string|max:255',
        ]);
        $chapter->update($validated);
        return response()->json($chapter);
    }

    public function destroy($id) { Chapter::findOrFail($id)->delete(); return response()->json(null, 204); }
}
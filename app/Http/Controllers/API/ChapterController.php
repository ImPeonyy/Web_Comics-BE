<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use Illuminate\Http\Request;

class ChapterController extends Controller
{
    public function index()
    {
        return response()->json(Chapter::all());
    }

    public function getChaptersByComicId($comicId)
    {
        $chapters = Chapter::where('comic_id', $comicId)
            ->orderBy('chapter_order', 'asc')
            ->get();

        return response()->json($chapters);
    }
}
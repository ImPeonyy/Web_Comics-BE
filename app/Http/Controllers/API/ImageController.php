<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Image;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    public function getImagesByChapterId($chapterId)
    {
        $images = Image::where('chapter_id', $chapterId)
            ->orderBy('image_order', 'asc')
            ->get();

        return response()->json($images);
    }
}
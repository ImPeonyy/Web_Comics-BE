<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use Illuminate\Http\Request;


class GenreController extends Controller
{
    /**
     * Lấy danh sách tất cả thể loại
     */
    public function index()
    {
        $genres = Genre::all();
        return response()->json([
            'data' => $genres
        ], 200);
    }

}

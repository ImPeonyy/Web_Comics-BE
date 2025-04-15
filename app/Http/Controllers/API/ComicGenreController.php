<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ComicGenre;
use Illuminate\Http\Request;

class ComicGenreController extends Controller
{
    public function getComicsByGenre($genreId, Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $comics = ComicGenre::with([
            'comic' => function ($query) {
                $query->with([
                    'statistics',
                    'genres',
                    'chapters' => function ($query) {
                        $query->orderBy('created_at', 'desc')->take(3);
                    }
                ]);
            }
        ])->where('genre_id', $genreId)->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'genre' => $comics->first()->genre->name,
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
}

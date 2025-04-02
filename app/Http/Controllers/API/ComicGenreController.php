<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ComicGenre;
use Illuminate\Http\Request;

class ComicGenreController extends Controller
{
    public function index() { return response()->json(ComicGenre::all()); }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'comic_id' => 'required|exists:comics,id',
            'genre_id' => 'required|exists:genres,id',
        ]);
        $comicGenre = ComicGenre::create($validated);
        return response()->json($comicGenre, 201);
    }

    public function show($id) { return response()->json(ComicGenre::findOrFail($id)); }

    public function update(Request $request, $id)
    {
        $comicGenre = ComicGenre::findOrFail($id);
        $validated = $request->validate([
            'comic_id' => 'sometimes|exists:comics,id',
            'genre_id' => 'sometimes|exists:genres,id',
        ]);
        $comicGenre->update($validated);
        return response()->json($comicGenre);
    }

    public function destroy($id) { ComicGenre::findOrFail($id)->delete(); return response()->json(null, 204); }
}
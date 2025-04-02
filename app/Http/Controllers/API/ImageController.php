<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Image;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Images", description="API Endpoints for Images")
 */
class ImageController extends Controller
{
    /** @OA\Get(path="/api/images", tags={"Images"}, summary="Get all images", @OA\Response(response=200, description="Success")) */
    public function index() { return response()->json(Image::all()); }

    /** @OA\Post(path="/api/images", tags={"Images"}, summary="Create image", @OA\RequestBody(@OA\JsonContent(@OA\Property(property="chapter_id", type="integer"), @OA\Property(property="image_url", type="string"), @OA\Property(property="image_order", type="integer"))), @OA\Response(response=201, description="Created")) */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'chapter_id' => 'required|exists:chapters,id',
            'image_url' => 'required|string|max:255',
            'image_order' => 'required|integer',
        ]);
        $image = Image::create($validated);
        return response()->json($image, 201);
    }

    /** @OA\Get(path="/api/images/{id}", tags={"Images"}, summary="Get image by ID", @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")), @OA\Response(response=200, description="Success")) */
    public function show($id) { return response()->json(Image::findOrFail($id)); }

    /** @OA\Put(path="/api/images/{id}", tags={"Images"}, summary="Update image", @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")), @OA\RequestBody(@OA\JsonContent(@OA\Property(property="image_url", type="string"))), @OA\Response(response=200, description="Updated")) */
    public function update(Request $request, $id)
    {
        $image = Image::findOrFail($id);
        $validated = $request->validate([
            'chapter_id' => 'sometimes|exists:chapters,id',
            'image_url' => 'sometimes|string|max:255',
            'image_order' => 'sometimes|integer',
        ]);
        $image->update($validated);
        return response()->json($image);
    }

    /** @OA\Delete(path="/api/images/{id}", tags={"Images"}, summary="Delete image", @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")), @OA\Response(response=204, description="Deleted")) */
    public function destroy($id) { Image::findOrFail($id)->delete(); return response()->json(null, 204); }
}
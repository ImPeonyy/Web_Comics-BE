<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Statistic;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Statistics", description="API Endpoints for Statistics")
 */
class StatisticController extends Controller
{
    /** @OA\Get(path="/api/statistics", tags={"Statistics"}, summary="Get all statistics", @OA\Response(response=200, description="Success")) */
    public function index() { return response()->json(Statistic::all()); }

    /** @OA\Post(path="/api/statistics", tags={"Statistics"}, summary="Create statistic", @OA\RequestBody(@OA\JsonContent(@OA\Property(property="comic_id", type="integer"), @OA\Property(property="view_count", type="integer"), @OA\Property(property="date_recorded", type="string", format="date"))), @OA\Response(response=201, description="Created")) */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'comic_id' => 'required|exists:comics,id',
            'view_count' => 'required|integer',
            'date_recorded' => 'required|date',
        ]);
        $statistic = Statistic::create($validated);
        return response()->json($statistic, 201);
    }

    /** @OA\Get(path="/api/statistics/{id}", tags={"Statistics"}, summary="Get statistic by ID", @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")), @OA\Response(response=200, description="Success")) */
    public function show($id) { return response()->json(Statistic::findOrFail($id)); }

    /** @OA\Put(path="/api/statistics/{id}", tags={"Statistics"}, summary="Update statistic", @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")), @OA\RequestBody(@OA\JsonContent(@OA\Property(property="view_count", type="integer"))), @OA\Response(response=200, description="Updated")) */
    public function update(Request $request, $id)
    {
        $statistic = Statistic::findOrFail($id);
        $validated = $request->validate([
            'comic_id' => 'sometimes|exists:comics,id',
            'view_count' => 'sometimes|integer',
            'date_recorded' => 'sometimes|date',
        ]);
        $statistic->update($validated);
        return response()->json($statistic);
    }

    /** @OA\Delete(path="/api/statistics/{id}", tags={"Statistics"}, summary="Delete statistic", @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")), @OA\Response(response=204, description="Deleted")) */
    public function destroy($id) { Statistic::findOrFail($id)->delete(); return response()->json(null, 204); }
}
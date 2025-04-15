<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Statistic;
use App\Models\Comic;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StatisticController extends Controller
{

    public function getComicViews($comicId)
    {
        $statistic = Statistic::where('comic_id', $comicId)->first();

        if (!$statistic) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thống kê cho bộ truyện này',
                'data' => null
            ], 404);
        }

        return response()->json([
            'data' => [
                'comic_id' => $statistic->comic_id,
                'view_count' => $statistic->view_count
            ]
        ]);
    }

    public function getTopComicsByDay()
    {
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        $topComics = Statistic::whereBetween('created_at', [$today, $tomorrow])
            ->select('comic_id', \DB::raw('SUM(view_count) as total_views'))
            ->with([
                'comic' => function ($query) {
                    $query->select('id', 'title', 'cover_image')
                        ->with([
                            'genres',
                            'statistics',
                            'favorites',
                            'chapters' => function ($query) {
                                $query->orderBy('chapter_order', 'desc')->take(1);
                            }
                        ]);
                }
            ])
            ->groupBy('comic_id')
            ->orderByDesc('total_views')
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->comic_id,
                    'title' => $item->comic->title,
                    'cover_image' => $item->comic->cover_image,
                    'view_count' => $item->total_views,
                    'genres' => $item->comic->genres,
                    'statistics' => $item->comic->statistics,
                    'favorites' => $item->comic->favorites,
                    'chapters' => $item->comic->chapters,
                    'status' => $item->comic->status
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $topComics
        ]);
    }

    public function getTopComicsByWeek()
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $topComics = Statistic::whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->select('comic_id', \DB::raw('SUM(view_count) as total_views'))
            ->with([
                'comic' => function ($query) {
                    $query->select('id', 'title', 'cover_image')
                        ->with([
                            'genres',
                            'statistics',
                            'favorites',
                            'chapters' => function ($query) {
                                $query->orderBy('chapter_order', 'desc')->take(1);
                            }
                        ]);
                }
            ])
            ->groupBy('comic_id')
            ->orderByDesc('total_views')
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->comic_id,
                    'title' => $item->comic->title,
                    'cover_image' => $item->comic->cover_image,
                    'view_count' => $item->total_views,
                    'genres' => $item->comic->genres,
                    'statistics' => $item->comic->statistics,
                    'favorites' => $item->comic->favorites,
                    'chapters' => $item->comic->chapters,
                    'status' => $item->comic->status
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $topComics
        ]);
    }

    public function getTopComicsByMonth()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $topComics = Statistic::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->select('comic_id', \DB::raw('SUM(view_count) as total_views'))
            ->with([
                'comic' => function ($query) {
                    $query->select('id', 'title', 'cover_image', 'status')
                        ->with([
                            'genres',
                            'statistics',
                            'favorites',
                            'chapters' => function ($query) {
                                $query->orderBy('chapter_order', 'desc')->take(1);
                            }
                        ]);
                }
            ])
            ->groupBy('comic_id')
            ->orderByDesc('total_views')
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->comic_id,
                    'title' => $item->comic->title,
                    'cover_image' => $item->comic->cover_image,
                    'view_count' => $item->total_views,
                    'genres' => $item->comic->genres,
                    'statistics' => $item->comic->statistics,
                    'favorites' => $item->comic->favorites,
                    'chapters' => $item->comic->chapters,
                    'status' => $item->comic->status
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $topComics
        ]);
    }

    public function increaseComicView($comicId)
    {
        try {
            $statistic = Statistic::where('comic_id', $comicId)->first();

            if (!$statistic) {
                // Nếu chưa có thống kê, tạo mới
                $statistic = Statistic::create([
                    'comic_id' => $comicId,
                    'view_count' => 1
                ]);
            } else {
                // Nếu đã có, tăng lượt xem lên 1
                $statistic->increment('view_count');
            }

            return response()->json([
                'success' => true,
                'message' => 'Tăng lượt xem thành công',
                'data' => [
                    'id' => $statistic->comic_id,
                    'view_count' => $statistic->view_count
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }
}
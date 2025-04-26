<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Statistic;
use App\Models\Comic;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StatisticController extends Controller
{
    public function getTopComicsByDay()
    {
        $user = null;
        $token = request()->bearerToken();
        if ($token) {
            $user = \Laravel\Sanctum\PersonalAccessToken::findToken($token)?->tokenable;
        }

        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        $topComics = Comic::with([
            'genres',
            'chapters' => function ($query) {
                $query->orderBy('chapter_order', 'desc')->take(1);
            }
        ])
            ->selectRaw('comics.*, COALESCE(SUM(statistics.view_count), 0) as totalViews')
            ->leftJoin('statistics', 'comics.id', '=', 'statistics.comic_id')
            ->whereBetween('statistics.created_at', [$today, $tomorrow])
            ->groupBy('comics.id')
            ->orderByDesc('totalViews')
            ->take(5)
            ->get();

        if ($user) {
            $topComics->transform(function ($comic) use ($user) {
                $comic->isFav = $comic->favorites()->where('user_id', $user->id)->exists();
                return $comic;
            });
        }

        return response()->json([
            'success' => true,
            'data' => $topComics
        ]);
    }

    public function getTopComicsByWeek()
    {
        $user = null;
        $token = request()->bearerToken();
        if ($token) {
            $user = \Laravel\Sanctum\PersonalAccessToken::findToken($token)?->tokenable;
        }

        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $topComics = Comic::with([
            'genres',
            'chapters' => function ($query) {
                $query->orderBy('chapter_order', 'desc')->take(1);
            }
        ])
            ->selectRaw('comics.*, COALESCE(SUM(statistics.view_count), 0) as totalViews')
            ->leftJoin('statistics', 'comics.id', '=', 'statistics.comic_id')
            ->whereBetween('statistics.created_at', [$startOfWeek, $endOfWeek])
            ->groupBy('comics.id')
            ->orderByDesc('totalViews')
            ->take(5)
            ->get();

        if ($user) {
            $topComics->transform(function ($comic) use ($user) {
                $comic->isFav = $comic->favorites()->where('user_id', $user->id)->exists();
                return $comic;
            });
        }

        return response()->json([
            'success' => true,
            'data' => $topComics
        ]);
    }

    public function getTopComicsByMonth()
    {
        $user = null;
        $token = request()->bearerToken();
        if ($token) {
            $user = \Laravel\Sanctum\PersonalAccessToken::findToken($token)?->tokenable;
        }

        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $topComics = Comic::with([
            'genres',
            'chapters' => function ($query) {
                $query->orderBy('chapter_order', 'desc')->take(1);
            }
        ])
            ->selectRaw('comics.*, COALESCE(SUM(statistics.view_count), 0) as totalViews')
            ->leftJoin('statistics', 'comics.id', '=', 'statistics.comic_id')
            ->whereBetween('statistics.created_at', [$startOfMonth, $endOfMonth])
            ->groupBy('comics.id')
            ->orderByDesc('totalViews')
            ->take(5)
            ->get();

        if ($user) {
            $topComics->transform(function ($comic) use ($user) {
                $comic->isFav = $comic->favorites()->where('user_id', $user->id)->exists();
                return $comic;
            });
        }

        return response()->json([
            'success' => true,
            'data' => $topComics
        ]);
    }

    public function getRecommendComics()
    {
        $user = null;
        $token = request()->bearerToken();
        if ($token) {
            $user = \Laravel\Sanctum\PersonalAccessToken::findToken($token)?->tokenable;
        }

        $topComics = Comic::with([
            'genres',
            'chapters' => function ($query) {
                $query->orderBy('chapter_order', 'desc')->take(1);
            }
        ])
            ->selectRaw('comics.*, COALESCE(SUM(statistics.view_count), 0) as totalViews')
            ->leftJoin('statistics', 'comics.id', '=', 'statistics.comic_id')
            ->groupBy('comics.id')
            ->orderByDesc('totalViews')
            ->take(5)
            ->get();

        if ($user) {
            $topComics->transform(function ($comic) use ($user) {
                $comic->isFav = $comic->favorites()->where('user_id', $user->id)->exists();
                return $comic;
            });
        }

        return response()->json([
            'success' => true,
            'data' => $topComics
        ]);
    }

    public function increaseComicView($comicId)
    {
        try {
            $today = Carbon::today();
            $tomorrow = Carbon::tomorrow();

            $statistic = Statistic::where('comic_id', $comicId)
                ->whereBetween('created_at', [$today, $tomorrow])
                ->first();

            if (!$statistic) {
                // Nếu chưa có thống kê trong ngày, tạo mới
                $statistic = Statistic::create([
                    'comic_id' => $comicId,
                    'view_count' => 1
                ]);
            } else {
                // Nếu đã có thống kê trong ngày, tăng lượt xem lên 1
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
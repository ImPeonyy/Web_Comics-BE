<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\ComicController;
use App\Http\Controllers\API\ChapterController;
use App\Http\Controllers\API\ImageController;
use App\Http\Controllers\API\CommentController;
use App\Http\Controllers\API\FavoriteController;
use App\Http\Controllers\API\HistoryController;
use App\Http\Controllers\API\StatisticController;
use App\Http\Controllers\API\GenreController;
use App\Http\Controllers\API\ComicGenreController;
// Users
Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [UserController::class, 'logout'])->middleware('self');
    Route::prefix('users')->group(function () {
        Route::post('/upload-avatar', [UserController::class, 'uploadAvatar'])->middleware('self');
        Route::post('/change-password', [UserController::class, 'changePassword'])->middleware('self');
        Route::get('/', [UserController::class, 'index'])->middleware('admin');
        Route::get('/{id}', [UserController::class, 'show'])->middleware('self');
        Route::put('/update-info', [UserController::class, 'update'])->middleware('self');
        Route::delete('/{id}', [UserController::class, 'destroy'])->middleware('admin');
    });

    // Comments
    Route::prefix('comments')->group(function () {
        Route::post('/post', [CommentController::class, 'postComment']);
    });

    // Favorites
    Route::prefix('favorites')->group(function () {
        // Lấy favorites của người dùng hiện tại (yêu cầu đăng nhập)
        Route::get('/my-favorites', [FavoriteController::class, 'getCurrentUserFavorites']);
        Route::get('/home-page', [FavoriteController::class, 'getHomePageFavorites']);
        // Thêm và xóa favorite của người dùng hiện tại
        Route::post('/add/{comicId}', [FavoriteController::class, 'addFavorite']);
        Route::delete('/remove/{comicId}', [FavoriteController::class, 'removeFavorite']);
    });

    // History
    Route::prefix('history')->group(function () {
        // Lấy history của người dùng hiện tại
        Route::get('/chapters', [HistoryController::class, 'getChapterHistory']);

        // Lấy history của người dùng hiện tại
        Route::get('/my-history', [HistoryController::class, 'getCurrentUserHistory']);
        Route::get('/home-page', [HistoryController::class, 'getHomePageHistory']);

        // Thêm và xóa history của người dùng hiện tại
        Route::post('/add', [HistoryController::class, 'addHistory']);
        Route::delete('/remove', [HistoryController::class, 'removeHistory']);
    });
});
// Comics
Route::prefix('comics')->group(function () {
    Route::get('/random', [ComicController::class, 'getRandomComic']); // Lấy ngẫu nhiên 1 truyện với 3 chapter mới nhất
    Route::get('/filter', [ComicController::class, 'filterComics']);
    Route::get('/search', [ComicController::class, 'searchOnChange']); // Lấy comics theo keyword
    Route::get('/', [ComicController::class, 'getAllComics']); // Lấy tất cả comics
    Route::get('/{id}', [ComicController::class, 'getComicById']); // Lấy 1 comic theo ID
});

// Chapters
Route::prefix('chapters')->group(function () {
    Route::get('/', [ChapterController::class, 'index']); // Lấy tất cả chapters
    Route::get('/comic/{comicId}', [ChapterController::class, 'getChaptersByComicId']); // Lấy chapters theo comic id
});

// Images
Route::prefix('images')->group(function () {
    Route::get('/chapter/{chapterId}', [ImageController::class, 'getImagesByChapterId']); // Lấy images theo chapter id
});

// Genres
Route::prefix('genres')->group(function () {
    Route::get('/', [GenreController::class, 'index']); // Lấy tất cả genres
});

// Statistics
Route::prefix('statistics')->group(function () {
    Route::get('top/day', [StatisticController::class, 'getTopComicsByDay']);
    Route::get('top/week', [StatisticController::class, 'getTopComicsByWeek']);
    Route::get('top/month', [StatisticController::class, 'getTopComicsByMonth']);
    Route::post('increase-view/{comicId}', [StatisticController::class, 'increaseComicView']);
});

// Comic Genres
Route::prefix('comics-genres')->group(function () {
    Route::get('/{genreId}', [ComicGenreController::class, 'getComicsByGenre']);
});

Route::prefix('comments')->group(function () {
    Route::get('/comic/{comicId}', [CommentController::class, 'getCommentsByComic']);
});

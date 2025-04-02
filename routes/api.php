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

    // Favorites
    Route::prefix('favorites')->group(function () {
        // Lấy favorites của người dùng hiện tại (yêu cầu đăng nhập)
        Route::get('/my-favorites', [FavoriteController::class, 'getCurrentUserFavorites'])->middleware('auth:sanctum');

        // Thêm và xóa favorite của người dùng hiện tại
        Route::post('/add', [FavoriteController::class, 'addFavorite'])->middleware('auth:sanctum');
        Route::delete('/remove', [FavoriteController::class, 'removeFavorite'])->middleware('auth:sanctum');
    });

    // History
    Route::prefix('history')->group(function () {
        // Lấy history của người dùng hiện tại
        Route::get('/my-history', [HistoryController::class, 'getCurrentUserHistory'])->middleware('auth:sanctum');

        // Thêm và xóa history của người dùng hiện tại
        Route::post('/add', [HistoryController::class, 'addHistory'])->middleware('auth:sanctum');
        Route::delete('/remove', [HistoryController::class, 'removeHistory'])->middleware('auth:sanctum');
    });
});

// Comics
Route::prefix('comics')->group(function () {
    Route::get('/', [ComicController::class, 'index']); // Lấy tất cả comics
    Route::get('/{id}', [ComicController::class, 'show']); // Lấy 1 comic theo ID
    Route::get('/status/{status}', [ComicController::class, 'getByStatus']); // Lấy comics theo status
});

// Genres
Route::prefix('genres')->group(function () {
    Route::get('/', [GenreController::class, 'index']); // Lấy tất cả genres
});

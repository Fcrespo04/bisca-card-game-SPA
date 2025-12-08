<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoardThemeController;
use App\Http\Controllers\CardFaceController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\HistoryController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::apiResource('games', GameController::class)->only(['index', 'show', 'store']);

Route::get('/leaderboard/global', [HistoryController::class, 'leaderboardGlobal']);
Route::get('/users/list', [HistoryController::class, 'listAllUsers']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users/me', function (Request $request) {
        return $request->user();
    });

    Route::post('logout', [AuthController::class, 'logout']);

    Route::prefix('files')->group(function () {
        Route::post('userphoto', [FileController::class, 'uploadUserPhoto']);
        Route::post('cardfaces', [FileController::class, 'uploadCardFaces']);
    });

    Route::apiResource('games', GameController::class)->except(['index', 'show', 'store']);

    Route::apiResources([
        'users' => UserController::class,
        'card-faces' => CardFaceController::class,
        'board-themes' => BoardThemeController::class,
    ]);
    Route::patch('/users/{user}/photo-url', [UserController::class, 'patchPhotoURL']);

    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions', [TransactionController::class, 'store']);

    Route::get('/card-decks', [\App\Http\Controllers\CardDeckController::class, 'index']);
    Route::post('/card-decks/{deck}/purchase', [\App\Http\Controllers\CardDeckController::class, 'purchase']);
    Route::post('/card-decks/{deck}/equip', [\App\Http\Controllers\CardDeckController::class, 'equip']);

    Route::get('/history', [HistoryController::class, 'index']);
    Route::get('/statistics/personal', [HistoryController::class, 'statisticsPersonal']);
    Route::get('/admin/stats/{user}', [HistoryController::class, 'statisticsPlayer']);
    
});

Route::get('/metadata', function (Request $request) {
    return [
        'name' => 'DAD 2025/26 PROJECT API',
        'version' => '0.0.1',
    ];
});

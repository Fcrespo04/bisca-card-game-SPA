<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoardThemeController;
use App\Http\Controllers\CardFaceController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\CardDeckController; // Adicionei o import para ficar mais limpo
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\AdminController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas Públicas (Não precisa de Login)
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/metadata', function (Request $request) {
    return [
        'name' => 'DAD 2025/26 PROJECT API',
        'version' => '0.0.1',
    ];
});

// Estatísticas Públicas
Route::get('/public-statistics', [StatisticsController::class, 'publicStats']);

// Jogos (Apenas leitura pública básica, se necessário, ou ajusta conforme o enunciado)
Route::apiResource('games', GameController::class)->only(['index', 'show', 'store']);


Route::get('/leaderboard/global', [HistoryController::class, 'leaderboardGlobal']);


/*
|--------------------------------------------------------------------------
| Rotas Privadas (Precisa de Token / Login)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    
    // User Info
    Route::get('/users/me', function (Request $request) {
        return $request->user();
    });
    Route::post('logout', [AuthController::class, 'logout']);

    // Estatísticas Privadas (AQUI ESTÁ A CORREÇÃO)
    Route::get('/player-statistics', [StatisticsController::class, 'playerStats']);
    Route::get('/admin-statistics', [StatisticsController::class, 'adminStats']);

    // Uploads
    Route::prefix('files')->group(function () {
        Route::post('userphoto', [FileController::class, 'uploadUserPhoto']);
        Route::post('cardfaces', [FileController::class, 'uploadCardFaces']);
    });

    // Games (Restantes operações)
    Route::apiResource('games', GameController::class)->except(['index', 'show', 'store']);

    // Resources Gerais
    Route::apiResources([
        'users' => UserController::class,
        'card-faces' => CardFaceController::class,
        'board-themes' => BoardThemeController::class,
    ]);
    
    Route::patch('/users/{user}/photo-url', [UserController::class, 'patchPhotoURL']);

    // Transactions
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions', [TransactionController::class, 'store']);

    // Card Decks
    Route::get('/card-decks', [CardDeckController::class, 'index']);
    Route::post('/card-decks/{deck}/purchase', [CardDeckController::class, 'purchase']);
    Route::post('/card-decks/{deck}/equip', [CardDeckController::class, 'equip']);


        Route::get('/history', [HistoryController::class, 'index']);
    Route::get('/statistics/personal', [HistoryController::class, 'statisticsPersonal'])

    ->withTrashed();

    Route::prefix('admin')->controller(AdminController::class)->group(function () {
        
        Route::get('/stats/{user}', [HistoryController::class, 'statisticsPlayer']);
        Route::get('/users/list', [AdminController::class, 'getUsersListAdmin']); // ADICIONADO
        Route::post('/users', [AdminController::class, 'storeUserByAdmin']);
        Route::get('/transactions/stats', [AdminController::class, 'getDashboardStats']);
        
        // G5: Bloqueio/Desbloqueio (PATCH /api/admin/users/{user}/block)
        Route::patch('/users/{user}/block', 'toggleBlock');
        
        // G5: Remoção (DELETE /api/admin/users/{user})
        Route::delete('/users/{user}', 'removeAccount');    
        
        // G5: Criação de Admin (POST /api/admin/create-admin)
         Route::post('/create-admin', 'createAdmin');        
    });
});
<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\QrCodeController;
use App\Http\Controllers\Api\PointageController;
use App\Http\Controllers\Api\BulletinPaieController;
use App\Http\Controllers\Api\StatsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Application de Pointage RH
|--------------------------------------------------------------------------
*/

// Routes publiques (sans authentification)
Route::post('/auth/login', [AuthController::class, 'login']);

// Routes protégées (authentification requise)
Route::middleware('auth:sanctum')->group(function () {

    // ===== AUTHENTIFICATION =====
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
    });

    // ===== POINTAGES (Tous les utilisateurs) =====
    Route::prefix('pointages')->group(function () {
        Route::post('/', [PointageController::class, 'store']); // Scanner QR
        Route::get('/me', [PointageController::class, 'mesPointages']);
        Route::get('/statut', [PointageController::class, 'statut']);
        Route::get('/sessions', [PointageController::class, 'mesSessions']);
    });

    // ===== QR CODES (Gardien) =====
    Route::prefix('qrcode')->group(function () {
        Route::post('/generate', [QrCodeController::class, 'generate']);
        Route::get('/current', [QrCodeController::class, 'current']);
        Route::post('/validate', [QrCodeController::class, 'validate']);
        Route::get('/history', [QrCodeController::class, 'history']);
    });

    // ===== BULLETINS DE PAIE (Employé - ses bulletins) =====
    Route::get('/bulletins/me', [BulletinPaieController::class, 'mesBulletins']);

    // ===== ROUTES RH & DIRECTEUR =====
    Route::middleware('can:admin')->group(function () {

        // Gestion des utilisateurs
        Route::apiResource('users', UserController::class);
        Route::post('/users/{user}/toggle-active', [UserController::class, 'toggleActive']);
        Route::get('/employes', [UserController::class, 'employes']);

        // Pointages (consultation)
        Route::get('/pointages', [PointageController::class, 'index']);
        Route::get('/pointages/user/{user}', [PointageController::class, 'userPointages']);
        Route::get('/pointages/date', [PointageController::class, 'parDate']);
        Route::get('/heures/{user}', [PointageController::class, 'resumeHeures']);

        // Bulletins de paie (gestion)
        Route::get('/bulletins', [BulletinPaieController::class, 'index']);
        Route::post('/bulletins/generate', [BulletinPaieController::class, 'generate']);
        Route::post('/bulletins/generate-all', [BulletinPaieController::class, 'generateAll']);
        Route::get('/bulletins/{bulletin}', [BulletinPaieController::class, 'show']);
        Route::put('/bulletins/{bulletin}', [BulletinPaieController::class, 'update']);

        // Statistiques
        Route::prefix('stats')->group(function () {
            Route::get('/dashboard', [StatsController::class, 'dashboard']);
            Route::get('/presences', [StatsController::class, 'presences']);
            Route::get('/heures', [StatsController::class, 'heures']);
            Route::get('/salaires', [StatsController::class, 'salaires']);
        });
    });
});

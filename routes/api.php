<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RepertoireController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AccountingController;
use App\Http\Controllers\Api\DashboardController;

Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        Route::get('/repertoire', [RepertoireController::class, 'index']);
        Route::post('/repertoire', [RepertoireController::class, 'store'])->middleware('check.role:Admin,Direttore');
        Route::put('/repertoire/{id}', [RepertoireController::class, 'update'])->middleware('check.role:Admin,Direttore');
        Route::delete('/repertoire/{id}', [RepertoireController::class, 'destroy'])->middleware('check.role:Admin,Direttore');

        Route::get('/events', [EventController::class, 'index']);
        Route::post('/events', [EventController::class, 'store'])->middleware('check.role:Admin,Direttore');
        Route::put('/events/{id}', [EventController::class, 'update'])->middleware('check.role:Admin,Direttore');
        Route::delete('/events/{id}', [EventController::class, 'destroy'])->middleware('check.role:Admin,Direttore');
        Route::post('/events/{id}/attendance', [AttendanceController::class, 'store']);

        Route::get('/accounting', [AccountingController::class, 'index']);
        Route::post('/accounting', [AccountingController::class, 'store'])->middleware('check.role:Admin');
        Route::put('/accounting/{id}', [AccountingController::class, 'update'])->middleware('check.role:Admin');

        Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    });
});

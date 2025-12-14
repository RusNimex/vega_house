<?php

use App\Http\Controllers\V1\AuthController;
use App\Http\Controllers\V1\ProfileController;
use Illuminate\Support\Facades\Route;

// Публичные
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Защищенные
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/profile/update', [ProfileController::class, 'update']);
    Route::get('/profile/company', [ProfileController::class, 'companies']);
});

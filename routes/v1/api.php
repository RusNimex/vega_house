<?php

use App\Http\Controllers\V1\AuthController;
use App\Http\Controllers\V1\CompanyController;
use App\Http\Controllers\V1\ProfileController;
use Illuminate\Support\Facades\Route;

// Публичные
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Защищенные
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [ProfileController::class, 'me']);
    Route::put('/profile/update', [ProfileController::class, 'update']);
    Route::get('/profile/company', [ProfileController::class, 'companies']);
    Route::get('/profile/options', [ProfileController::class, 'options']);
    Route::put('/profile/options', [ProfileController::class, 'updateOption']);
    Route::get('/company', [CompanyController::class, 'index']);
});

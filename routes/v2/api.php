<?php

use Illuminate\Support\Facades\Route;

// Заглушка
Route::get('/', function () {
    return response()->json([
        'version' => 2,
        'message' => 'Coming soon'
    ]);
});


<?php

Route::get('/', function () {
    return response()->json([
        'message' => 'Vega House API',
        'version' => '1.0.0',
        'status' => 'running'
    ]);
});

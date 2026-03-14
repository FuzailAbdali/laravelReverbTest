<?php

use App\Http\Controllers\ScoreController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/matches/{matchId}/score', [ScoreController::class, 'update']);
});

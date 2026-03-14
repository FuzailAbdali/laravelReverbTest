<?php

use App\Http\Controllers\ScoreController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'live');
Route::post('/matches/{matchId}/score', [ScoreController::class, 'update']);

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\OpponentController;
use App\Http\Controllers\FootballMatchController;

Route::redirect('/', '/players');

Route::resources([
    'players' => PlayerController::class,
    'positions' => PositionController::class,
    'opponents' => OpponentController::class,
    'football-matches' => FootballMatchController::class,
]);

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\OpponentController;
use App\Http\Controllers\FootballMatchController;

Route::redirect('/', '/players');

Route::get('football-matches/{footballMatch}/lineup', [FootballMatchController::class, 'lineup'])->name('football-matches.lineup');
Route::post('football-matches/{footballMatch}/lineup', [FootballMatchController::class, 'lineupUpdate'])->name('football-matches.lineup.update');

Route::resources([
    'players' => PlayerController::class,
    'positions' => PositionController::class,
    'opponents' => OpponentController::class,
    'football-matches' => FootballMatchController::class,
]);

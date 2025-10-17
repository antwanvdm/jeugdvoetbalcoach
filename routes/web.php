<?php

use App\Http\Controllers\FormationController;
use App\Http\Controllers\SeasonController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\OpponentController;
use App\Http\Controllers\FootballMatchController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/players');

Route::get('football-matches/{footballMatch}/lineup', [FootballMatchController::class, 'lineup'])->name('football-matches.lineup');
Route::post('football-matches/{footballMatch}/lineup', [FootballMatchController::class, 'lineupUpdate'])->name('football-matches.lineup.update');

Route::resources([
    'players' => PlayerController::class,
    'seasons' => SeasonController::class,
    'formations' => FormationController::class,
    'positions' => PositionController::class,
    'opponents' => OpponentController::class,
    'football-matches' => FootballMatchController::class,
]);

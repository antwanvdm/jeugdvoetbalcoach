<?php

use App\Http\Controllers\Admin\PositionController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FootballMatchController;
use App\Http\Controllers\FormationController;
use App\Http\Controllers\OpponentController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SeasonController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

// Public home page
Route::get('/', function () {
    return view('home');
})->name('home');

// Protected routes - require authentication
Route::middleware(['auth'])->group(function () {
    // Dashboard (renamed from home)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Team management routes
    Route::resource('teams', TeamController::class)->except(['show']);
    Route::post('/teams/{team}/switch', [TeamController::class, 'switch'])->name('teams.switch');
    Route::post('/teams/{team}/set-default', [TeamController::class, 'setDefault'])->name('teams.set-default');
    Route::post('/teams/{team}/invite/regenerate', [TeamController::class, 'regenerateInviteCode'])->name('teams.invite.regenerate');
    Route::get('/teams/join/{inviteCode}', [TeamController::class, 'showJoin'])->name('teams.join.show');
    Route::post('/teams/join/{inviteCode}', [TeamController::class, 'join'])->name('teams.join');
    Route::delete('/teams/{team}/leave', [TeamController::class, 'leave'])->name('teams.leave');

    // Football match lineup routes
    Route::get('football-matches/{footballMatch}/lineup', [FootballMatchController::class, 'lineup'])->name('football-matches.lineup');
    Route::post('football-matches/{footballMatch}/lineup', [FootballMatchController::class, 'lineupUpdate'])->name('football-matches.lineup.update');

    // Resource routes
    Route::resources([
        'players' => PlayerController::class,
        'seasons' => SeasonController::class,
        'formations' => FormationController::class,
        'opponents' => OpponentController::class,
        'football-matches' => FootballMatchController::class,
    ]);

    // Admin-only: Position//User management
    Route::middleware(['admin'])->name('admin.')->group(function () {
        Route::resource('positions', PositionController::class);
        Route::get('admin/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::patch('admin/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    });
});

require __DIR__.'/auth.php';

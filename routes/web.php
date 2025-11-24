<?php

use App\Http\Controllers\Admin\PositionController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FootballMatchController;
use App\Http\Controllers\FormationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\OpponentController;
use App\Http\Controllers\Api\OpponentSearchController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SeasonController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

// Public home page
Route::get('/', [HomeController::class, 'show'])->name('home');

// Public opponents search (autocomplete endpoint)
Route::get('/api/opponents', OpponentSearchController::class)->name('api.opponents');

// Public team join routes (accessible without authentication)
Route::get('/teams/join/{inviteCode}', [TeamController::class, 'showJoin'])->name('teams.join.show');
Route::post('/teams/join/{inviteCode}', [TeamController::class, 'join'])->middleware('auth')->name('teams.join');

// Protected routes - require authentication
Route::middleware(['auth'])->group(function () {
    // Dashboard (renamed from home)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Onboarding wizard
    Route::get('/onboarding', [OnboardingController::class, 'index'])->name('onboarding.index');
    Route::post('/onboarding/complete', [OnboardingController::class, 'complete'])->name('onboarding.complete');
    Route::post('/onboarding/skip', [OnboardingController::class, 'skip'])->name('onboarding.skip');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Team management routes
    Route::post('/teams/{team}/switch', [TeamController::class, 'switch'])->name('teams.switch');
    Route::post('/teams/{team}/set-default', [TeamController::class, 'setDefault'])->name('teams.set-default');
    Route::post('/teams/{team}/invite/regenerate', [TeamController::class, 'regenerateInviteCode'])->name('teams.invite.regenerate');
    Route::delete('/teams/{team}/leave', [TeamController::class, 'leave'])->name('teams.leave');

    // Football match lineup routes
    Route::get('football-matches/{footballMatch}/lineup', [FootballMatchController::class, 'lineup'])->name('football-matches.lineup');
    Route::post('football-matches/{footballMatch}/lineup', [FootballMatchController::class, 'lineupUpdate'])->name('football-matches.lineup.update');

    // Resource routes
    Route::resources([
        'teams' => TeamController::class,
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

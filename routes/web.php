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
use Spatie\Honeypot\ProtectAgainstSpam;

//General routes
Route::get('/', [HomeController::class, 'show'])->name('home');
Route::post('/feedback', [HomeController::class, 'feedback'])->name('home.feedback')->middleware(ProtectAgainstSpam::class);
Route::get('/privacy', [\App\Http\Controllers\PrivacyController::class, 'show'])->name('privacy');

//Public opponents search (autocomplete endpoint)
Route::get('/api/opponents', OpponentSearchController::class)->name('api.opponents');

//Public share pages (for parents)
Route::get('/football-matches/{footballMatch}/share/{shareToken}', [FootballMatchController::class, 'showPublic'])->name('football-matches.share');
Route::get('/seasons/{season}/share/{shareToken}', [SeasonController::class, 'showPublic'])->name('seasons.share');

//Public team join routes (accessible without authentication)
Route::get('/teams/join/{inviteCode}', [TeamController::class, 'showJoin'])->name('teams.join.show');
Route::post('/teams/join/{inviteCode}', [TeamController::class, 'join'])->middleware('auth')->name('teams.join');

// Dashboard - accessible with auth only (no email verification required)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// Protected routes - require authentication + verified email
Route::middleware(['auth', 'verified'])->group(function () {
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
    Route::patch('/teams/{team}/label', [TeamController::class, 'updateLabel'])->name('teams.label.update');
    Route::post('/teams/{team}/invite/regenerate', [TeamController::class, 'regenerateInviteCode'])->name('teams.invite.regenerate');
    Route::delete('/teams/{team}/leave', [TeamController::class, 'leave'])->name('teams.leave');

    // Football match lineup routes
    Route::get('football-matches/{footballMatch}/lineup', [FootballMatchController::class, 'lineup'])->name('football-matches.lineup');
    Route::post('football-matches/{footballMatch}/lineup', [FootballMatchController::class, 'lineupUpdate'])->name('football-matches.lineup.update');
    Route::post('football-matches/{footballMatch}/regenerate-lineup', [FootballMatchController::class, 'regenerateLineup'])->name('football-matches.regenerate-lineup');

    // Resource routes
    Route::resources([
        'teams' => TeamController::class,
        'players' => PlayerController::class,
        'seasons' => SeasonController::class,
        'formations' => FormationController::class,
        'opponents' => OpponentController::class,
        'football-matches' => FootballMatchController::class,
    ]);

    // Season share token regeneration
    Route::post('/seasons/{season}/share/regenerate', [SeasonController::class, 'regenerateShareToken'])->name('seasons.share.regenerate');

    // Admin-only: Position//User management
    Route::middleware(['admin'])->name('admin.')->group(function () {
        Route::resource('positions', PositionController::class);
        Route::get('admin/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::patch('admin/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    });
});

// Sitemap
Route::withoutMiddleware('web')->get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');

require __DIR__.'/auth.php';

<?php

namespace App\Http\Controllers;

use App\Models\FootballMatch;
use App\Models\Formation;
use App\Models\Player;
use App\Models\Season;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if (auth()->user()->isAdmin()){
            $statistics = [
                'total_users' => User::count(),
                'total_teams' => Team::count(),
                'total_seasons' => Season::count(),
                'total_players' => Player::count(),
                'total_matches' => FootballMatch::count(),
                'total_custom_formations' => Formation::where('is_global', false)->count(),
            ];

            return view('admin.dashboard', compact('statistics'));
        }

        $currentTeamId = session('current_team_id');
        $currentTeam = Team::find($currentTeamId);
        $user = auth()->user();

        // Check if team has any seasons, if not redirect to onboarding
        // Only redirect if this is the user's ONLY team (first team ever)
        // (unless user has explicitly skipped onboarding)
        $hasSeasons = Season::where('team_id', $currentTeamId)->exists();
        $isFirstTeam = $user->teams()->count() === 1;
        if (!$hasSeasons && !session('onboarding_skipped') && $isFirstTeam) {
            return redirect()->route('onboarding.index');
        }

        // Get onboarding progress
        $onboardingSteps = [
            'season' => $hasSeasons,
            'players' => $currentTeam?->players()->exists() ?? false,
            'match' => $currentTeam?->footballMatches()->exists() ?? false,
        ];
        $onboardingComplete = $onboardingSteps['season'] && $onboardingSteps['players'] && $onboardingSteps['match'];

        $activeSeason = Season::getCurrent();
        $recentMatches = $currentTeam->footballMatches()->whereNotNull('goals_scored')->orderByDesc('date')->take(3)->get();
        $nextMatch = $currentTeam->footballMatches()->where('date', '>', now())->get()->first();
        return view('dashboard', compact('recentMatches', 'nextMatch', 'currentTeam', 'onboardingSteps', 'onboardingComplete', 'activeSeason'));
    }
}

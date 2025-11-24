<?php

namespace App\Http\Controllers;

use App\Models\FootballMatch;
use App\Models\Team;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        if (auth()->user()->isAdmin()){
            return view('admin.dashboard');
        }

        $currentTeamId = session('current_team_id');
        $currentTeam = Team::find($currentTeamId);
        $recentMatches = $currentTeam->footballMatches()->whereNotNull('goals_scored')->orderByDesc('date')->take(3)->get();
        $nextMatch = $currentTeam->footballMatches()->where('date', '>', now())->get()->first();
        return view('dashboard', compact('recentMatches', 'nextMatch', 'currentTeam'));
    }
}

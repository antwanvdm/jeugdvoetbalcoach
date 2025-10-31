<?php

namespace App\Http\Controllers;

use App\Models\FootballMatch;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        if (auth()->user()->isAdmin()){
            return view('admin.dashboard');
        }

        $recentMatches = FootballMatch::with('opponent')->whereNotNull('goals_scored')->orderByDesc('date')->take(3)->get();
        $nextMatch = FootballMatch::with('opponent')->where('date', '>', now())->get()->first();
        return view('dashboard', compact('recentMatches', 'nextMatch'));
    }
}

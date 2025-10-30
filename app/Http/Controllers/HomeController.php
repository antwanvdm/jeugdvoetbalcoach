<?php

namespace App\Http\Controllers;

use App\Models\FootballMatch;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $recentMatches = FootballMatch::with('opponent')->whereNotNull('goals_scored')->orderByDesc('date')->take(3)->get();
        $nextMatch = FootballMatch::with('opponent')->where('date', '>', now())->get()->first();
        return view('home', compact('recentMatches', 'nextMatch'));
    }
}

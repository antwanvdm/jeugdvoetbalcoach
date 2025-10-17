<?php

namespace App\Http\Controllers;

use App\Models\FootballMatch;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $recentMatches = FootballMatch::with('opponent')->orderByDesc('date')->take(3)->get();
        return view('home', compact('recentMatches'));
    }
}

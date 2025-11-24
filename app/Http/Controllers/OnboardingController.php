<?php

namespace App\Http\Controllers;

use App\Models\Season;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    /**
     * Show the onboarding wizard.
     */
    public function index(): View|RedirectResponse
    {
        $currentTeamId = session('current_team_id');
        
        if (!$currentTeamId) {
            return redirect()->route('teams.index')
                ->with('error', 'Selecteer eerst een team.');
        }

        // Check if onboarding is already completed
        $hasSeasons = Season::where('team_id', $currentTeamId)->exists();
        
        if ($hasSeasons) {
            return redirect()->route('dashboard')
                ->with('info', 'Je hebt al een seizoen aangemaakt!');
        }

        return view('onboarding.index');
    }

    /**
     * Mark onboarding as completed and redirect to next step.
     */
    public function complete(): RedirectResponse
    {
        // Clear the skipped flag if it exists
        session()->forget('onboarding_skipped');
        
        return redirect()->route('seasons.create')
            ->with('success', 'Maak je eerste seizoen aan om te beginnen!');
    }

    /**
     * Skip onboarding.
     */
    public function skip(): RedirectResponse
    {
        // Mark onboarding as skipped in session so we don't keep redirecting
        session(['onboarding_skipped' => true]);
        
        return redirect()->route('dashboard')
            ->with('info', 'Je kunt later een seizoen aanmaken via het menu.');
    }
}

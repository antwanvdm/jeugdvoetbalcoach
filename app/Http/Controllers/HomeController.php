<?php

namespace App\Http\Controllers;

use App\Mail\Feedback;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class HomeController extends Controller
{
    public function show()
    {
        $currentTeamId = session('current_team_id');
        $currentTeam = Team::find($currentTeamId);
        return view('home', compact('currentTeam'));
    }

    public function feedback(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        Log::info('Feedback ontvangen', $validated);

        // To administrator
        if (config('mail.default_to')) {
            Mail::to(config('mail.default_to'))
                ->send((new Feedback($validated))->replyTo($validated['email'], $validated['name']));
        }

        // Copy to sender
        Mail::to($validated['email'])->send(new Feedback($validated));

        return back()->with('feedback_success', 'Bedankt voor je feedback! We nemen deze in behandeling.');
    }
}

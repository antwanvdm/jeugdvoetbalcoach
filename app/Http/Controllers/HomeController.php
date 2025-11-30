<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function show()
    {
        return view('home');
    }

    public function feedback(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        // Log de feedback voor nu (later kun je dit naar een database schrijven of mailen)
        Log::info('Feedback ontvangen', $validated);

        // Optioneel: Verstuur een email naar jezelf
        // Mail::to('your-email@example.com')->send(new FeedbackMail($validated));

        return back()->with('feedback_success', 'Bedankt voor je feedback! We nemen deze in behandeling.');
    }
}

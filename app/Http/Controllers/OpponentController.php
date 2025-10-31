<?php

namespace App\Http\Controllers;

use App\Models\Opponent;
use Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OpponentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        Gate::authorize('viewAny', Opponent::class);

        $opponents = Opponent::orderBy('name')->paginate(15);
        return view('opponents.index', compact('opponents'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        Gate::authorize('create', Opponent::class);

        return view('opponents.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Opponent::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'logo_file' => ['required', 'image', 'max:4096'], // upload verplicht
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
        ]);

        // Altijd uploaden; bestandsnaam met user_id prefix
        $file = $request->file('logo_file');
        $ext = $file->getClientOriginalExtension() ?: $file->extension();
        $userId = auth()->id();
        $filename = $userId . '-' . Str::slug($validated['name']) . '-' . time() . ($ext ? ('.' . $ext) : '');
        $storedLogo = $file->storeAs('opponents', $filename, 'public'); // relative path

        $payload = [
            'name' => $validated['name'],
            'location' => $validated['location'],
            'logo' => $storedLogo,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'user_id' => auth()->id(),
        ];

        $opponent = Opponent::create($payload);
        return redirect()->route('opponents.show', $opponent)->with('success', 'Tegenstander aangemaakt.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Opponent $opponent): View
    {
        Gate::authorize('view', $opponent);

        return view('opponents.show', compact('opponent'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Opponent $opponent): View
    {
        Gate::authorize('update', $opponent);

        return view('opponents.edit', compact('opponent'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Opponent $opponent): RedirectResponse
    {
        Gate::authorize('update', $opponent);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'logo_file' => ['nullable', 'image', 'max:4096'], // upload optioneel bij bewerken
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
        ]);

        $storedLogo = $opponent->logo; // behoud huidige als er geen nieuwe upload is

        if ($request->hasFile('logo_file')) {
            $file = $request->file('logo_file');
            $ext = $file->getClientOriginalExtension() ?: $file->extension();
            $userId = auth()->id();
            $filename = $userId . '-' . Str::slug($validated['name']) . '-' . time() . ($ext ? ('.' . $ext) : '');
            $newPath = $file->storeAs('opponents', $filename, 'public');

            // verwijder oude indien lokaal opgeslagen relatief pad
            if ($storedLogo && !str_starts_with($storedLogo, 'http') && !str_starts_with($storedLogo, '/storage/')) {
                Storage::disk('public')->delete($storedLogo);
            }
            $storedLogo = $newPath;
        }

        $payload = [
            'name' => $validated['name'],
            'location' => $validated['location'],
            'logo' => $storedLogo,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
        ];

        $opponent->update($payload);
        return redirect()->route('opponents.show', $opponent)->with('success', 'Tegenstander bijgewerkt.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Opponent $opponent): RedirectResponse
    {
        Gate::authorize('delete', $opponent);

        $opponent->delete();
        return redirect()->route('opponents.index')->with('success', 'Opponent deleted.');
    }
}

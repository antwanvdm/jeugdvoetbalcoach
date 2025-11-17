<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasActiveTeam
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && !session('current_team_id')) {
            // Set default team as current
            $defaultTeam = auth()->user()->defaultTeam();
            
            if ($defaultTeam) {
                session(['current_team_id' => $defaultTeam->id]);
            } else {
                // Fallback to first team if no default is set
                $firstTeam = auth()->user()->teams()->first();
                if ($firstTeam) {
                    session(['current_team_id' => $firstTeam->id]);
                }
            }
        }

        return $next($request);
    }
}

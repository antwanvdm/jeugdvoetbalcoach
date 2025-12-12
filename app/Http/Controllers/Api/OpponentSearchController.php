<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Opponent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class OpponentSearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $q = trim((string)$request->query('q', ''));
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $cacheKey = 'opponent_search:' . md5($q);
        $results = Cache::remember($cacheKey, 60, function () use ($q) {
            return Opponent::query()
                ->where(function ($w) use ($q) {
                    $w->where('name', 'like', '%' . $q . '%')
                      ->orWhere('real_name', 'like', '%' . $q . '%')
                      ->orWhere('location', 'like', '%' . $q . '%');
                })
                ->orderBy('name')
                ->limit(15)
                ->get()
                ->map(function (Opponent $o) {
                    return [
                        'id' => $o->id,
                        'name' => $o->name,
                        'location' => $o->location,
                        'logo_url' => $o->logo ? asset('storage/' . $o->logo) : null,
                    ];
                })
                ->all();
        });

        return response()->json($results);
    }
}

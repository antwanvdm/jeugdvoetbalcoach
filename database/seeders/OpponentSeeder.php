<?php

namespace Database\Seeders;

use App\Models\Opponent;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OpponentSeeder extends Seeder
{
    public function run(): void
    {
        $opponents = [
            ['name' => 'VVOR', "location" => 'Rotterdam', 'logo' => 'https://www.vvor.nl/wp-content/uploads/2015/05/vvor_logo1-188x200.png', 'latitude' => 51.93915934387992, 'longitude' => 4.531862697197812, 'kit_reference' => 1, 'real_name' => 'VVOR'],
        ];

        foreach ($opponents as $opponent) {
            $logoUrl = $opponent['logo'] ?? null;

            if ($logoUrl) {
                try {
                    $response = Http::timeout(15)->get($logoUrl);
                    if ($response->successful()) {
                        $contents = $response->body();

                        // Determine extension from content-type or URL
                        $mime = $response->header('Content-Type', '');
                        $ext = match (true) {
                            str_contains($mime, 'image/png') => 'png',
                            str_contains($mime, 'image/jpeg') => 'jpg',
                            str_contains($mime, 'image/jpg') => 'jpg',
                            str_contains($mime, 'image/gif') => 'gif',
                            default => pathinfo(parse_url($logoUrl, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION) ?: 'png',
                        };

                        $filename = Str::slug($opponent['name']) . '.' . $ext;
                        $path = 'logos/' . $filename;
                        Storage::disk('public')->put($path, $contents);
                        $opponent['logo'] = $path;
                    }
                } catch (\Throwable $e) {
                    // If download fails, keep original URL as fallback
                }
            }

            Opponent::create($opponent);
        }
    }
}

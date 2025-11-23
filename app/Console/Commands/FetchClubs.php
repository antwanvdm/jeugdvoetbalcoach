<?php

namespace App\Console\Commands;

use App\Models\Opponent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

class FetchClubs extends Command
{
    protected $signature = 'clubs:fetch';
    protected $description = 'Fetch Dutch football clubs using Google Places and store them in opponents table';

    public function handle(): int
    {
        $apiKey = config('app.apiKeys.googlePlaces');
        if (!$apiKey) {
            $this->error('Google Places API key ontbreekt (config app.apiKeys.googlePlaces).');
            return self::FAILURE;
        }

        $csvPath = storage_path('app/private/clubs.csv');
        if (!is_file($csvPath)) {
            $this->error('clubs.csv niet gevonden op: ' . $csvPath);
            return self::FAILURE;
        }

        $handle = fopen($csvPath, 'r');
        if (!$handle) {
            $this->error('Kan clubs.csv niet openen.');
            return self::FAILURE;
        }

        $this->info('Start import van clubs.csv');

        $inserted = 0;
        $updated = 0;
        $skipped = 0;
        $lineNo = 0;

        // Gebruik ; als delimiter (clubs.csv is nu ; gescheiden). Fallback als fgetcsv niets splitst.
        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $lineNo++;

            [$name, $location, $kitRef] = array_pad($row, 3, null);
            $name = trim($name ?? '');
            $location = trim($location ?? '');
            $kitRef = trim($kitRef ?? '');

            $slug = Str::slug($name . '-' . $location);

            // Check of opponent al bestaat (match op name + location)
            $opponent = Opponent::where('name', $name)->where('location', $location)->first();

            // Verrijk via Google Places
            $geo = null;
            $website = null;
            try {
                $query = urlencode($name . ' ' . $location . ' Netherlands');
                $searchUrl = 'https://maps.googleapis.com/maps/api/place/textsearch/json?query=' . $query . '&key=' . $apiKey;
                $searchResp = Http::timeout(10)->get($searchUrl);
                if ($searchResp->ok() && ($searchResp->json('results.0.place_id'))) {
                    $placeId = $searchResp->json('results.0.place_id');
                    $detailsUrl = 'https://maps.googleapis.com/maps/api/place/details/json?place_id=' . $placeId . '&fields=website,geometry&key=' . $apiKey;
                    $detailsResp = Http::timeout(10)->get($detailsUrl);
                    if ($detailsResp->ok()) {
                        $geo = $detailsResp->json('result.geometry.location');
                        $website = $detailsResp->json('result.website');
                    }
                }else {
                    $this->warn($query);
                }
            } catch (\Throwable $e) {
                $this->warn("[$slug] Fout bij Places API: " . $e->getMessage());
            }

            $latitude = $geo['lat'] ?? null;
            $longitude = $geo['lng'] ?? null;

            // Logo scraping
            $logoPathRelative = null;
            if ($website) {
                try {
                    $htmlResp = Http::timeout(10)->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (compatible; VVORBot/1.0; +https://example.com/bot)'
                    ])->get($website);
                    if ($htmlResp->ok()) {
                        $crawler = new Crawler($htmlResp->body(), $website);
                        $logoUrl = $this->extractLogoUrl($crawler, $website);
                        if ($logoUrl) {
                            $logoContentResp = Http::timeout(15)->get($logoUrl);
                            if ($logoContentResp->ok()) {
                                $mime = $logoContentResp->header('Content-Type');
                                $ext = $this->determineExtension($logoUrl, $mime);
                                $finalFilename = $slug . ($ext ? '.' . $ext : '.png');
                                $diskPath = 'logos/' . $finalFilename;
                                Storage::disk('public')->put($diskPath, $logoContentResp->body());
                                $logoPathRelative = $diskPath;
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    $this->warn("[$slug] Logo scraping mislukt: " . $e->getMessage());
                }
            }

            $data = [
                'name' => $name,
                'location' => $location,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'logo' => $logoPathRelative ?? $opponent?->logo,
                'kit_reference' => $kitRef !== '' ? $kitRef : ($opponent?->kit_reference ?? null),
            ];

            if ($opponent) {
                $opponent->update($data);
                $updated++;
                $this->line("[UPDATE] $name ($location)");
            } else {
                Opponent::create($data);
                $inserted++;
                $this->line("[CREATE] $name ($location)");
            }

            // Klein throttle om limieten te respecteren
            usleep(250_000); // 250ms
        }

        fclose($handle);

        $this->info("Klaar. Nieuw: $inserted | Bijgewerkt: $updated | Overgeslagen: $skipped");
        return self::SUCCESS;
    }

    private function extractLogoUrl(Crawler $crawler, string $baseUrl): ?string
    {
        // 1. img met class/id/alt/title bevat 'logo'
        $img = $crawler->filter('img')->reduce(function (Crawler $node) {
            $haystack = strtolower($node->attr('class') . ' ' . $node->attr('id') . ' ' . $node->attr('alt') . ' ' . $node->attr('title'));
            return str_contains($haystack, 'logo');
        })->first();
        if ($img->count()) {
            return $this->absoluteUrl($img->attr('src'), $baseUrl);
        }
        // 2. link rel icon
        $icon = $crawler->filter('link[rel*="icon"]')->first();
        if ($icon->count()) {
            return $this->absoluteUrl($icon->attr('href'), $baseUrl);
        }
        // 3. grootste img in header/nav
        $headerImgs = $crawler->filter('header img, nav img');
        $best = null;
        $bestSize = 0;
        foreach ($headerImgs as $node) {
            $src = $node->getAttribute('src');
            if (!$src) continue;
            $sizeGuess = strlen($src); // simpele heuristiek
            if ($sizeGuess > $bestSize) {
                $bestSize = $sizeGuess;
                $best = $src;
            }
        }
        if ($best) {
            return $this->absoluteUrl($best, $baseUrl);
        }
        return null;
    }

    private function absoluteUrl(?string $src, string $base): ?string
    {
        if (!$src) return null;
        if (Str::startsWith($src, ['http://', 'https://'])) return $src;
        // Protocol-relative
        if (Str::startsWith($src, '//')) {
            $scheme = parse_url($base, PHP_URL_SCHEME) ?: 'https';
            return $scheme . ':' . $src;
        }
        // Relative path
        $baseParts = parse_url($base);
        $host = $baseParts['scheme'] . '://' . $baseParts['host'] . (isset($baseParts['port']) ? ':' . $baseParts['port'] : '');
        if (Str::startsWith($src, '/')) {
            return $host . $src;
        }
        // trim possible path
        $path = isset($baseParts['path']) ? rtrim(dirname($baseParts['path']), '/\\') : '';
        return $host . ($path ? '/' . $path : '') . '/' . $src;
    }

    private function determineExtension(string $url, ?string $mime): string
    {
        if ($mime) {
            return match ($mime) {
                'image/svg+xml' => 'svg',
                'image/png' => 'png',
                'image/jpeg', 'image/jpg' => 'jpg',
                'image/gif' => 'gif',
                default => 'png'
            };
        }
        $path = parse_url($url, PHP_URL_PATH);
        if ($path && preg_match('/\.(svg|png|jpe?g|gif)$/i', $path, $m)) {
            return strtolower($m[1] === 'jpeg' ? 'jpg' : $m[1]);
        }
        return 'png';
    }
}

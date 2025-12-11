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

        // Use ; as delimiter (clubs.csv is now ; separated). Fallback if fgetcsv doesn't split.
        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            [$name, $location, $kitRef] = array_pad($row, 3, null);
            $name = trim($this->stripBom($name) ?? '');
            $location = trim($this->stripBom($location) ?? '');
            $kitRef = trim($this->stripBom($kitRef) ?? '');

            $slug = Str::slug($name . '-' . $location);

            // Check of opponent al bestaat (match op name + location)
            $opponent = Opponent::where('name', $name)->where('location', $location)->first();

            // Verrijk via Google Places
//            $geo = null;
//            try {
//                $query = urlencode($name . ' ' . $location . ' Netherlands');
//                $searchUrl = 'https://maps.googleapis.com/maps/api/place/textsearch/json?query=' . $query . '&key=' . $apiKey;
//                $searchResp = Http::timeout(10)->get($searchUrl);
//                if ($searchResp->ok() && ($searchResp->json('results.0.place_id'))) {
//                    $placeId = $searchResp->json('results.0.place_id');
//                    $detailsUrl = 'https://maps.googleapis.com/maps/api/place/details/json?place_id=' . $placeId . '&fields=geometry&key=' . $apiKey;
//                    $detailsResp = Http::timeout(10)->get($detailsUrl);
//                    if ($detailsResp->ok()) {
//                        $geo = $detailsResp->json('result.geometry.location');
//                    }
//                }else {
//                    $this->warn($query);
//                }
//            } catch (\Throwable $e) {
//                $this->warn("[$slug] Fout bij Places API: " . $e->getMessage());
//            }
//
//            $latitude = $geo['lat'] ?? 0.0;
//            $longitude = $geo['lng'] ?? 0.0;

            // Logo scraping from Hollandse Velden website
            $logoPathRelative = null;
            try {
                // Build the club URL based on the first letter of the team name
                $teamSlug = Str::slug($name);
                $firstLetter = strtolower(substr($teamSlug, 0, 1));
                $clubUrl = "https://www.hollandsevelden.nl/clubs/{$firstLetter}/{$teamSlug}/";

                $allowedDomains = ['www.hollandsevelden.nl', 'hollandsevelden.nl', 'cdn.hollandsevelden.nl'];

                $htmlResp = Http::timeout(10)->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; VVORBot/1.0; +https://example.com/bot)'
                ])->get($clubUrl);

                if ($htmlResp->ok()) {
                    $crawler = new Crawler($htmlResp->body(), $clubUrl);
                    $logoUrl = $this->extractHollandseVeldenLogo($crawler, $clubUrl);
                    $host = $logoUrl ? (parse_url($logoUrl, PHP_URL_HOST) ?? '') : '';
                    if ($logoUrl && in_array($host, $allowedDomains, true)) {
                        $logoContentResp = Http::timeout(15)->get($logoUrl);
                        if ($logoContentResp->ok()) {
                            $contentType = $logoContentResp->header('Content-Type');
                            if (!is_string($contentType) || !str_starts_with($contentType, 'image/')) {
                                $this->warn("[$slug] Ongeldig content-type voor logo: {$contentType}");
                            } else {
                                $finalFilename = $slug . '.webp';
                                $diskPath = 'logos/' . $finalFilename;
                                Storage::disk('public')->put($diskPath, $logoContentResp->body());
                                $logoPathRelative = $diskPath;
                                $this->info("[$slug] Logo opgeslagen: $diskPath");
                            }
                        }
                    } elseif ($logoUrl) {
                        $this->warn("[$slug] Domein niet toegestaan voor logo: {$logoUrl}");
                    } else {
                        $this->warn("[$slug] Geen logo gevonden op Hollandse Velden pagina");
                    }
                } else {
                    $this->warn("[$slug] Hollandse Velden pagina niet bereikbaar: $clubUrl");
                }
            } catch (\Throwable $e) {
                $this->warn("[$slug] Logo scraping mislukt: " . $e->getMessage());
            }

            $data = [
                'name' => $name,
                'location' => $location,
                'latitude' => $latitude ?? $opponent?->latitude,
                'longitude' => $longitude ?? $opponent?->longitude,
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

        $this->info("Klaar. Nieuw: $inserted | Bijgewerkt: $updated");
        return self::SUCCESS;
    }

    private function extractHollandseVeldenLogo(Crawler $crawler, string $baseUrl): ?string
    {
        // Look for <p class="logo"> containing a <picture> element with <source> webp
        try {
            $logoP = $crawler->filter('p.logo')->first();
            if ($logoP->count()) {
                $source = $logoP->filter('picture source[type="image/webp"]')->first();
                if ($source->count()) {
                    $srcset = $source->attr('srcset');
                    if ($srcset) {
                        // srcset can contain multiple URLs, take the first one
                        $url = explode(' ', trim($srcset))[0];
                        return $this->absoluteUrl($url, $baseUrl);
                    }
                }
            }
        } catch (\Throwable $e) {
            // If the specific selector fails, return null
            return null;
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

    private function stripBom(?string $value): ?string
    {
        if ($value === null) return null;
        // UTF-8 BOM bytes EF BB BF
        if (str_starts_with($value, "\xEF\xBB\xBF")) {
            return substr($value, 3);
        }
        return $value;
    }
}

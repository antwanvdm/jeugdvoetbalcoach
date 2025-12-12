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
    protected $description = 'Fetch Dutch football clubs using CSV + web scraping and store them in opponents table';

    public function handle(): int
    {
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

            // Build the club URL based on the first letter of the team name
            $teamSlug = Str::slug($name);
            $firstLetter = strtolower(substr($teamSlug, 0, 1));
            $clubUrl = "https://www.hollandsevelden.nl/clubs/{$firstLetter}/{$teamSlug}/";

            // Logo scraping from Hollandse Velden website
            $logoPathRelative = null;
            // Only fetch logo if opponent doesn't already have one
            if (!$opponent || !$opponent->logo) {
                try {
                    $allowedDomains = ['www.hollandsevelden.nl', 'hollandsevelden.nl', 'cdn.hollandsevelden.nl'];

                    $htmlResp = Http::timeout(10)->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (compatible; FetchBot/1.0; +https://example.com/bot)'
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
            }

            // Scrape real name, address and website from Hollandse Velden
            $realName = null;
            $address = null;
            $website = null;
            // Only scrape real name, address and website if the opponent doesn't have them
            if (!$opponent || !$opponent->address || !$opponent->address || !$opponent->real_name) {
                try {
                    $htmlResp = Http::timeout(10)->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (compatible; FetchBot/1.0; +https://example.com/bot)'
                    ])->get($clubUrl);

                    if ($htmlResp->ok()) {
                        $crawler = new Crawler($htmlResp->body(), $clubUrl);

                        // Extract address from <address> element
                        $addressElement = $crawler->filter('address')->first();
                        if ($addressElement->count()) {
                            $addressHtml = $addressElement->html();
                            // Extract real_name from <strong> tags before removing them
                            if (preg_match('/<strong[^>]*>(.*?)<\/strong>/i', $addressHtml, $matches)) {
                                $realNameRaw = strip_tags($matches[1]);
                                $realName = trim(preg_replace('/\s+/', ' ', $realNameRaw)) ?: null;
                            }
                            // Remove <strong> tags and their content (club name)
                            $addressHtml = preg_replace('/<strong[^>]*>.*?<\/strong>/i', '', $addressHtml);
                            // Remove everything up to and including "Sportpark ...," pattern and the <br> tag
                            $addressHtml = preg_replace('/.*?Sportpark\s+[^<]+<br\s*\/?>/i', '', $addressHtml);
                            // Replace <br> tags with commas
                            $addressText = preg_replace('/<br\s*\/?>/i', ', ', $addressHtml);
                            // Strip remaining HTML tags
                            $addressText = strip_tags($addressText);
                            // Clean up non-breaking spaces and multiple spaces
                            $addressText = str_replace("\u{A0}", ' ', $addressText);
                            $addressText = trim(preg_replace('/\s+/', ' ', $addressText));
                            $addressText = trim($addressText, ', ');
                            $address = $addressText ?: null;

                            // Extract website from p > a[target="_blank"] after address
                            $pAfterAddress = $addressElement->nextAll()->filter('p')->first();
                            if ($pAfterAddress->count()) {
                                $websiteLink = $pAfterAddress->filter('a[target="_blank"]')->first();
                                if ($websiteLink->count()) {
                                    $website = $websiteLink->attr('href');
                                }
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    $this->warn("[$slug] Address/website scraping mislukt: " . $e->getMessage());
                }
            }
            dump($address);

            $data = [
                'name' => $name,
                'real_name' => $realName ?? $opponent?->real_name,
                'location' => $location,
                'address' => $address ?? $opponent?->address,
                'website' => $website ?? $opponent?->website,
                'latitude' => $opponent?->latitude ?? 0,
                'longitude' => $opponent?->longitude ?? 0,
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

            // Limit throttle
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

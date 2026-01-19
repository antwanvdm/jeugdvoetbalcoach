<?php

namespace App\Console\Commands;

use App\Jobs\SendPromotionEmail;
use App\Models\Opponent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class CrawlOpponentEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:crawl-opponent-emails {--dry-run : Only show what would be sent} {--limit= : Limit the number of opponents to process} {--delay=0 : Delay in seconds between dispatching jobs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawl opponent websites for email addresses and send promotion emails';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = Opponent::whereNotNull('website')->where('website', '!=', '');

        if ($this->option('limit')) {
            $query->limit($this->option('limit'));
        }

        $opponents = $query->get();

        $this->info("Found {$opponents->count()} opponents with websites.");

        $foundCount = 0;
        $dispatchedCount = 0;
        $errorCount = 0;

        foreach ($opponents as $opponent) {
            $this->line("Processing: {$opponent->name} ({$opponent->website})");

            $email = $this->getEmailFromWebsite($opponent->website);

            if ($email) {
                $this->info("  Found email: {$email}");
                $foundCount++;

                if ($this->option('dry-run')) {
                    $this->comment("  [Dry-run] Would dispatch email to {$email}");
                } else {
                    try {
                        SendPromotionEmail::dispatch($email);
                        $this->info("  Dispatched email to {$email}");
                        $dispatchedCount++;

                        if ($this->option('delay') > 0) {
                            sleep((int) $this->option('delay'));
                        }
                    } catch (\Exception $e) {
                        $this->error("  Failed to dispatch email for {$opponent->name}: " . $e->getMessage());
                        $errorCount++;
                    }
                }
            } else {
                $this->warn("  No email found for {$opponent->name}");
            }
        }

        $this->newLine();
        $this->info('Finished processing.');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Opponents processed', $opponents->count()],
                ['Emails found', $foundCount],
                ['Emails dispatched', $dispatchedCount],
                ['Errors', $errorCount],
            ]
        );
    }

    private function getEmailFromWebsite(string $url): ?string
    {
        try {
            if (!str_starts_with($url, 'http')) {
                $url = 'https://' . $url;
            }

            $response = Http::timeout(10)->get($url);

            if (!$response->successful()) {
                return null;
            }

            $html = $response->body();
            $email = $this->extractEmailFromHtml($html);

            if ($email) {
                return $email;
            }

            // No email on homepage, look for a contact page
            $this->line("    No email on homepage, searching for contact page...");
            $crawler = new Crawler($html);
            $contactLink = $crawler->filter('a')->reduce(function (Crawler $node) {
                $text = strtolower($node->text());
                $href = strtolower($node->attr('href') ?? '');

                // Skip PDF links or other non-html files
                if (preg_match('/\.(pdf|jpg|jpeg|png|gif|doc|docx|xls|xlsx)$/i', $href)) {
                    return false;
                }

                return str_contains($text, 'contact') || str_contains($href, 'contact');
            })->first();

            if ($contactLink->count() > 0) {
                $contactUrl = $contactLink->attr('href');

                // Make sure it's an absolute URL
                if (!str_starts_with($contactUrl, 'http')) {
                    $base = rtrim($url, '/');
                    $contactUrl = $base . '/' . ltrim($contactUrl, '/');
                }

                $this->line("    Found contact page: {$contactUrl}");
                $contactResponse = Http::timeout(10)->get($contactUrl);

                if ($contactResponse->successful() && str_contains($contactResponse->header('Content-Type'), 'text/html')) {
                    $email = $this->extractEmailFromHtml($contactResponse->body());
                    if ($email) {
                        return $email;
                    }
                } else {
                    $this->warn("    Skipping non-HTML contact page or failed request.");
                }
            }

        } catch (\Exception $e) {
            $this->error("  Error crawling {$url}: " . $e->getMessage());
        }

        return null;
    }

    private function extractEmailFromHtml(string $html): ?string
    {
        // Try to find email in mailto links first
        $crawler = new Crawler($html);
        $mailtos = $crawler->filter('a[href^="mailto:"]');

        if ($mailtos->count() > 0) {
            $email = str_replace('mailto:', '', $mailtos->first()->attr('href'));
            // Clean up possible query params
            $email = explode('?', $email)[0];
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return trim($email);
            }
        }

        // Fallback to regex on the whole body
        preg_match_all('/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}/i', $html, $matches);

        foreach ($matches[0] as $match) {
            if (filter_var($match, FILTER_VALIDATE_EMAIL)) {
                // Ignore obvious placeholders
                if (preg_match('/example|yourname|domain|naam|domein/i', $match)) {
                    continue;
                }
                return trim($match);
            }
        }

        return null;
    }
}

<?php

namespace App\Jobs;

use App\Mail\PromotionEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\Middleware\RateLimited;

use Illuminate\Support\Facades\Storage;

class SendPromotionEmail implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 0;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     *
     * @var int
     */
    public $maxExceptions = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $email, public ?string $opponentName = null)
    {
        //
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [new RateLimited('promotion-emails')];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->email)->send(new PromotionEmail($this->opponentName));

        $this->logSuccess();
    }

    private function logSuccess(): void
    {
        $logPath = 'sent_emails.json';
        $sentEmails = [];

        if (Storage::exists($logPath)) {
            $sentEmails = json_decode(Storage::get($logPath), true) ?? [];
        }

        $sentEmails[] = $this->email;

        Storage::put($logPath, json_encode(array_values(array_unique($sentEmails)), JSON_PRETTY_PRINT));
    }
}

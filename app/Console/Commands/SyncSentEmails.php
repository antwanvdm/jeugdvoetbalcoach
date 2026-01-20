<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SyncSentEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-sent-emails {--dry-run : Only show what would be removed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove emails from sent_emails.json that are found in failed_jobs table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $logPath = 'sent_emails.json';

        if (!Storage::exists($logPath)) {
            $this->error("File {$logPath} not found.");
            return 1;
        }

        $sentEmails = json_decode(Storage::get($logPath), true) ?? [];
        $initialCount = count($sentEmails);

        $this->info("Currently {$initialCount} emails in {$logPath}.");

        // Fetch all failed jobs
        $failedJobs = DB::table('failed_jobs')->get();

        if ($failedJobs->isEmpty()) {
            $this->info("No failed jobs found in database.");
            return 0;
        }

        $failedEmails = [];
        foreach ($failedJobs as $job) {
            $payload = json_decode($job->payload, true);
            $command = unserialize($payload['data']['command']);

            if (isset($command->email)) {
                $failedEmails[] = $command->email;
            }
        }

        $failedEmails = array_unique($failedEmails);
        $this->info("Found " . count($failedEmails) . " unique email addresses in failed_jobs.");

        $toRemove = array_intersect($sentEmails, $failedEmails);
        $removedCount = count($toRemove);

        if ($removedCount === 0) {
            $this->info("No emails from the JSON were found in failed_jobs.");
            return 0;
        }

        $this->warn("Found {$removedCount} emails to remove from JSON.");

        foreach ($toRemove as $email) {
            $this->line(" - To remove: {$email}");
        }

        if ($this->option('dry-run')) {
            $this->info("[Dry-run] No changes made.");
            return 0;
        }

        if ($this->confirm("Are you sure you want to remove these {$removedCount} emails from {$logPath}?")) {
            $newSentEmails = array_values(array_diff($sentEmails, $failedEmails));
            Storage::put($logPath, json_encode($newSentEmails, JSON_PRETTY_PRINT));
            $this->info("Successfully updated {$logPath}. New count: " . count($newSentEmails));
        }

        return 0;
    }
}

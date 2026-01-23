<?php

namespace App\Console\Commands;

use App\Jobs\SendMemberEmailJob;
use App\Models\User;
use Illuminate\Console\Command;

class SendMemberEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:send-member-emails {mailable : De mailable class name (zonder App\Mail\)} {--dry-run : Alleen tonen naar wie de mail zou gaan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verstuur een specifieke mail naar alle geregistreerde gebruikers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $mailableName = $this->argument('mailable');
        $mailableClass = $mailableName;

        if (!str_contains($mailableClass, '\\')) {
            $mailableClass = "App\\Mail\\{$mailableClass}";
        }

        if (!class_exists($mailableClass)) {
            $this->error("Mailable class {$mailableClass} niet gevonden.");
            return 1;
        }

        $users = User::where('updates_opt_out', false)
            ->where('role', '!=', 1)
            ->get();

        if ($users->isEmpty()) {
            $this->info('Geen gebruikers gevonden.');
            return 0;
        }

        $this->info("Er worden {$users->count()} emails ingepland met class: {$mailableClass}...");

        if ($this->option('dry-run')) {
            foreach ($users as $user) {
                $this->line("Dry-run: Mail ({$mailableClass}) zou verstuurd worden naar: {$user->email} ({$user->name})");
            }
            $this->info('Dry-run voltooid. Er zijn geen emails verstuurd of jobs aangemaakt.');
            return 0;
        }

        $bar = $this->output->createProgressBar($users->count());

        $bar->start();

        foreach ($users as $user) {
            SendMemberEmailJob::dispatch($user->email, $user->name, $mailableClass);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info('Alle jobs zijn succesvol aangemaakt in de queue.');

        return 0;
    }
}

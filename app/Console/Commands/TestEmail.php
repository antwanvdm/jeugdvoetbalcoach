<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test-email {mailable : De mailable class name (zonder App\Mail\)} {email : Het emailadres waar de test naar toe moet} {--name=Test Gebruiker : De naam die gebruikt wordt in de mail}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verstuur een test email naar een specifiek adres';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $mailableName = $this->argument('mailable');
        $email = $this->argument('email');
        $name = $this->option('name');

        $mailableClass = $mailableName;

        if (!str_contains($mailableClass, '\\')) {
            $mailableClass = "App\\Mail\\{$mailableClass}";
        }

        if (!class_exists($mailableClass)) {
            $this->error("Mailable class {$mailableClass} niet gevonden.");
            return 1;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error("Ongeldig emailadres: {$email}");
            return 1;
        }

        $this->info("Sending test email ({$mailableClass}) to {$email}...");

        try {
            // We proberen de mailable te instantiëren met de naam als parameter
            // Sommige mailables verwachten een naam (zoals WelcomeMemberEmail of PromotionEmail)
            \Illuminate\Support\Facades\Mail::to($email)->send(new $mailableClass($name));
            $this->info('Email succesvol verzonden!');
        } catch (\Exception $e) {
            $this->error('Verzenden mislukt: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}

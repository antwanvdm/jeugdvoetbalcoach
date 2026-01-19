<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestPromotionEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-promotion-email {email : The email address to send the test to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test promotion email to a specific address';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error("Invalid email address: {$email}");
            return 1;
        }

        $this->info("Sending test promotion email to {$email}...");

        try {
            \Illuminate\Support\Facades\Mail::to($email)->send(new \App\Mail\PromotionEmail());
            $this->info('Email sent successfully!');
        } catch (\Exception $e) {
            $this->error('Failed to send email: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}

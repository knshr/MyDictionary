<?php

namespace App\Console\Commands;

use App\Jobs\SendOtpEmail;
use App\Models\User;
use Illuminate\Console\Command;

class TestOtpEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:otp-email {email} {--type=login}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test OTP email sending via queue';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $type = $this->option('type');

        // Find or create a test user
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email {$email} not found.");
            return 1;
        }

        // Generate a test OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $this->info("Dispatching OTP email job for user: {$user->name} ({$email})");
        $this->info("OTP Code: {$otp}");
        $this->info("Type: {$type}");

        // Dispatch the job
        SendOtpEmail::dispatch($user, $otp, $type);

        $this->info('OTP email job dispatched successfully!');
        $this->info('Check the queue worker to process the job.');
        $this->info('Email will be logged to storage/logs/laravel.log');

        return 0;
    }
}

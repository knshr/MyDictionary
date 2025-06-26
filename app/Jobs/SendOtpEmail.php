<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendOtpEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private User $user,
        private string $otp,
        private string $type = 'login'
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $subject = match($this->type) {
                'login' => 'Login Verification Code',
                'register' => 'Registration Verification Code',
                'password_reset' => 'Password Reset Code',
                default => 'Verification Code'
            };

            $expiresIn = config('otp.expires_in');

            Mail::send('emails.otp', [
                'user' => $this->user,
                'otp' => $this->otp,
                'type' => $this->type,
                'subject' => $subject,
                'expiresIn' => $expiresIn
            ], function ($message) use ($subject) {
                $message->to($this->user->email)
                        ->subject($subject);
            });

            Log::info('OTP email sent successfully', [
                'user_id' => $this->user->id,
                'email' => $this->user->email,
                'type' => $this->type
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send OTP email', [
                'user_id' => $this->user->id,
                'email' => $this->user->email,
                'type' => $this->type,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('OTP email job failed', [
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'type' => $this->type,
            'error' => $exception->getMessage(),
        ]);
    }
}

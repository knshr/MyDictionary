<?php

namespace App\Services;

use App\Models\User;
use App\Models\OtpCode;
use App\Jobs\SendOtpEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class OtpService
{
    /**
     * Generate and send OTP to user.
     */
    public function generateAndSendOtp(User $user, string $type = 'login'): bool
    {
        // Check request limits
        $this->checkRequestLimits($user, $type);

        // Check resend cooldown
        $this->checkResendCooldown($user, $type);

        // Invalidate previous OTP codes
        $this->invalidatePreviousOtps($user, $type);

        // Generate new OTP
        $otp = $this->generateOtp();
        $expiresAt = Carbon::now()->addMinutes(config('otp.expires_in'));

        // Create OTP record
        $otpCode = OtpCode::create([
            'user_id' => $user->id,
            'code' => $otp,
            'type' => $type,
            'expires_at' => $expiresAt,
        ]);

        // Dispatch OTP email job
        return $this->dispatchOtpEmail($user, $otp, $type);
    }

    /**
     * Check if user has exceeded the maximum OTP requests.
     */
    private function checkRequestLimits(User $user, string $type): void
    {
        $maxRequests = config('otp.max_requests');
        $requestWindow = config('otp.request_window');

        $recentOtps = $user->otpCodes()
                           ->type($type)
                           ->where('created_at', '>=', Carbon::now()->subMinutes($requestWindow))
                           ->count();

        if ($recentOtps >= $maxRequests) {
            $remainingTime = $this->getRemainingTimeForNextRequest($user, $type, $requestWindow);

            throw ValidationException::withMessages([
                'otp' => [
                    "You have reached the maximum limit of {$maxRequests} OTP requests within {$requestWindow} minutes. " .
                    "Please wait {$remainingTime} minutes before requesting another OTP."
                ]
            ]);
        }
    }

    /**
     * Check if user is within the resend cooldown period.
     */
    private function checkResendCooldown(User $user, string $type): void
    {
        $cooldownSeconds = config('otp.resend_cooldown');

        $latestOtp = $user->otpCodes()
                          ->type($type)
                          ->latest()
                          ->first();

        if ($latestOtp && $latestOtp->created_at->addSeconds($cooldownSeconds)->isFuture()) {
            $remainingSeconds = Carbon::now()->diffInSeconds($latestOtp->created_at->addSeconds($cooldownSeconds));

            throw ValidationException::withMessages([
                'otp' => [
                    "Please wait {$remainingSeconds} seconds before requesting another OTP."
                ]
            ]);
        }
    }

    /**
     * Get remaining time for next OTP request.
     */
    private function getRemainingTimeForNextRequest(User $user, string $type, int $requestWindow): int
    {
        $oldestOtpInWindow = $user->otpCodes()
                                  ->type($type)
                                  ->where('created_at', '>=', Carbon::now()->subMinutes($requestWindow))
                                  ->oldest()
                                  ->first();

        if (!$oldestOtpInWindow) {
            return 0;
        }

        $windowEnd = $oldestOtpInWindow->created_at->addMinutes($requestWindow);
        return max(0, Carbon::now()->diffInMinutes($windowEnd));
    }

    /**
     * Validate OTP code.
     */
    public function validateOtp(User $user, string $code, string $type = 'login'): bool
    {
        $otpCode = $user->otpCodes()
                        ->type($type)
                        ->valid()
                        ->where('code', $code)
                        ->first();

        if (!$otpCode) {
            return false;
        }

        // Mark OTP as used
        $otpCode->markAsUsed();

        return true;
    }

    /**
     * Generate a 6-digit OTP.
     */
    private function generateOtp(): string
    {
        $length = config('otp.length', 6);
        return str_pad(random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
    }

    /**
     * Invalidate previous OTP codes for the user.
     */
    private function invalidatePreviousOtps(User $user, string $type): void
    {
        $user->otpCodes()
             ->type($type)
             ->valid()
             ->update(['used_at' => now()]);
    }

    /**
     * Dispatch OTP email job.
     */
    private function dispatchOtpEmail(User $user, string $otp, string $type): bool
    {
        try {
            SendOtpEmail::dispatch($user, $otp, $type);
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to dispatch OTP email job', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send OTP email to user (legacy method - now uses job).
     */
    private function sendOtpEmail(User $user, string $otp, string $type): bool
    {
        return $this->dispatchOtpEmail($user, $otp, $type);
    }

    /**
     * Check if user has a valid OTP.
     */
    public function hasValidOtp(User $user, string $type = 'login'): bool
    {
        return $user->otpCodes()
                    ->type($type)
                    ->valid()
                    ->exists();
    }

    /**
     * Get remaining time for OTP expiration.
     */
    public function getOtpRemainingTime(User $user, string $type = 'login'): ?int
    {
        $otpCode = $user->getLatestValidOtp($type);

        if (!$otpCode) {
            return null;
        }

        return max(0, Carbon::now()->diffInSeconds($otpCode->expires_at));
    }

    /**
     * Get remaining cooldown time for resend.
     */
    public function getResendCooldownRemaining(User $user, string $type = 'login'): ?int
    {
        $cooldownSeconds = config('otp.resend_cooldown');

        $latestOtp = $user->otpCodes()
                          ->type($type)
                          ->latest()
                          ->first();

        if (!$latestOtp || $latestOtp->created_at->addSeconds($cooldownSeconds)->isPast()) {
            return 0;
        }

        return Carbon::now()->diffInSeconds($latestOtp->created_at->addSeconds($cooldownSeconds));
    }

    /**
     * Get remaining requests available for the user.
     */
    public function getRemainingRequests(User $user, string $type = 'login'): array
    {
        $maxRequests = config('otp.max_requests');
        $requestWindow = config('otp.request_window');

        $recentOtps = $user->otpCodes()
                           ->type($type)
                           ->where('created_at', '>=', Carbon::now()->subMinutes($requestWindow))
                           ->count();

        $remainingRequests = max(0, $maxRequests - $recentOtps);
        $nextRequestTime = $this->getRemainingTimeForNextRequest($user, $type, $requestWindow);

        return [
            'remaining_requests' => $remainingRequests,
            'max_requests' => $maxRequests,
            'request_window_minutes' => $requestWindow,
            'next_request_available_in_minutes' => $nextRequestTime,
        ];
    }
}

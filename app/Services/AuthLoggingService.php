<?php

namespace App\Services;

use App\Models\AuthLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuthLoggingService
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Log authentication event.
     */
    public function logAuthEvent(
        string $action,
        string $email,
        ?User $user = null,
        bool $success = true,
        ?string $failureReason = null
    ): AuthLog {
        $metadata = [
            'device' => $this->getDeviceInfo(),
            'location' => $this->getLocationInfo(),
            'timestamp' => now()->toISOString(),
        ];

        return AuthLog::create([
            'user_id' => $user?->id,
            'email' => $email,
            'action' => $action,
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
            'metadata' => $metadata,
            'success' => $success,
            'failure_reason' => $failureReason,
        ]);
    }

    /**
     * Log successful login.
     */
    public function logSuccessfulLogin(User $user): AuthLog
    {
        return $this->logAuthEvent('login', $user->email, $user, true);
    }

    /**
     * Log failed login attempt.
     */
    public function logFailedLogin(string $email, string $reason = 'Invalid credentials'): AuthLog
    {
        return $this->logAuthEvent('failed_login', $email, null, false, $reason);
    }

    /**
     * Log successful registration.
     */
    public function logSuccessfulRegistration(User $user): AuthLog
    {
        return $this->logAuthEvent('register', $user->email, $user, true);
    }

    /**
     * Log logout.
     */
    public function logLogout(User $user): AuthLog
    {
        return $this->logAuthEvent('logout', $user->email, $user, true);
    }

    /**
     * Log OTP verification.
     */
    public function logOtpVerification(User $user, bool $success, ?string $reason = null): AuthLog
    {
        $action = $success ? 'otp_verified' : 'otp_failed';
        return $this->logAuthEvent($action, $user->email, $user, $success, $reason);
    }

    /**
     * Get device information from user agent.
     */
    private function getDeviceInfo(): array
    {
        $userAgent = $this->request->userAgent();

        // Basic device detection (you can use a more sophisticated library)
        $isMobile = preg_match('/(android|iphone|ipad|mobile)/i', $userAgent);
        $isTablet = preg_match('/(ipad|tablet)/i', $userAgent);

        return [
            'is_mobile' => $isMobile,
            'is_tablet' => $isTablet,
            'is_desktop' => !$isMobile && !$isTablet,
            'user_agent' => $userAgent,
        ];
    }

    /**
     * Get location information (basic implementation).
     */
    private function getLocationInfo(): array
    {
        // This is a basic implementation
        // You can integrate with IP geolocation services for more accurate data
        return [
            'ip_address' => $this->request->ip(),
            'country' => null, // Can be populated with IP geolocation
            'city' => null,    // Can be populated with IP geolocation
        ];
    }

    /**
     * Get authentication statistics for a user.
     */
    public function getUserAuthStats(User $user, int $days = 30): array
    {
        $startDate = now()->subDays($days);

        $stats = AuthLog::where('user_id', $user->id)
                        ->where('created_at', '>=', $startDate)
                        ->selectRaw('action, COUNT(*) as count, success')
                        ->groupBy('action', 'success')
                        ->get();

        return [
            'total_logins' => $stats->where('action', 'login')->where('success', true)->sum('count'),
            'failed_logins' => $stats->where('action', 'failed_login')->sum('count'),
            'total_logouts' => $stats->where('action', 'logout')->sum('count'),
            'otp_verifications' => $stats->where('action', 'otp_verified')->sum('count'),
            'failed_otp' => $stats->where('action', 'otp_failed')->sum('count'),
            'period_days' => $days,
        ];
    }
}

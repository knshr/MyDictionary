<?php

namespace App\Services;

use App\Http\Resources\UserResource;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private OtpService $otpService,
        private AuthLoggingService $authLoggingService
    ) {}

    public function login(array $credentials, bool $remember = false): array
    {
        $user = $this->userRepository->findByEmail($credentials['email']);

        if (!$user || !Auth::attempt($credentials, $remember)) {
            // Log failed login attempt
            $this->authLoggingService->logFailedLogin($credentials['email']);

            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Log successful login
        $this->authLoggingService->logSuccessfulLogin($user);

        // Generate and send OTP
        $otpSent = $this->otpService->generateAndSendOtp($user, 'login');

        return [
            'user' => new UserResource($user),
            'message' => 'Login successful. Please check your email for OTP verification.',
            'requires_otp' => true,
            'otp_sent' => $otpSent,
        ];
    }

    public function verifyOtp(string $email, string $otp): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['User not found.'],
            ]);
        }

        $isValid = $this->otpService->validateOtp($user, $otp, 'login');

        if (!$isValid) {
            // Log failed OTP verification
            $this->authLoggingService->logOtpVerification($user, false, 'Invalid OTP');

            throw ValidationException::withMessages([
                'otp' => ['Invalid or expired OTP code.'],
            ]);
        }

        // Log successful OTP verification
        $this->authLoggingService->logOtpVerification($user, true);

        // Mark email as verified if not already
        if (!$user->isEmailVerified()) {
            $this->userRepository->update($user->id, ['email_verified_at' => now()]);
            $user->refresh();
        }

        return [
            'user' => new UserResource($user),
            'message' => 'OTP verified successfully.',
            'email_verified' => true,
        ];
    }

    public function register(array $data): array
    {
        $user = $this->userRepository->create($data);

        // Log successful registration
        $this->authLoggingService->logSuccessfulRegistration($user);

        // Generate and send OTP for email verification
        $otpSent = $this->otpService->generateAndSendOtp($user, 'register');

        return [
            'user' => new UserResource($user),
            'message' => 'Registration successful. Please check your email for OTP verification.',
            'requires_otp' => true,
            'otp_sent' => $otpSent,
        ];
    }

    public function verifyRegistrationOtp(string $email, string $otp): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['User not found.'],
            ]);
        }

        $isValid = $this->otpService->validateOtp($user, $otp, 'register');

        if (!$isValid) {
            // Log failed OTP verification
            $this->authLoggingService->logOtpVerification($user, false, 'Invalid registration OTP');

            throw ValidationException::withMessages([
                'otp' => ['Invalid or expired OTP code.'],
            ]);
        }

        // Log successful OTP verification
        $this->authLoggingService->logOtpVerification($user, true);

        // Mark email as verified
        $this->userRepository->update($user->id, ['email_verified_at' => now()]);
        $user->refresh();

        // Auto-login after successful verification
        Auth::login($user);

        return [
            'user' => new UserResource($user),
            'message' => 'Email verified successfully. You are now logged in.',
            'email_verified' => true,
        ];
    }

    public function logout(): array
    {
        $user = Auth::user();

        if ($user) {
            // Log logout
            $this->authLoggingService->logLogout($user);
        }

        Auth::logout();

        return [
            'message' => 'Logout successful',
        ];
    }

    public function getCurrentUser(): ?UserResource
    {
        $user = Auth::user();
        return $user ? new UserResource($user) : null;
    }

    public function getUserByEmail(string $email): ?UserResource
    {
        $user = $this->userRepository->findByEmail($email);
        return $user ? new UserResource($user) : null;
    }

    public function resendOtp(string $email, string $type = 'login'): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['User not found.'],
            ]);
        }

        $otpSent = $this->otpService->generateAndSendOtp($user, $type);

        return [
            'message' => $otpSent ? 'OTP sent successfully.' : 'Failed to send OTP.',
            'otp_sent' => $otpSent,
        ];
    }

    public function getAuthStats(int $userId, int $days = 30): array
    {
        $user = $this->userRepository->findById($userId);

        if (!$user) {
            return [];
        }

        return $this->authLoggingService->getUserAuthStats($user, $days);
    }
}

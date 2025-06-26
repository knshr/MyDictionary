<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\OtpService;

/**
 * @group Authentication
 *
 * APIs for managing authentication
 */
class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    /**
     * User Login
     *
     * Authenticate a user with email and password. An OTP will be sent to the user's email for verification.
     *
     * @bodyParam email string required The user's email address. Example: user@example.com
     * @bodyParam password string required The user's password. Example: password123
     * @bodyParam remember boolean Remember the user's login. Example: false
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Login successful. Please check your email for OTP verification.",
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "John Doe",
     *       "email": "user@example.com",
     *       "email_verified_at": null,
     *       "created_at": "2024-01-01T00:00:00.000000Z",
     *       "updated_at": "2024-01-01T00:00:00.000000Z"
     *     },
     *     "requires_otp": true,
     *     "otp_sent": true
     *   }
     * }
     *
     * @response 422 {
     *   "success": false,
     *   "message": "The provided credentials are incorrect.",
     *   "errors": {
     *     "email": ["The provided credentials are incorrect."]
     *   }
     * }
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $credentials = $request->only(['email', 'password']);
            $remember = $request->boolean('remember');

            $result = $this->authService->login($credentials, $remember);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'user' => $result['user'],
                    'requires_otp' => $result['requires_otp'],
                    'otp_sent' => $result['otp_sent'],
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e instanceof \Illuminate\Validation\ValidationException ? $e->errors() : null,
            ], 422);
        }
    }

    /**
     * Verify OTP
     *
     * Verify the OTP code sent to the user's email after login.
     *
     * @bodyParam email string required The user's email address. Example: user@example.com
     * @bodyParam otp string required The 6-digit OTP code. Example: 123456
     *
     * @response 200 {
     *   "success": true,
     *   "message": "OTP verified successfully.",
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "John Doe",
     *       "email": "user@example.com",
     *       "email_verified_at": "2024-01-01T00:00:00.000000Z",
     *       "created_at": "2024-01-01T00:00:00.000000Z",
     *       "updated_at": "2024-01-01T00:00:00.000000Z"
     *     },
     *     "email_verified": true
     *   }
     * }
     *
     * @response 422 {
     *   "success": false,
     *   "message": "Invalid or expired OTP code.",
     *   "errors": {
     *     "otp": ["Invalid or expired OTP code."]
     *   }
     * }
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        try {
            $result = $this->authService->verifyOtp($request->email, $request->otp);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'user' => $result['user'],
                    'email_verified' => $result['email_verified'],
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e instanceof \Illuminate\Validation\ValidationException ? $e->errors() : null,
            ], 422);
        }
    }

    /**
     * User Registration
     *
     * Register a new user. An OTP will be sent to the user's email for verification.
     *
     * @bodyParam name string required The user's full name. Example: John Doe
     * @bodyParam email string required The user's email address. Example: user@example.com
     * @bodyParam password string required The user's password (min 8 characters). Example: password123
     * @bodyParam password_confirmation string required Password confirmation. Example: password123
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Registration successful. Please check your email for OTP verification.",
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "John Doe",
     *       "email": "user@example.com",
     *       "email_verified_at": null,
     *       "created_at": "2024-01-01T00:00:00.000000Z",
     *       "updated_at": "2024-01-01T00:00:00.000000Z"
     *     },
     *     "requires_otp": true,
     *     "otp_sent": true
     *   }
     * }
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $result = $this->authService->register($data);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'user' => $result['user'],
                    'requires_otp' => $result['requires_otp'],
                    'otp_sent' => $result['otp_sent'],
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e instanceof \Illuminate\Validation\ValidationException ? $e->errors() : null,
            ], 422);
        }
    }

    /**
     * Verify Registration OTP
     *
     * Verify the OTP code sent to the user's email after registration.
     *
     * @bodyParam email string required The user's email address. Example: user@example.com
     * @bodyParam otp string required The 6-digit OTP code. Example: 123456
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Email verified successfully. You are now logged in.",
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "John Doe",
     *       "email": "user@example.com",
     *       "email_verified_at": "2024-01-01T00:00:00.000000Z",
     *       "created_at": "2024-01-01T00:00:00.000000Z",
     *       "updated_at": "2024-01-01T00:00:00.000000Z"
     *     },
     *     "email_verified": true
     *   }
     * }
     */
    public function verifyRegistrationOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        try {
            $result = $this->authService->verifyRegistrationOtp($request->email, $request->otp);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'user' => $result['user'],
                    'email_verified' => $result['email_verified'],
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e instanceof \Illuminate\Validation\ValidationException ? $e->errors() : null,
            ], 422);
        }
    }

    /**
     * Resend OTP
     *
     * Resend OTP code to the user's email.
     *
     * @bodyParam email string required The user's email address. Example: user@example.com
     * @bodyParam type string The type of OTP (login, register). Example: login
     *
     * @response 200 {
     *   "success": true,
     *   "message": "OTP sent successfully.",
     *   "data": {
     *     "otp_sent": true
     *   }
     * }
     */
    public function resendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'type' => 'string|in:login,register',
        ]);

        try {
            $result = $this->authService->resendOtp($request->email, $request->type ?? 'login');

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'otp_sent' => $result['otp_sent'],
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e instanceof \Illuminate\Validation\ValidationException ? $e->errors() : null,
            ], 422);
        }
    }

    /**
     * User Logout
     *
     * Logout the authenticated user.
     *
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Logout successful"
     * }
     */
    public function logout(): JsonResponse
    {
        $result = $this->authService->logout();

        return response()->json([
            'success' => true,
            'message' => $result['message'],
        ], 200);
    }

    /**
     * Get Current User
     *
     * Get the currently authenticated user's information.
     *
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "John Doe",
     *       "email": "user@example.com",
     *       "email_verified_at": "2024-01-01T00:00:00.000000Z",
     *       "created_at": "2024-01-01T00:00:00.000000Z",
     *       "updated_at": "2024-01-01T00:00:00.000000Z"
     *     }
     *   }
     * }
     */
    public function me(): JsonResponse
    {
        $user = $this->authService->getCurrentUser();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
            ]
        ], 200);
    }

    /**
     * Get OTP Status
     *
     * Get OTP status information including remaining requests and cooldown time.
     *
     * @bodyParam email string required The user's email address. Example: user@example.com
     * @bodyParam type string The type of OTP (login, register). Example: login
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "remaining_requests": 4,
     *     "max_requests": 5,
     *     "request_window_minutes": 30,
     *     "next_request_available_in_minutes": 0,
     *     "resend_cooldown_remaining_seconds": 0,
     *     "has_valid_otp": true,
     *     "otp_remaining_time_seconds": 540
     *   }
     * }
     */
    public function getOtpStatus(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'type' => 'string|in:login,register',
        ]);

        try {
            $user = $this->authService->getUserByEmail($request->email);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found.',
                ], 404);
            }

            $type = $request->type ?? 'login';
            $otpService = app(OtpService::class);

            $remainingRequests = $otpService->getRemainingRequests($user, $type);
            $cooldownRemaining = $otpService->getResendCooldownRemaining($user, $type);
            $hasValidOtp = $otpService->hasValidOtp($user, $type);
            $otpRemainingTime = $otpService->getOtpRemainingTime($user, $type);

            return response()->json([
                'success' => true,
                'data' => array_merge($remainingRequests, [
                    'resend_cooldown_remaining_seconds' => $cooldownRemaining,
                    'has_valid_otp' => $hasValidOtp,
                    'otp_remaining_time_seconds' => $otpRemainingTime,
                ])
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}

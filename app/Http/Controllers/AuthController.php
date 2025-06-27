<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function showLogin()
    {
        return Inertia::render('Auth/Login');
    }

    public function showRegister()
    {
        return Inertia::render('Auth/Register');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only(['email', 'password']);
        $remember = $request->boolean('remember');

        $result = $this->authService->login($credentials, $remember);

        if ($result['requires_otp']) {
            // Store OTP email in session for verification
            $request->session()->put('otp_email', $credentials['email']);
            $request->session()->put('otp_type', 'login');
            
            // For Inertia requests, stay on the same page and show OTP form
            if ($request->header('X-Inertia')) {
                return back()->with('message', $result['message']);
            }
            
            // If it's an AJAX request, return JSON response
            if ($request->expectsJson()) {
                return response()->json([
                    'requires_otp' => true,
                    'message' => $result['message']
                ]);
            }
            
            return redirect('/login')->with('message', $result['message']);
        }

        $request->session()->regenerate();

        // If it's an AJAX request, return JSON response
        if ($request->expectsJson()) {
            return response()->json([
                'requires_otp' => false,
                'message' => $result['message']
            ]);
        }

        return redirect()->intended('/dashboard')->with('message', $result['message']);
    }

    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $result = $this->authService->register($data);

        if ($result['requires_otp']) {
            // Store OTP email in session for verification
            $request->session()->put('otp_email', $data['email']);
            $request->session()->put('otp_type', 'register');
            
            // If it's an AJAX request, return JSON response
            if ($request->expectsJson()) {
                return response()->json([
                    'requires_otp' => true,
                    'message' => $result['message']
                ]);
            }
            
            return redirect('/register')->with('message', $result['message']);
        }

        // If it's an AJAX request, return JSON response
        if ($request->expectsJson()) {
            return response()->json([
                'requires_otp' => false,
                'message' => $result['message']
            ]);
        }

        return redirect('/dashboard')->with('message', $result['message']);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $email = $request->session()->get('otp_email');
        $type = $request->session()->get('otp_type', 'login');

        if (!$email) {
            return redirect('/login')->withErrors(['otp' => 'No pending OTP verification found.']);
        }

        try {
            if ($type === 'register') {
                $result = $this->authService->verifyRegistrationOtp($email, $request->otp);
            } else {
                $result = $this->authService->verifyOtp($email, $request->otp);
            }

            // Clear OTP session data
            $request->session()->forget(['otp_email', 'otp_type']);

            $request->session()->regenerate();

            // Redirect to dashboard with success message
            return redirect('/dashboard')->with('message', $result['message']);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['otp' => $e->getMessage()]);
        }
    }

    public function verifyRegistrationOtp(Request $request)
    {
        return $this->verifyOtp($request);
    }

    public function resendOtp(Request $request)
    {
        $email = $request->session()->get('otp_email');
        $type = $request->session()->get('otp_type', 'login');

        if (!$email) {
            // If it's an AJAX request, return JSON response
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No pending OTP verification found.'
                ], 422);
            }
            
            return redirect('/login')->withErrors(['email' => 'No pending OTP verification found.']);
        }

        try {
            $result = $this->authService->resendOtp($email, $type);
            
            // If it's an AJAX request, return JSON response
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message']
                ]);
            }
            
            return redirect()->back()->with('message', $result['message']);
        } catch (\Exception $e) {
            // If it's an AJAX request, return JSON response
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()->withErrors(['email' => $e->getMessage()]);
        }
    }

    public function logout(Request $request)
    {
        $result = $this->authService->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('message', $result['message']);
    }
}

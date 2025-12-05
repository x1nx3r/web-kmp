<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Verify Cloudflare Turnstile token
     */
    private function verifyTurnstile($token)
    {
        $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => config('services.turnstile.secret_key'),
            'response' => $token,
        ]);

        $result = $response->json();
        
        return $result['success'] ?? false;
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
            'cf-turnstile-response' => 'required',
        ], [
            'cf-turnstile-response.required' => 'Mohon verifikasi bahwa Anda bukan robot.',
        ]);

        // Verify Turnstile CAPTCHA
        $turnstileToken = $request->input('cf-turnstile-response');
        if (!$this->verifyTurnstile($turnstileToken)) {
            return response()->json([
                'success' => false,
                'message' => 'Verifikasi CAPTCHA gagal. Silakan coba lagi.'
            ], 422);
        }

        $credentials = $request->only('email', 'password');
        
        // First, check if user exists (regardless of status)
        $userExists = User::where('email', $credentials['email'])
                         ->orWhere('username', $credentials['email'])
                         ->first();

        // If user doesn't exist
        if (!$userExists) {
            return response()->json([
                'success' => false,
                'message' => 'Email/Username tidak ditemukan.'
            ], 401);
        }

        // Check if password is correct
        if (!Hash::check($credentials['password'], $userExists->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password yang Anda masukkan salah.'
            ], 401);
        }

        // Check if user is active
        if ($userExists->status !== 'aktif') {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda tidak aktif. Silakan hubungi administrator.'
            ], 403);
        }

        // All checks passed, login the user
        Auth::login($userExists, $request->filled('remember'));
        
        $request->session()->regenerate();
        
        return response()->json([
            'success' => true,
            'message' => 'Login berhasil!',
            'redirect' => route('dashboard')
        ]);
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }
}

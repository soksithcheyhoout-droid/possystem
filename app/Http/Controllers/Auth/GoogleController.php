<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GoogleController extends Controller
{
    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle()
    {
        // Check if Google OAuth is configured
        if (!config('services.google.client_id') || !config('services.google.client_secret')) {
            return redirect()->route('login')
                ->with('error', 'Google login is not configured. Please use email/password login.');
        }

        // Check if client secret is still placeholder
        if (config('services.google.client_secret') === 'GOCSPX-PLACEHOLDER_ADD_YOUR_SECRET_HERE') {
            return redirect()->route('login')
                ->with('error', 'Google login setup incomplete. Please add your Client Secret from Google Cloud Console.');
        }

        try {
            // Fix SSL certificate issue for Windows
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // Configure Socialite with SSL fix
                $driver = Socialite::driver('google');
                
                // Use reflection to access the http client and disable SSL verification for local development
                if (app()->environment('local')) {
                    $reflection = new \ReflectionClass($driver);
                    if ($reflection->hasProperty('httpClient')) {
                        $httpClientProperty = $reflection->getProperty('httpClient');
                        $httpClientProperty->setAccessible(true);
                        
                        $httpClient = new \GuzzleHttp\Client([
                            'verify' => false, // Disable SSL verification for local development
                            'timeout' => 30,
                        ]);
                        
                        $httpClientProperty->setValue($driver, $httpClient);
                    }
                }
                
                return $driver->redirect();
            }
            
            return Socialite::driver('google')->redirect();
        } catch (\Exception $e) {
            \Log::error('Google OAuth redirect error: ' . $e->getMessage());
            return redirect()->route('login')
                ->with('error', 'Google login is temporarily unavailable. Error: ' . $e->getMessage());
        }
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback()
    {
        // Check if Google OAuth is configured
        if (!config('services.google.client_id') || !config('services.google.client_secret')) {
            return redirect()->route('login')
                ->with('error', 'Google login is not configured. Please use email/password login.');
        }

        // Check if client secret is still placeholder
        if (config('services.google.client_secret') === 'GOCSPX-PLACEHOLDER_ADD_YOUR_SECRET_HERE') {
            return redirect()->route('login')
                ->with('error', 'Google login setup incomplete. Please add your Client Secret from Google Cloud Console.');
        }

        try {
            // Fix SSL certificate issue for Windows
            $driver = Socialite::driver('google');
            
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && app()->environment('local')) {
                // Use reflection to access the http client and disable SSL verification for local development
                $reflection = new \ReflectionClass($driver);
                if ($reflection->hasProperty('httpClient')) {
                    $httpClientProperty = $reflection->getProperty('httpClient');
                    $httpClientProperty->setAccessible(true);
                    
                    $httpClient = new \GuzzleHttp\Client([
                        'verify' => false, // Disable SSL verification for local development
                        'timeout' => 30,
                    ]);
                    
                    $httpClientProperty->setValue($driver, $httpClient);
                }
            }
            
            $googleUser = $driver->user();
            
            // Check if user already exists with this Google ID
            $user = User::where('google_id', $googleUser->id)->first();
            
            if ($user) {
                // User exists, log them in
                Auth::login($user, true);
                return redirect()->intended(route('admin.dashboard'))
                    ->with('success', 'Welcome back, ' . $user->name . '!');
            }
            
            // Check if user exists with this email
            $existingUser = User::where('email', $googleUser->email)->first();
            
            if ($existingUser) {
                // Link Google account to existing user
                $existingUser->update([
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                ]);
                
                Auth::login($existingUser, true);
                return redirect()->intended(route('admin.dashboard'))
                    ->with('success', 'Google account linked successfully! Welcome back, ' . $existingUser->name . '!');
            }
            
            // Create new user
            $newUser = User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
                'avatar' => $googleUser->avatar,
                'email_verified_at' => now(),
                'password' => Hash::make(Str::random(24)), // Random password for security
            ]);
            
            Auth::login($newUser, true);
            
            return redirect()->intended(route('admin.dashboard'))
                ->with('success', 'Account created successfully! Welcome to the POS System, ' . $newUser->name . '!');
                
        } catch (\Exception $e) {
            \Log::error('Google OAuth callback error: ' . $e->getMessage());
            return redirect()->route('login')
                ->with('error', 'Google authentication failed: ' . $e->getMessage() . '. Please try again or use email/password login.');
        }
    }
}
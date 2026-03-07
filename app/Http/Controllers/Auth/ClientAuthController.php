<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class ClientAuthController extends Controller
{
    public function showRegister()
    {
        return view('client.auth.register');
    }

    public function register(Request $request)
    {
        // Verify Turnstile token
        $turnstileResponse = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret'   => config('services.turnstile.secret'),
            'response' => $request->input('cf-turnstile-response'),
            'remoteip' => $request->ip(),
        ]);

        if (!$turnstileResponse->json('success')) {
            return back()->withErrors(['captcha' => 'Security check failed. Please try again.'])->withInput();
        }

        $validated = $request->validate([
            'first_name'   => 'required|string|max:100',
            'last_name'    => 'nullable|string|max:100',
            'email'        => 'required|email|unique:clients,email',
            'phone'        => 'required|string',
            'password'     => 'required|string|min:8|confirmed',
            'email_consent'=> 'required|accepted',
        ]);

        $client = Client::create([
            'first_name'       => $validated['first_name'],
            'last_name'        => $validated['last_name'] ?? null,
            'name'             => trim($validated['first_name'] . ' ' . ($validated['last_name'] ?? '')),
            'email'            => $validated['email'],
            'phone'            => $validated['phone'],
            'password'         => Hash::make($validated['password']),
            'email_consent_at' => now(),
        ]);

        Auth::guard('client')->login($client);

        return redirect()->route('client.dashboard')->with('success', 'Account created successfully!');
    }

    public function showLogin()
    {
        return view('client.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('client')->attempt($credentials, $request->remember)) {
            $request->session()->regenerate();
            return redirect()->intended(route('client.dashboard'));
        }

        return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('client')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}

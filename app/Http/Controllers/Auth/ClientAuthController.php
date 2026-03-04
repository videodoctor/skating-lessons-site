<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ClientAuthController extends Controller
{
    // Show registration form
    public function showRegister()
    {
        return view('client.auth.register');
    }
    
    // Handle registration
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email',
            'phone' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
            'email_consent' => 'required|accepted',
        ]);
        
        $client = Client::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'email_consent_at' => now(),
        ]);
        
        Auth::guard('client')->login($client);
        
        return redirect()->route('client.dashboard')->with('success', 'Account created successfully!');
    }
    
    // Show login form
    public function showLogin()
    {
        return view('client.auth.login');
    }
    
    // Handle login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        if (Auth::guard('client')->attempt($credentials, $request->remember)) {
            $request->session()->regenerate();
            return redirect()->intended(route('client.dashboard'));
        }
        
        return back()->withErrors([
            'email' => 'Invalid credentials.',
        ])->onlyInput('email');
    }
    
    // Handle logout
    public function logout(Request $request)
    {
        Auth::guard('client')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }
}

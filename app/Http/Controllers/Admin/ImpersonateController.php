<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Auth;

class ImpersonateController extends Controller
{
    public function start(Client $client)
    {
        $admin = Auth::user();

        // Store admin ID in session so we can return
        session(['impersonating_admin_id' => $admin->id]);
        session(['impersonating_client_name' => $client->full_name]);

        ActivityLogger::log($client->id, 'impersonated', "Admin {$admin->name} started impersonating", [
            'admin_id' => $admin->id,
        ]);

        // Log in as the client
        Auth::guard('client')->login($client);

        return redirect()->route('client.dashboard')
            ->with('success', "Now viewing as {$client->full_name}.");
    }

    public function stop()
    {
        $adminId = session('impersonating_admin_id');

        // Clear impersonation session
        session()->forget(['impersonating_admin_id', 'impersonating_client_name']);

        // Log out the client
        Auth::guard('client')->logout();

        return redirect()->route('admin.clients.index')
            ->with('success', 'Impersonation ended. Welcome back!');
    }
}

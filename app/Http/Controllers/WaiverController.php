<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\LiabilityWaiver;
use Illuminate\Http\Request;

class WaiverController extends Controller
{
    public function show(Request $request)
    {
        $client         = auth('client')->user();
        $existingWaiver = null;
        $alreadySigned  = false;

        if ($client) {
            $existingWaiver = LiabilityWaiver::where('client_id', $client->id)
                ->where('version', LiabilityWaiver::CURRENT_VERSION)
                ->latest()
                ->first();
            $alreadySigned = $existingWaiver !== null;
        }

        return view('waiver', compact('client', 'existingWaiver', 'alreadySigned'));
    }

    public function sign(Request $request)
    {
        $validated = $request->validate([
            'signed_name' => 'required|string|min:3|max:200',
            'email'       => 'nullable|email',
        ]);

        // Resolve client
        $client = auth('client')->user();

        if (!$client && !empty($validated['email'])) {
            $client = Client::where('email', $validated['email'])->first();
            if (!$client) {
                return back()->withErrors(['email' => 'No account found with that email. Please register first.'])->withInput();
            }
        }

        if (!$client) {
            return back()->withErrors(['signed_name' => 'You must be logged in or provide your email to sign the waiver.'])->withInput();
        }

        // Check already signed this version
        $existing = LiabilityWaiver::where('client_id', $client->id)
            ->where('version', LiabilityWaiver::CURRENT_VERSION)
            ->first();

        if ($existing) {
            return redirect()->route('waiver.show')->with('success', 'You have already signed this waiver.');
        }

        // Save waiver
        LiabilityWaiver::create([
            'client_id'           => $client->id,
            'version'             => LiabilityWaiver::CURRENT_VERSION,
            'signed_name'         => $validated['signed_name'],
            'signed_ip'           => $request->ip(),
            'signed_at'           => now(),
            'waiver_text_snapshot'=> 'v' . LiabilityWaiver::CURRENT_VERSION,
        ]);

        // Update client waiver fields
        $client->update([
            'waiver_signed_at' => now(),
            'waiver_version'   => LiabilityWaiver::CURRENT_VERSION,
            'waiver_ip'        => $request->ip(),
        ]);

        return redirect()->route('waiver.show')
            ->with('success', "Waiver signed successfully by {$validated['signed_name']}. Thank you!");
    }
}

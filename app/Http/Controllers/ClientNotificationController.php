<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientNotificationController extends Controller
{
    public function update(Request $request)
    {
        $client = auth('client')->user();

        $prefs = $client->notification_prefs ?? [];

        foreach (Client::NOTIFICATION_CATEGORIES as $key => $label) {
            if ($key === 'account_security') continue; // always on

            $prefs[$key] = [
                'email' => $request->boolean("prefs.{$key}.email"),
                'sms'   => $request->boolean("prefs.{$key}.sms"),
            ];
        }

        $client->update(['notification_prefs' => $prefs]);

        return back()->with('success', 'Notification preferences saved.');
    }
}

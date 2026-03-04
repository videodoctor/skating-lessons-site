<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function portal($token)
    {
        $client = Client::where('access_token', $token)->firstOrFail();
        
        // TODO: Load client's bookings and assessments
        
        return view('client.portal', compact('client'));
    }
}

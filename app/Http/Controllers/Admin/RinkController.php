<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rink;
use Illuminate\Http\Request;

class RinkController extends Controller
{
    public function index()
    {
        $rinks = Rink::orderBy('name')->get();
        return view('admin.rinks', compact('rinks'));
    }

    public function update(Request $request, Rink $rink)
    {
        $validated = $request->validate([
            'name'             => 'sometimes|string|max:255',
            'address'          => 'nullable|string|max:500',
            'website_url'      => 'nullable|url|max:500',
            'is_active'        => 'sometimes|boolean',
            'is_bookable'      => 'sometimes|boolean',
            'is_displayed'     => 'sometimes|boolean',
            'inactive_message' => 'nullable|string|max:500',
        ]);

        $rink->update($validated);

        return back()->with('success', "{$rink->name} updated.");
    }
}

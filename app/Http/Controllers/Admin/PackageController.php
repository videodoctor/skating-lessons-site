<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PackageController extends Controller
{
    public function index()
    {
        $services = Service::orderBy('price')->get();
        return view('admin.packages', compact('services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:100',
            'slug'               => 'required|string|max:100|unique:services,slug',
            'description'        => 'required|string',
            'price'              => 'required|numeric|min:0',
            'duration_minutes'   => 'required|integer|min:1',
            'discount_amount'    => 'nullable|numeric|min:0',
            'discount_type'      => 'nullable|in:percent,dollar',
            'discount_starts_at' => 'nullable|date',
            'discount_ends_at'   => 'nullable|date|after_or_equal:discount_starts_at',
        ]);

        Service::create([
            'name'               => $validated['name'],
            'slug'               => Str::slug($validated['slug']),
            'description'        => $validated['description'],
            'price'              => $validated['price'],
            'duration_minutes'   => $validated['duration_minutes'],
            'features'           => array_values(array_filter($request->input('features', []))),
            'is_active'          => $request->boolean('is_active'),
            'discount_amount'    => $validated['discount_amount'] ?: null,
            'discount_type'      => $validated['discount_amount'] ? $validated['discount_type'] : null,
            'discount_starts_at' => $validated['discount_starts_at'] ?: null,
            'discount_ends_at'   => $validated['discount_ends_at'] ?: null,
        ]);

        return redirect()->route('admin.packages.index')->with('success', 'Package created!');
    }

    public function update(Request $request, Service $package)
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:100',
            'slug'               => 'required|string|max:100|unique:services,slug,' . $package->id,
            'description'        => 'required|string',
            'price'              => 'required|numeric|min:0',
            'duration_minutes'   => 'required|integer|min:1',
            'discount_amount'    => 'nullable|numeric|min:0',
            'discount_type'      => 'nullable|in:percent,dollar',
            'discount_starts_at' => 'nullable|date',
            'discount_ends_at'   => 'nullable|date|after_or_equal:discount_starts_at',
        ]);

        $package->update([
            'name'               => $validated['name'],
            'slug'               => Str::slug($validated['slug']),
            'description'        => $validated['description'],
            'price'              => $validated['price'],
            'duration_minutes'   => $validated['duration_minutes'],
            'features'           => array_values(array_filter($request->input('features', []))),
            'is_active'          => $request->boolean('is_active'),
            'discount_amount'    => $validated['discount_amount'] ?: null,
            'discount_type'      => $validated['discount_amount'] ? $validated['discount_type'] : null,
            'discount_starts_at' => $validated['discount_starts_at'] ?: null,
            'discount_ends_at'   => $validated['discount_ends_at'] ?: null,
        ]);

        return redirect()->route('admin.packages.index')->with('success', "Package \"{$package->name}\" updated!");
    }

    public function toggle(Service $package)
    {
        $package->update(['is_active' => !$package->is_active]);
        return back()->with('success', $package->is_active
            ? "\"{$package->name}\" is now visible on the booking page."
            : "\"{$package->name}\" is now hidden.");
    }
}

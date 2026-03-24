<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceWaitlist;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PackageController extends Controller
{
    public function index()
    {
        $services = Service::orderBy('price')->get();
        $waitlistCounts = ServiceWaitlist::selectRaw('service_id, count(*) as total')
            ->groupBy('service_id')
            ->pluck('total', 'service_id');
        return view('admin.packages', compact('services', 'waitlistCounts'));
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
            'coming_soon_teaser' => 'nullable|string|max:200',
        ]);

        Service::create([
            'name'               => $validated['name'],
            'slug'               => Str::slug($validated['slug']),
            'description'        => $validated['description'],
            'price'              => $validated['price'],
            'duration_minutes'   => $validated['duration_minutes'],
            'features'           => array_values(array_filter($request->input('features', []))),
            'is_active'          => $request->input('status') === 'active',
            'coming_soon'        => $request->input('status') === 'coming_soon',
            'coming_soon_teaser' => $validated['coming_soon_teaser'] ?: null,
            'show_price'         => $request->boolean('show_price', true),
            'show_duration'      => $request->boolean('show_duration', true),
            'show_features'      => $request->boolean('show_features', true),
            'show_description'   => $request->boolean('show_description', true),
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
            'coming_soon_teaser' => 'nullable|string|max:200',
        ]);

        $package->update([
            'name'               => $validated['name'],
            'slug'               => Str::slug($validated['slug']),
            'description'        => $validated['description'],
            'price'              => $validated['price'],
            'duration_minutes'   => $validated['duration_minutes'],
            'features'           => array_values(array_filter($request->input('features', []))),
            'is_active'          => $request->input('status') === 'active',
            'coming_soon'        => $request->input('status') === 'coming_soon',
            'coming_soon_teaser' => $validated['coming_soon_teaser'] ?: null,
            'show_price'         => $request->boolean('show_price'),
            'show_duration'      => $request->boolean('show_duration'),
            'show_features'      => $request->boolean('show_features'),
            'show_description'   => $request->boolean('show_description'),
            'discount_amount'    => $validated['discount_amount'] ?: null,
            'discount_type'      => $validated['discount_amount'] ? $validated['discount_type'] : null,
            'discount_starts_at' => $validated['discount_starts_at'] ?: null,
            'discount_ends_at'   => $validated['discount_ends_at'] ?: null,
        ]);

        return redirect()->route('admin.packages.index')->with('success', "Package \"{$package->name}\" updated!");
    }

    public function toggle(Service $package)
    {
        $package->update(['is_active' => !$package->is_active, 'coming_soon' => false]);
        return back()->with('success', $package->is_active
            ? "\"{$package->name}\" is now visible."
            : "\"{$package->name}\" is now hidden.");
    }

    public function waitlist(Service $package)
    {
        $entries = ServiceWaitlist::where('service_id', $package->id)
            ->orderByDesc('created_at')->get();
        return view('admin.waitlist', compact('package', 'entries'));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function index()
    {
        $testimonials = Testimonial::orderBy('sort_order')->orderBy('id')->get();
        return view('admin.testimonials', compact('testimonials'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'quote'         => 'required|string',
            'author'        => 'required|string|max:100',
            'author_detail' => 'nullable|string|max:150',
            'sort_order'    => 'nullable|integer',
        ]);

        Testimonial::create([
            'quote'         => $validated['quote'],
            'author'        => $validated['author'],
            'author_detail' => $validated['author_detail'] ?? null,
            'source_type'   => $request->input('source_type') ?: null,
            'is_active'     => $request->boolean('is_active', true),
            'sort_order'    => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.testimonials.index')
            ->with('success', 'Testimonial added!');
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $validated = $request->validate([
            'quote'         => 'required|string',
            'author'        => 'required|string|max:100',
            'author_detail' => 'nullable|string|max:150',
            'sort_order'    => 'nullable|integer',
        ]);

        $testimonial->update([
            'quote'         => $validated['quote'],
            'author'        => $validated['author'],
            'author_detail' => $validated['author_detail'] ?? null,
            'source_type'   => $request->input('source_type') ?: null,
            'is_active'     => $request->boolean('is_active'),
            'sort_order'    => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.testimonials.index')
            ->with('success', 'Testimonial updated!');
    }

    public function toggle(Testimonial $testimonial)
    {
        $testimonial->update(['is_active' => !$testimonial->is_active]);
        return back()->with('success', $testimonial->is_active ? 'Testimonial enabled.' : 'Testimonial disabled.');
    }

    public function destroy(Testimonial $testimonial)
    {
        $testimonial->delete();
        return back()->with('success', 'Testimonial deleted.');
    }
}

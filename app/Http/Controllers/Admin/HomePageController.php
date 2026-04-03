<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Models\StudentMedia;
use Illuminate\Http\Request;

class HomePageController extends Controller
{
    public function index()
    {
        $heroMediaIds = json_decode(SiteSetting::get('homepage_hero_media', '[]'), true) ?: [];
        $heroMedia = $heroMediaIds ? StudentMedia::whereIn('id', $heroMediaIds)->with('student')->get()
            ->sortBy(fn($m) => array_search($m->id, $heroMediaIds))->values() : collect();

        $bioMediaIds = json_decode(SiteSetting::get('homepage_bio_media', '[]'), true) ?: [];
        $bioMedia = $bioMediaIds ? StudentMedia::whereIn('id', $bioMediaIds)->with('student')->get()
            ->sortBy(fn($m) => array_search($m->id, $bioMediaIds))->values() : collect();

        $availableVideos = StudentMedia::where('type', 'video')->with('student')->latest()->get();
        $availablePhotos = StudentMedia::where('type', 'photo')->with('student')->latest()->get();

        return view('admin.home-page', compact('heroMedia', 'heroMediaIds', 'bioMedia', 'bioMediaIds', 'availableVideos', 'availablePhotos'));
    }

    public function updateHeroMedia(Request $request)
    {
        $request->validate([
            'media_ids'   => 'nullable|array',
            'media_ids.*' => 'integer|exists:student_media,id',
        ]);

        SiteSetting::set('homepage_hero_media', json_encode(array_map('intval', $request->input('media_ids', []))));

        return back()->with('success', 'Hero media updated.');
    }

    public function updateBioMedia(Request $request)
    {
        $request->validate([
            'media_ids'   => 'nullable|array|max:2',
            'media_ids.*' => 'integer|exists:student_media,id',
        ]);

        SiteSetting::set('homepage_bio_media', json_encode(array_map('intval', $request->input('media_ids', []))));

        return back()->with('success', 'Bio photos updated.');
    }
}

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
            'media_ids'   => 'nullable|array',
            'media_ids.*' => 'integer|exists:student_media,id',
        ]);

        SiteSetting::set('homepage_bio_media', json_encode(array_map('intval', $request->input('media_ids', []))));

        return back()->with('success', 'Bio photos updated.');
    }

    /**
     * Register a cropped bio photo (uploaded to S3 via presigned URL from the browser).
     */
    public function registerBioCrop(Request $request)
    {
        $request->validate([
            's3_path'          => 'required|string',
            'source_media_id'  => 'required|exists:student_media,id',
            'width'            => 'nullable|integer',
            'height'           => 'nullable|integer',
            'file_size'        => 'required|integer',
        ]);

        $source = StudentMedia::findOrFail($request->source_media_id);

        // Create a new media record for the bio crop
        $crop = StudentMedia::create([
            'student_id'        => $source->student_id,
            'type'              => 'photo',
            'path'              => $request->s3_path,
            'original_path'     => $source->path,
            'original_filename' => 'bio_crop_' . $source->original_filename,
            'mime_type'         => 'image/jpeg',
            'file_size'         => $request->file_size,
            'width'             => $request->width,
            'height'            => $request->height,
            'caption'           => 'Bio crop of ' . ($source->caption ?? $source->original_filename),
            'uploaded_by_type'  => 'admin',
            'uploaded_by_id'    => auth()->id(),
        ]);

        // Add this crop to the bio media list
        $bioIds = json_decode(SiteSetting::get('homepage_bio_media', '[]'), true) ?: [];
        $bioIds[] = $crop->id;
        SiteSetting::set('homepage_bio_media', json_encode($bioIds));

        return response()->json(['id' => $crop->id, 'url' => $crop->url]);
    }
}

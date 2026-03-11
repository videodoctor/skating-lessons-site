<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\LiabilityWaiver;
use Illuminate\Http\Request;

class WaiverAdminController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('q');

        $waivers = LiabilityWaiver::with('client')
            ->when($search, fn($q) => $q->whereHas('client', fn($cq) =>
                $cq->where('name', 'like', "%{$search}%")
                   ->orWhere('first_name', 'like', "%{$search}%")
                   ->orWhere('last_name', 'like', "%{$search}%")
                   ->orWhere('email', 'like', "%{$search}%")
            )->orWhere('signed_name', 'like', "%{$search}%"))
            ->orderByDesc('signed_at')
            ->paginate(30);

        $unsignedCount = Client::whereNull('waiver_signed_at')
            ->orWhereNull('waiver_version')
            ->orWhereColumn('waiver_version', '!=', \DB::raw("'" . LiabilityWaiver::CURRENT_VERSION . "'"))
            ->count();

        return view('admin.waivers', compact('waivers', 'search', 'unsignedCount'));
    }
}

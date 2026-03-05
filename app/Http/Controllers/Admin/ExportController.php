<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ExportController extends Controller
{
    public function index()
    {
        $stats = [
            'total_revenue'    => Booking::where('status', 'confirmed')->sum('price_paid'),
            'total_bookings'   => Booking::count(),
            'confirmed'        => Booking::where('status', 'confirmed')->count(),
            'pending'          => Booking::where('status', 'pending')->count(),
            'total_clients'    => Client::count(),
            'this_month_rev'   => Booking::where('status', 'confirmed')
                ->whereMonth('date', now()->month)->sum('price_paid'),
            'this_month_count' => Booking::where('status', 'confirmed')
                ->whereMonth('date', now()->month)->count(),
        ];

        return view('admin.export', compact('stats'));
    }

    public function bookingsCsv(Request $request)
    {
        $from = $request->get('from', now()->subDays(90)->format('Y-m-d'));
        $to   = $request->get('to', now()->format('Y-m-d'));

        $bookings = Booking::with(['service', 'timeSlot.rink'])
            ->whereBetween('date', [$from, $to])
            ->orderBy('date')
            ->get();

        $rows = [['ID','Date','Time','Client Name','Email','Phone','Service','Rink','Price','Status','Notes','Booked At']];
        foreach ($bookings as $b) {
            $rows[] = [
                $b->id,
                $b->date ?? ($b->timeSlot->date ?? ''),
                $b->start_time ?? ($b->timeSlot->start_time ?? ''),
                $b->client_name,
                $b->client_email,
                $b->client_phone,
                $b->service->name ?? '',
                $b->timeSlot->rink->name ?? '',
                $b->price_paid,
                $b->status,
                $b->notes,
                $b->created_at->format('Y-m-d H:i'),
            ];
        }

        return $this->toCsvResponse($rows, "bookings_{$from}_to_{$to}.csv");
    }

    public function clientsCsv()
    {
        $clients = Client::withCount('bookings')
            ->withSum('bookings', 'price_paid')
            ->get();

        $rows = [['ID','Name','Email','Phone','Total Bookings','Total Paid','Member Since']];
        foreach ($clients as $c) {
            $rows[] = [$c->id,$c->name,$c->email,$c->phone,$c->bookings_count,$c->bookings_sum_price_paid,$c->created_at->format('Y-m-d')];
        }

        return $this->toCsvResponse($rows, 'clients_' . now()->format('Y-m-d') . '.csv');
    }

    private function toCsvResponse(array $rows, string $filename): Response
    {
        $csv = implode("\n", array_map(fn($row) =>
            implode(',', array_map(fn($v) => '"' . str_replace('"', '""', (string)$v) . '"', $row))
        , $rows));

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }
}

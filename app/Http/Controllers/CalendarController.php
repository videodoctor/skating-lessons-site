<?php

namespace App\Http\Controllers;

use App\Models\RinkSession;
use Illuminate\Http\Request;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;

class CalendarController extends Controller
{
    public function publicSessions($rink = null)
    {
        // Get upcoming public sessions (next 90 days)
        $sessions = RinkSession::with('rink')
            ->where('date', '>=', today())
            ->where('date', '<=', today()->addDays(90))
            ->when($rink, function($query) use ($rink) {
                $query->whereHas('rink', function($q) use ($rink) {
                    $q->where('slug', $rink);
                });
            })
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

         \Log::info('Calendar sessions count: ' . $sessions->count());
         \Log::info('Now: ' . now());
         \Log::info('First session date: ' . ($sessions->first() ? $sessions->first()->date : 'none'));

        
        $calendar = Calendar::create('Public Skating - Kristine Skates')
            ->refreshInterval(60); // Refresh every hour
        
        foreach ($sessions as $session) {
            $calendar->event(
                Event::create()
                    ->name('Public Skate - ' . $session->rink->name)
                    ->description('Public skating session. Book a lesson with Coach Kristine at kristineskates.com')
                    ->startsAt(new \DateTime($session->date->format('Y-m-d') . ' ' . $session->start_time))
                    ->endsAt(new \DateTime($session->date->format('Y-m-d') . ' ' . $session->end_time))
                    ->address($session->rink->address ?? '')
                    ->url('https://kristineskates.com/book')
            );
        }
        
        return response($calendar->get())
            ->header('Content-Type', 'text/calendar; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="public-skating.ics"');
    }
}

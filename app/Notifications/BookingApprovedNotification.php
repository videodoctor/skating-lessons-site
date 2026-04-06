<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Models\NotificationTemplate;
use App\Services\TemplateVars;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingApprovedNotification extends Notification
{
    use Queueable;

    public $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $vars = TemplateVars::fromBooking($this->booking);
        $subject = NotificationTemplate::renderSubject('email_booking_approved', $vars)
            ?? 'Lesson Approved! - ' . $this->booking->service->name;

        $icsContent = $this->generateIcsFile();

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.booking-approved', [
                'booking' => $this->booking,
                'templateBody' => NotificationTemplate::render('email_booking_approved', $vars),
            ])
            ->attachData($icsContent, 'skating-lesson.ics', [
                'mime' => 'text/calendar',
            ]);
    }

    private function generateIcsFile()
    {
        $start = \Carbon\Carbon::parse($this->booking->date->format('Y-m-d') . ' ' . $this->booking->start_time);
        $end = \Carbon\Carbon::parse($this->booking->date->format('Y-m-d') . ' ' . $this->booking->end_time);

        $ics = "BEGIN:VCALENDAR\r\n";
        $ics .= "VERSION:2.0\r\n";
        $ics .= "PRODID:-//Kristine Skates//Lesson Booking//EN\r\n";
        $ics .= "BEGIN:VEVENT\r\n";
        $ics .= "UID:" . md5($this->booking->id . time()) . "@kristineskates.com\r\n";
        $ics .= "DTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\n";
        $ics .= "DTSTART:" . $start->format('Ymd\THis') . "\r\n";
        $ics .= "DTEND:" . $end->format('Ymd\THis') . "\r\n";
        $ics .= "SUMMARY:Skating Lesson with Coach Kristine\r\n";
        $ics .= "DESCRIPTION:" . $this->booking->service->name . "\r\n";
        $ics .= "LOCATION:" . $this->booking->timeSlot->rink->name . ", " . ($this->booking->timeSlot->rink->address ?? '') . "\r\n";
        $ics .= "STATUS:CONFIRMED\r\n";
        $ics .= "END:VEVENT\r\n";
        $ics .= "END:VCALENDAR\r\n";

        return $ics;
    }
}

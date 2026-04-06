<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Models\NotificationTemplate;
use App\Services\TemplateVars;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingRejectedNotification extends Notification
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
        $subject = NotificationTemplate::renderSubject('email_booking_rejected', $vars)
            ?? 'Lesson Request Update - Kristine Skates';

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.booking-rejected', [
                'booking' => $this->booking,
                'templateBody' => NotificationTemplate::render('email_booking_rejected', $vars),
            ]);
    }
}

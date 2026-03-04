<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingRequestedNotification extends Notification
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
        return (new MailMessage)
            ->subject('Lesson Request Received - Kristine Skates')
            ->greeting('Thank you for your lesson request!')
            ->line('We have received your skating lesson request.')
            ->line('**Service:** ' . $this->booking->service->name)
            ->line('**Date:** ' . $this->booking->date->format('l, F j, Y'))
            ->line('**Time:** ' . \Carbon\Carbon::parse($this->booking->start_time)->format('g:i A'))
            ->line('**Location:** ' . $this->booking->timeSlot->rink->name)
            ->line('**Confirmation Code:** ' . $this->booking->confirmation_code)
            ->line('Coach Kristine will review your request and email you confirmation shortly.')
            ->line('**Note:** Lesson price does not include rink admission fee.')
            ->line('If you have any questions, please reply to this email.');
    }
}

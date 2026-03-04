<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewBookingNotification extends Notification
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
            ->subject('New Lesson Request - ' . $this->booking->client_name)
            ->greeting('New Lesson Request!')
            ->line('You have a new skating lesson request.')
            ->line('**Client:** ' . $this->booking->client_name)
            ->line('**Email:** ' . $this->booking->client_email)
            ->line('**Phone:** ' . $this->booking->client_phone)
            ->line('**Service:** ' . $this->booking->service->name)
            ->line('**Date:** ' . $this->booking->date->format('l, F j, Y'))
            ->line('**Time:** ' . \Carbon\Carbon::parse($this->booking->start_time)->format('g:i A'))
            ->line('**Rink:** ' . $this->booking->timeSlot->rink->name)
            ->line('**Price:** $' . number_format($this->booking->price_paid, 2))
            ->line('**Notes:** ' . ($this->booking->notes ?: 'None'))
            ->action('Review Booking', url('/admin/bookings'))
            ->line('Please review and approve or reject this request.');
    }
}

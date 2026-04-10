<?php

namespace App\Notifications;

use App\Models\BookingInterest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WaitlistConfirmationNotification extends Notification
{
    use Queueable;

    public function __construct(public BookingInterest $interest) {}

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("You're on the Waitlist! — Kristine Skates")
            ->view('emails.waitlist-confirmation', ['interest' => $this->interest]);
    }
}

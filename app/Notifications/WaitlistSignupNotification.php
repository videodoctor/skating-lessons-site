<?php

namespace App\Notifications;

use App\Models\BookingInterest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WaitlistSignupNotification extends Notification
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
            ->subject('New Waitlist Sign-up: ' . $this->interest->name)
            ->view('emails.waitlist-signup', ['interest' => $this->interest]);
    }
}

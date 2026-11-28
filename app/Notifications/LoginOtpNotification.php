<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoginOtpNotification extends Notification
{
    public function __construct(public string $code) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->subject('Your La Lassaura sign-in code')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Use this code to sign in to your La Lassaura account:')
            ->line($this->code)
            ->line('This code expires in '.config('verification.expiry_minutes').' minutes and can only be used once.')
            ->line('Do not share this code. If you did not request it, you can ignore this email.');
    }
}

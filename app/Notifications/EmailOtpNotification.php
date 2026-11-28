<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailOtpNotification extends Notification
{
    public function __construct(public string $code) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->subject('Verify your La Lassaura email')->greeting('Hello '.$notifiable->name.',')->line('Your email verification code is:')->line($this->code)->line('This code expires in '.config('verification.expiry_minutes').' minutes. Do not share it with anyone.')->line('If you did not request this code, you can ignore this email.');
    }
}

<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPassword extends Notification
{
    public $token;
    public $action;

    public function __construct($token)
    {
        $this->token = $token;
        $this->action = config('app.front_end_url') . '/password/reset/' . $this->token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your Reset Password Subject Here')
            ->line('You are receiving this email because we received a password reset request for your account.')
            ->action('Reset Password', $this->action)
            ->line('If you did not request a password reset, no further action is required.');
    }
}

<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

class ResetPassword extends Notification
{
    public $token;
    public $action;

    /**
     * ResetPassword constructor.
     *
     * @param   string  $token
     *
     * @return  void
     */
    public function __construct($token)
    {
        $this->token = $token;
        $this->action = config('app.front_end_url') . '/password/reset/' . $this->token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param   mixed  $notifiable
     *
     * @return  array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param   mixed  $notifiable
     *
     * @return  \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Reset Password Notification')
            ->line('You are receiving this email because we received a password reset request for your account.')
            ->action('Reset Password', $this->action)
            ->line('This password reset link will expire in ' . config('auth.passwords.users.expire') . ' minutes.')
            ->line('If you did not request a password reset, no further action is required.');
    }
}

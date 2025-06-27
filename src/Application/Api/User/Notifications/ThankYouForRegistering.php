<?php

namespace Application\Api\User\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ThankYouForRegistering extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct() {}

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Thank you for registering!')
            ->greeting('Welcome to ' . config('app.name') . ', ' . $notifiable->first_name . '!')
            ->line('Thank you for registering with us. We\'re excited to have you on board.')
            ->action('Go to Dashboard', config('app.url'))
            ->line('If you have any questions, feel free to reply to this email.')
            ->salutation('Thanks, The ' . config('app.name') . ' Team');
    }
}

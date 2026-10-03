<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Sent once the user has confirmed their email address. */
class EmailVerifiedNotification extends Notification
{
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return BrandedMessage::make(__('Your email is confirmed — AutoNova'), [
            'kicker' => __('All set'),
            'title' => __('Your email is confirmed'),
            'preheader' => __('Your AutoNova account is now fully active.'),
            'greeting' => __('Hello :name,', ['name' => $notifiable->name]),
            'lines' => [
                __('Your AutoNova account is now fully active. You can post listings, save searches and message sellers.'),
            ],
            'actionText' => __('Post your first listing'),
            'actionUrl' => route('vehicles.create'),
        ]);
    }
}

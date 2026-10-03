<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Sent after a password has been reset through the "Forgot password?" link. */
class PasswordChangedNotification extends Notification
{
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return BrandedMessage::make(__('Your password was changed — AutoNova'), [
            'kicker' => __('Account security'),
            'title' => __('Your password was changed'),
            'preheader' => __('You can now sign in with your new password.'),
            'greeting' => __('Hello :name,', ['name' => $notifiable->name]),
            'lines' => [
                __('The password for your AutoNova account was changed successfully. You can now sign in with your new password.'),
            ],
            'actionText' => __('Sign in'),
            'actionUrl' => route('login'),
            'outro' => [
                __('If this was not you, reset your password right away and contact us.'),
            ],
        ]);
    }
}

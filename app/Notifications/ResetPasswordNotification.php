<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

/** Sent from "Forgot password?": link to choose a new password. */
class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $minutes = config('auth.passwords.' . config('auth.defaults.passwords') . '.expire');

        return BrandedMessage::make(__('Reset your password — AutoNova'), [
            'kicker' => __('Account security'),
            'title' => __('Reset your password'),
            'preheader' => __('Choose a new password for your AutoNova account.'),
            'greeting' => __('Hello :name,', ['name' => $notifiable->name]),
            'lines' => [
                __('We received a request to reset the password for your AutoNova account. Click the button below to choose a new one.'),
            ],
            'actionText' => __('Choose a new password'),
            'actionUrl' => $this->resetUrl($notifiable),
            'outro' => [
                __('This link expires in :count minutes.', ['count' => $minutes]),
                __('If you did not ask for a password reset, you can ignore this email — your password stays the same.'),
            ],
        ]);
    }
}

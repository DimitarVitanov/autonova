<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Config;

/** Sent on registration: welcome + "confirm your email" link. */
class VerifyEmailNotification extends VerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        return BrandedMessage::make(__('Confirm your email — AutoNova'), [
            'kicker' => __('Welcome'),
            'title' => __('Confirm your email address'),
            'preheader' => __('One click and your AutoNova account is ready.'),
            'greeting' => __('Hello :name,', ['name' => $notifiable->name]),
            'lines' => [
                __('Thanks for joining AutoNova. Confirm your email address to start posting listings and messaging sellers.'),
            ],
            'actionText' => __('Confirm email'),
            'actionUrl' => $this->verificationUrl($notifiable),
            'outro' => [
                __('This link expires in :count minutes.', ['count' => Config::get('auth.verification.expire', 60)]),
                __('If you did not create an account, you can ignore this email.'),
            ],
        ]);
    }
}

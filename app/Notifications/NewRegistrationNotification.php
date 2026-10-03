<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Tells the site owner (MAIL_ADMIN_ADDRESS) that someone registered. */
class NewRegistrationNotification extends Notification
{
    public function __construct(private User $user)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return BrandedMessage::make(__('New registration: :name', ['name' => $this->user->name]), [
            'kicker' => __('New registration'),
            'title' => __('A new account was created'),
            'lines' => [],
            'details' => array_filter([
                __('Name') => $this->user->name,
                __('Email') => $this->user->email,
                __('Account type') => __($this->user->account_type === 'dealer' ? 'Dealer' : 'Private seller'),
                __('Dealership') => $this->user->dealer?->name,
                __('Phone') => $this->user->phone,
                __('City') => $this->user->city,
            ]),
            'actionText' => __('Open users'),
            'actionUrl' => route('admin.users'),
        ]);
    }
}

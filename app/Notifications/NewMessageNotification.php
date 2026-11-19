<?php

namespace App\Notifications;

use App\Models\Message;
use App\Models\Translation;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** Sent when someone writes to a user about a listing: a buyer's first inquiry, or a new message in the thread. */
class NewMessageNotification extends Notification
{
    public function __construct(private Message $message, private bool $isFirstContact) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $conversation = $this->message->conversation;
        $vehicle = $conversation->vehicle;
        $sender = $this->message->sender->name;
        $isSeller = $notifiable->id === $conversation->seller_id;
        $cover = $vehicle->coverImage;

        $subject = $this->isFirstContact
            ? __('New inquiry for “:title” — AutoNova', ['title' => $vehicle->title])
            : __('New message from :name — AutoNova', ['name' => $sender]);

        return BrandedMessage::make($subject, [
            'kicker' => $this->isFirstContact ? __('New inquiry') : __('New message'),
            'title' => $this->isFirstContact ? __('Someone is interested in your vehicle') : __('You have a new message'),
            'preheader' => __(':name wrote to you about “:title”.', ['name' => $sender, 'title' => $vehicle->title]),
            'greeting' => __('Hello :name,', ['name' => $notifiable->name]),
            'lines' => [
                $this->isFirstContact
                    ? __(':name sent you a message about your listing:', ['name' => $sender])
                    : __(':name sent you a new message:', ['name' => $sender]),
            ],
            'vehicle' => [
                'title' => $vehicle->title,
                'price' => '€'.number_format((float) $vehicle->price, 0, ',', '.'),
                'meta' => collect([$vehicle->year, $vehicle->city ? Translation::label('city.'.$vehicle->city, $vehicle->city) : null])
                    ->filter()->implode(' · '),
                'image' => $cover ? url(Storage::url($cover->variants[640] ?? $cover->path)) : null,
                'url' => route('vehicles.show', $vehicle),
            ],
            'quote' => Str::limit($this->message->body, 600),
            'actionText' => __('Reply to the message'),
            'actionUrl' => route('inbox', ['conversation' => $conversation->id]),
            'outro' => [
                __('Replies to this email reach the AutoNova team, not :name — use the button above to answer.', ['name' => $sender]),
                $isSeller
                    ? __('Safety tip: AutoNova never asks for card details or SMS codes in a message.')
                    : __('Safety tip: never pay a deposit before you have seen the vehicle and its documents in person.'),
            ],
        ]);
    }
}

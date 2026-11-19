<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Models\Vehicle;
use App\Notifications\NewMessageNotification;
use Illuminate\Http\Request;

use function Illuminate\Support\defer;

class ConversationController extends Controller
{
    /** Buyer starts a conversation from a listing. */
    public function start(Request $request, Vehicle $vehicle)
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        abort_unless($vehicle->isPubliclyVisible(), 404);

        $user = $request->user();

        if ($user->id === $vehicle->user_id) {
            return back()->with('error', __('This is your own listing.'));
        }

        $conversation = Conversation::firstOrCreate(
            ['vehicle_id' => $vehicle->id, 'buyer_id' => $user->id, 'seller_id' => $vehicle->user_id],
            ['last_message_at' => now()]
        );

        $message = $conversation->messages()->create([
            'sender_id' => $user->id,
            'body' => $data['body'],
        ]);
        $conversation->update(['last_message_at' => now()]);
        $this->emailRecipient($conversation, $message, $user, $conversation->wasRecentlyCreated);

        return redirect()->route('inbox', ['conversation' => $conversation->id])
            ->with('success', __('Message sent to the seller.'));
    }

    /** Reply within an existing conversation. */
    public function message(Request $request, Conversation $conversation)
    {
        $user = $request->user();
        abort_unless(in_array($user->id, [$conversation->buyer_id, $conversation->seller_id], true), 403);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $message = $conversation->messages()->create([
            'sender_id' => $user->id,
            'body' => $data['body'],
        ]);
        $conversation->update(['last_message_at' => now()]);
        $this->emailRecipient($conversation, $message, $user, false);

        return back()->with('success', __('Message sent.'));
    }

    /**
     * Emails the other side about a new message — once per unread burst, so a
     * live chat does not flood their inbox. Sent after the response, and a mail
     * outage never breaks messaging.
     */
    private function emailRecipient(Conversation $conversation, Message $message, User $sender, bool $isFirstContact): void
    {
        $recipient = $conversation->counterpart($sender);
        $unreadFromSender = $conversation->messages()->where('sender_id', $sender->id)->whereNull('read_at')->count();

        if ($recipient && $unreadFromSender === 1) {
            defer(fn () => rescue(fn () => $recipient->notify(new NewMessageNotification($message, $isFirstContact))));
        }
    }
}

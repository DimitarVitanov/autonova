<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    /** Buyer starts a conversation from a listing. */
    public function start(Request $request, Vehicle $vehicle)
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $user = $request->user();

        if ($user->id === $vehicle->user_id) {
            return back()->with('error', __('This is your own listing.'));
        }

        $conversation = Conversation::firstOrCreate(
            ['vehicle_id' => $vehicle->id, 'buyer_id' => $user->id, 'seller_id' => $vehicle->user_id],
            ['last_message_at' => now()]
        );

        $conversation->messages()->create([
            'sender_id' => $user->id,
            'body' => $data['body'],
        ]);
        $conversation->update(['last_message_at' => now()]);

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

        $conversation->messages()->create([
            'sender_id' => $user->id,
            'body' => $data['body'],
        ]);
        $conversation->update(['last_message_at' => now()]);

        return back()->with('success', __('Message sent.'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InboxController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $conversations = Conversation::where('buyer_id', $user->id)
            ->orWhere('seller_id', $user->id)
            ->with(['vehicle.coverImage', 'buyer', 'seller', 'latestMessage'])
            ->withCount(['messages as unread_count' => fn ($q) => $q->whereNull('read_at')->where('sender_id', '!=', $user->id)])
            ->orderByDesc('last_message_at')
            ->get()
            ->map(function ($c) use ($user) {
                $other = $c->counterpart($user);
                $last = $c->latestMessage;

                return [
                    'id' => $c->id,
                    'with' => $other?->name ?? __('User'),
                    'vehicle' => [
                        'id' => $c->vehicle->id,
                        'slug' => $c->vehicle->slug,
                        'title' => $c->vehicle->title,
                        'price' => $c->vehicle->price,
                        'cover_url' => $c->vehicle->cover_url,
                    ],
                    'last' => $last?->body,
                    'last_at' => $c->last_message_at?->diffForHumans(),
                    'unread' => $c->unread_count,
                ];
            });

        $activeId = $request->input('conversation', $conversations->first()['id'] ?? null);
        $active = null;

        if ($activeId) {
            $conversation = Conversation::where('id', $activeId)
                ->where(fn ($q) => $q->where('buyer_id', $user->id)->orWhere('seller_id', $user->id))
                ->with(['vehicle.coverImage', 'buyer', 'seller', 'messages.sender'])
                ->first();

            if ($conversation) {
                $conversation->messages()
                    ->whereNull('read_at')
                    ->where('sender_id', '!=', $user->id)
                    ->update(['read_at' => now()]);

                $other = $conversation->counterpart($user);
                $active = [
                    'id' => $conversation->id,
                    'with' => $other?->name ?? __('User'),
                    'vehicle' => [
                        'id' => $conversation->vehicle->id,
                        'slug' => $conversation->vehicle->slug,
                        'title' => $conversation->vehicle->title,
                        'price' => $conversation->vehicle->price,
                        'cover_url' => $conversation->vehicle->cover_url,
                    ],
                    'messages' => $conversation->messages->map(fn ($m) => [
                        'id' => $m->id,
                        'body' => $m->body,
                        'mine' => $m->sender_id === $user->id,
                        'at' => $m->created_at->translatedFormat('H:i · d M'),
                    ]),
                ];
            }
        }

        return Inertia::render('Inbox', [
            'conversations' => $conversations,
            'active' => $active,
        ]);
    }
}

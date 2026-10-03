<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class MessagingSeeder extends Seeder
{
    public function run(): void
    {
        $buyers = User::whereIn('email', [
            'marko@autonova.test', 'ana@autonova.test', 'ilir@autonova.test', 'stefan@autonova.test',
        ])->get()->keyBy('email');

        // A few realistic threads on dealer listings.
        $threads = [
            ['buyer' => 'marko@autonova.test', 'msgs' => [
                ['from' => 'buyer', 'body' => 'Good day, is the vehicle still available and does it have a service book?', 'ago' => 180],
                ['from' => 'seller', 'body' => 'Good day, it is available. Service book and history from an authorised dealer — yes.', 'ago' => 172],
                ['from' => 'buyer', 'body' => 'Could I come for a test drive on Saturday morning?', 'ago' => 20],
            ]],
            ['buyer' => 'ana@autonova.test', 'msgs' => [
                ['from' => 'buyer', 'body' => 'Hello, is the price including VAT?', 'ago' => 1440],
                ['from' => 'seller', 'body' => 'Hello, yes the listed price is with VAT. An R1 invoice is possible.', 'ago' => 1400],
            ]],
            ['buyer' => 'ilir@autonova.test', 'msgs' => [
                ['from' => 'buyer', 'body' => 'Do you have a photo of the cargo area?', 'ago' => 1500],
            ]],
        ];

        $dealerVehicles = Vehicle::active()->whereNotNull('dealer_id')->with('user')->get();
        if ($dealerVehicles->isEmpty()) {
            return;
        }

        foreach ($threads as $t) {
            $buyer = $buyers[$t['buyer']] ?? null;
            $vehicle = $dealerVehicles->random();
            $seller = $vehicle->user;
            if (! $buyer || ! $seller || $buyer->id === $seller->id) {
                continue;
            }

            $conversation = Conversation::firstOrCreate(
                ['vehicle_id' => $vehicle->id, 'buyer_id' => $buyer->id, 'seller_id' => $seller->id],
                ['last_message_at' => now()]
            );

            $last = now();
            foreach ($t['msgs'] as $m) {
                $sender = $m['from'] === 'buyer' ? $buyer : $seller;
                $at = now()->subMinutes($m['ago']);
                $last = $at;
                Message::create([
                    'conversation_id' => $conversation->id,
                    'sender_id' => $sender->id,
                    'body' => $m['body'],
                    'read_at' => $m['from'] === 'seller' ? $at : null,
                    'created_at' => $at,
                    'updated_at' => $at,
                ]);
            }
            $conversation->update(['last_message_at' => $last]);
        }
    }
}

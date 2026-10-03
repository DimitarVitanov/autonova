<?php

namespace Database\Seeders;

use App\Models\Dealer;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Staff ----------------------------------------------------------
        User::updateOrCreate(['email' => 'admin@autonova.test'], [
            'name' => 'AutoNova Admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'account_type' => 'private',
            'city' => 'Skopje',
            'phone' => '+389 70 111 111',
            'email_verified_at' => now(),
        ]);

        User::updateOrCreate(['email' => 'moderator@autonova.test'], [
            'name' => 'Elena Moderator',
            'password' => Hash::make('password'),
            'role' => 'moderator',
            'account_type' => 'private',
            'city' => 'Skopje',
            'phone' => '+389 70 222 222',
            'email_verified_at' => now(),
        ]);

        // ---- Dealers --------------------------------------------------------
        $dealers = [
            [
                'email' => 'vardar@autonova.test', 'name' => 'Auto Centar Vardar',
                'city' => 'Skopje', 'address' => 'Ilinden 42, Skopje', 'founded_year' => 2011,
                'verified' => true, 'package' => 'pro', 'phone' => '+389 70 300 300',
                'about' => 'Sales of checked vehicles imported from Germany and Switzerland. Mileage guarantee, registration assistance and financing.',
                'hours' => 'Mon–Sat 09:00–19:00', 'rating' => 4.8, 'promo_credits' => 18,
            ],
            [
                'email' => 'premium@autonova.test', 'name' => 'Premium Motors',
                'city' => 'Skopje', 'address' => 'Boulevard Partizanski 88, Skopje', 'founded_year' => 2015,
                'verified' => true, 'package' => 'max', 'phone' => '+389 71 400 400',
                'about' => 'Premium and executive vehicles. Full service history, extended warranty available.',
                'hours' => 'Mon–Fri 09:00–20:00, Sat 10:00–16:00', 'rating' => 4.9, 'promo_credits' => 40,
            ],
            [
                'email' => 'kombe@autonova.test', 'name' => 'Kombe Centar',
                'city' => 'Tetovo', 'address' => 'Marshal Tito 12, Tetovo', 'founded_year' => 2009,
                'verified' => true, 'package' => 'pro', 'phone' => '+389 72 500 500',
                'about' => 'Specialists in vans and light commercial vehicles for business.',
                'hours' => 'Mon–Sat 08:00–18:00', 'rating' => 4.6, 'promo_credits' => 10,
            ],
            [
                'email' => 'agro@autonova.test', 'name' => 'Agro Mehanika',
                'city' => 'Bitola', 'address' => 'Novachki pat bb, Bitola', 'founded_year' => 2004,
                'verified' => false, 'package' => 'start', 'phone' => '+389 75 600 600',
                'about' => 'Agricultural machinery, tractors and trailers. New and used.',
                'hours' => 'Mon–Fri 08:00–17:00', 'rating' => 4.4, 'promo_credits' => 4,
            ],
        ];

        foreach ($dealers as $d) {
            $user = User::updateOrCreate(['email' => $d['email']], [
                'name' => $d['name'],
                'password' => Hash::make('password'),
                'role' => 'client',
                'account_type' => 'dealer',
                'city' => $d['city'],
                'phone' => $d['phone'],
                'email_verified_at' => now(),
            ]);

            Dealer::updateOrCreate(['user_id' => $user->id], [
                'name' => $d['name'],
                'slug' => str($d['name'])->slug(),
                'city' => $d['city'],
                'address' => $d['address'],
                'founded_year' => $d['founded_year'],
                'verified' => $d['verified'],
                'phone' => $d['phone'],
                'hours' => $d['hours'],
                'about' => $d['about'],
                'package' => $d['package'],
                'package_until' => now()->addDays(30),
                'promo_credits' => $d['promo_credits'],
                'rating' => $d['rating'],
            ]);
        }

        // ---- Private clients ------------------------------------------------
        $clients = [
            ['email' => 'marko@autonova.test', 'name' => 'Marko Stojanov', 'city' => 'Skopje', 'phone' => '+389 70 700 700'],
            ['email' => 'ana@autonova.test', 'name' => 'Ana Petrovska', 'city' => 'Bitola', 'phone' => '+389 71 800 800'],
            ['email' => 'ilir@autonova.test', 'name' => 'Ilir Bekiri', 'city' => 'Tetovo', 'phone' => '+389 72 900 900'],
            ['email' => 'stefan@autonova.test', 'name' => 'Stefan Trajkov', 'city' => 'Kumanovo', 'phone' => '+389 75 010 010'],
        ];

        foreach ($clients as $c) {
            User::updateOrCreate(['email' => $c['email']], [
                'name' => $c['name'],
                'password' => Hash::make('password'),
                'role' => 'client',
                'account_type' => 'private',
                'city' => $c['city'],
                'phone' => $c['phone'],
                'email_verified_at' => now(),
            ]);
        }
    }
}

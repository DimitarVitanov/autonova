<?php

namespace Database\Seeders;

use App\Models\Dealer;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * The first real profiles that post stock on the live site: one private seller
 * and one dealership (salon). Idempotent — safe to re-run.
 *
 *   php artisan db:seed --class=LiveProfilesSeeder
 */
class LiveProfilesSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Private seller -------------------------------------------------
        User::updateOrCreate(['email' => 'dimitar@autonova.test'], [
            'name' => 'Dimitar Trajkov',
            'password' => Hash::make('password'),
            'role' => 'client',
            'account_type' => 'private',
            'email_verified_at' => now(),
        ]);

        // ---- Dealer (salon) -------------------------------------------------
        $salon = User::updateOrCreate(['email' => 'deluxauto@autonova.test'], [
            'name' => 'Delux Auto Strumica',
            'password' => Hash::make('password'),
            'role' => 'client',
            'account_type' => 'dealer',
            'city' => 'Strumica',
            'email_verified_at' => now(),
        ]);

        Dealer::updateOrCreate(['user_id' => $salon->id], [
            'name' => 'Delux Auto Strumica',
            'slug' => 'delux-auto-strumica',
            'city' => 'Strumica',
            'verified' => true,
        ]);
    }
}

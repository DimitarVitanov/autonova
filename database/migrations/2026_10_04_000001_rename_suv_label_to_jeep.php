<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Client request: the SUV body type reads just "Џип". Only touches the
    // seeded default so an admin-edited label is left alone.
    public function up(): void
    {
        DB::table('translations')->where('key', 'body.SUV')->where('mk', 'Џип (SUV)')->update(['mk' => 'Џип']);
    }

    public function down(): void
    {
        DB::table('translations')->where('key', 'body.SUV')->where('mk', 'Џип')->update(['mk' => 'Џип (SUV)']);
    }
};

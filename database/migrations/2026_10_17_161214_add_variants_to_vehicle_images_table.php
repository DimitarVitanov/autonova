<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Responsive WebP renditions of each photo, keyed by pixel width
     * (thumb / mobile / tablet / desktop) — feeds the <img srcset>.
     */
    public function up(): void
    {
        Schema::table('vehicle_images', function (Blueprint $table) {
            $table->json('variants')->nullable()->after('thumb_path');
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_images', function (Blueprint $table) {
            $table->dropColumn('variants');
        });
    }
};

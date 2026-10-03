<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('thumb_path')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_cover')->default(false);
            $table->timestamps();
        });

        Schema::create('feature_vehicle', function (Blueprint $table) {
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('feature_id')->constrained()->cascadeOnDelete();
            $table->primary(['vehicle_id', 'feature_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_vehicle');
        Schema::dropIfExists('vehicle_images');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('model_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_model_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
            $table->unique(['car_model_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('model_versions');
    }
};

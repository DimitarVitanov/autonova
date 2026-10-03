<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('name_plural')->nullable();
            $table->string('hint')->nullable();
            $table->string('icon')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('makes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->index();
            $table->timestamps();
            $table->unique(['category_id', 'slug']);
        });

        Schema::create('car_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('make_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->index();
            $table->timestamps();
        });

        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('group')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('features');
        Schema::dropIfExists('car_models');
        Schema::dropIfExists('makes');
        Schema::dropIfExists('categories');
    }
};

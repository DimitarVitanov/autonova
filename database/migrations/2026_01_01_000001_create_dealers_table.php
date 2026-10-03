<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dealers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->unsignedSmallInteger('founded_year')->nullable();
            $table->boolean('verified')->default(false);
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('hours')->nullable();
            $table->text('about')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('cover_path')->nullable();
            $table->enum('package', ['start', 'pro', 'max'])->default('start');
            $table->date('package_until')->nullable();
            $table->unsignedInteger('promo_credits')->default(0);
            $table->decimal('rating', 2, 1)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dealers');
    }
};

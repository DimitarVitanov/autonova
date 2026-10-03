<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dealer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->constrained();
            $table->foreignId('make_id')->nullable()->constrained();
            $table->foreignId('car_model_id')->nullable()->constrained();

            $table->string('title');
            $table->string('slug')->unique();
            $table->string('version')->nullable();

            // Pricing (EUR is the primary currency, MKD shown as reference)
            $table->unsignedInteger('price');
            $table->enum('vat', ['with_vat', 'without_vat', 'negotiable'])->default('with_vat');

            // Core specs
            $table->unsignedSmallInteger('year')->index();
            $table->unsignedInteger('mileage_km')->default(0)->index();
            $table->enum('fuel', ['petrol', 'diesel', 'hybrid', 'electric', 'lpg', 'cng'])->default('petrol')->index();
            $table->enum('transmission', ['manual', 'automatic', 'semi_automatic'])->default('manual');
            $table->unsignedInteger('engine_cc')->nullable();
            $table->unsignedInteger('power_hp')->nullable();
            $table->enum('drivetrain', ['fwd', 'rwd', 'awd'])->nullable();
            $table->string('body_type')->nullable();
            $table->unsignedTinyInteger('doors')->nullable();
            $table->unsignedTinyInteger('seats')->nullable();
            $table->string('color')->nullable();
            $table->enum('condition', ['new', 'used'])->default('used');
            $table->unsignedTinyInteger('owners')->nullable();
            $table->string('registered_until')->nullable();

            $table->string('city')->nullable()->index();
            $table->text('description')->nullable();
            $table->enum('seller_type', ['private', 'dealer'])->default('private')->index();
            $table->string('contact_phone')->nullable();

            // Moderation + promotion
            $table->enum('status', ['draft', 'pending', 'active', 'sold', 'rejected', 'expired'])->default('pending')->index();
            $table->string('reject_reason')->nullable();
            $table->enum('promotion', ['none', 'bump', 'featured', 'homepage'])->default('none')->index();
            $table->timestamp('promoted_until')->nullable();
            $table->boolean('is_featured')->default(false)->index();

            $table->unsignedInteger('views')->default(0);
            $table->timestamp('bumped_at')->nullable();
            $table->timestamp('published_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'status']);
            $table->index(['make_id', 'car_model_id']);
            $table->index(['status', 'price']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};

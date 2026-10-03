<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('group')->default('general')->index();
            $table->string('key')->unique();
            $table->text('en')->nullable();   // English source (reference)
            $table->text('mk')->nullable();   // Macedonian (editable in admin)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};

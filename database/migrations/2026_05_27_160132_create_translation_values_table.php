<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('translation_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('translation_string_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('language_code', 12)->index();
            $table->text('value')->nullable();
            $table->boolean('is_approved')->default(true)->index();
            $table->timestamps();

            $table->unique(['translation_string_id', 'language_code'], 'translation_values_string_language_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translation_values');
    }
};

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
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_profile_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('training_program_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('instructor_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->string('status')->default('pending')->index();
            $table->date('started_at')->nullable()->index();
            $table->date('completed_at')->nullable()->index();
            $table->unsignedInteger('contracted_price_cents');
            $table->unsignedInteger('paid_cents')->default(0);
            $table->timestamps();

            $table->index(['student_profile_id', 'status']);
            $table->index(['training_program_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};

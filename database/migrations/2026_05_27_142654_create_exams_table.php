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
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('instructor_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->string('exam_type')->index();
            $table->string('status')->default('scheduled')->index();
            $table->timestamp('scheduled_at')->index();
            $table->unsignedSmallInteger('attempt_number')->default(1);
            $table->unsignedSmallInteger('score')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['enrollment_id', 'exam_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};

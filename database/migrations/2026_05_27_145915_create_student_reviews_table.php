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
        Schema::create('student_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_profile_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('training_program_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('training_group_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('instructor_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->string('author_name');
            $table->unsignedTinyInteger('rating')->default(5);
            $table->string('title');
            $table->text('body');
            $table->string('video_url')->nullable();
            $table->text('admin_reply')->nullable();
            $table->string('status')->default('pending')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();

            $table->index(['training_program_id', 'status']);
            $table->index(['training_group_id', 'status']);
            $table->index(['instructor_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_reviews');
    }
};

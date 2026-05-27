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
        Schema::create('training_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('training_program_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('instructor_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('status')->default('planned')->index();
            $table->unsignedSmallInteger('capacity')->default(12);
            $table->date('starts_on')->nullable()->index();
            $table->date('ends_on')->nullable()->index();
            $table->json('meeting_days')->nullable();
            $table->time('meeting_time')->nullable();
            $table->string('classroom')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'status']);
            $table->index(['training_program_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_groups');
    }
};

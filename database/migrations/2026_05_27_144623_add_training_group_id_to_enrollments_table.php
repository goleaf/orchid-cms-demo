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
        Schema::table('enrollments', function (Blueprint $table) {
            $table->foreignId('training_group_id')
                ->nullable()
                ->after('training_program_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->index(['training_group_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropForeign(['training_group_id']);
            $table->dropIndex(['training_group_id', 'status']);
            $table->dropColumn('training_group_id');
        });
    }
};

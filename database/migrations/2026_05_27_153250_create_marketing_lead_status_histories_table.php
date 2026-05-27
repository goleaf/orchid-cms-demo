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
        Schema::create('marketing_lead_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marketing_lead_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->string('from_status')->nullable()->index();
            $table->string('to_status')->index();
            $table->text('reason')->nullable();
            $table->timestamp('changed_at')->index();
            $table->timestamps();

            $table->index(['marketing_lead_id', 'changed_at']);
            $table->index(['to_status', 'changed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_lead_status_histories');
    }
};

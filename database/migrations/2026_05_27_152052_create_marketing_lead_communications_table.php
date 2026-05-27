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
        Schema::create('marketing_lead_communications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marketing_lead_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->string('channel')->index();
            $table->string('direction')->default('outbound')->index();
            $table->string('subject')->nullable();
            $table->text('body')->nullable();
            $table->timestamp('communicated_at')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['marketing_lead_id', 'communicated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_lead_communications');
    }
};

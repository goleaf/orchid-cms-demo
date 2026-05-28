<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('analytics_snapshots')) {
            Schema::create('analytics_snapshots', function (Blueprint $table): void {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('snapshot_type')->index();
                $table->string('period_type')->index();
                $table->date('period_start')->index();
                $table->date('period_end')->nullable()->index();
                $table->foreignId('branch_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
                $table->json('data');
                $table->timestamp('calculated_at')->index();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['snapshot_type', 'period_type', 'period_start'], 'analytics_snapshots_type_period_start_idx');
                $table->index(['branch_id', 'user_id', 'period_type'], 'analytics_snapshots_branch_user_period_idx');
            });
        }

        if (! Schema::hasTable('analytics_cache_entries')) {
            Schema::create('analytics_cache_entries', function (Blueprint $table): void {
                $table->id();
                $table->string('cache_key')->unique();
                $table->json('data');
                $table->json('tags')->nullable();
                $table->timestamp('expires_at')->nullable()->index();
                $table->timestamp('calculated_at')->index();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_cache_entries');
        Schema::dropIfExists('analytics_snapshots');
    }
};

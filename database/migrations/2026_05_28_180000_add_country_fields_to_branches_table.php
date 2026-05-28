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
        Schema::table('branches', function (Blueprint $table): void {
            if (! Schema::hasColumn('branches', 'country')) {
                $table->string('country')->default('Lithuania')->after('slug');
            }

            if (! Schema::hasColumn('branches', 'country_translations')) {
                $table->json('country_translations')->nullable()->after('country');
            }

            if (! Schema::hasIndex('branches', 'branches_country_city_active_idx')) {
                $table->index(['country', 'city', 'is_active'], 'branches_country_city_active_idx');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table): void {
            if (Schema::hasIndex('branches', 'branches_country_city_active_idx')) {
                $table->dropIndex('branches_country_city_active_idx');
            }

            $columns = collect(['country', 'country_translations'])
                ->filter(fn (string $column): bool => Schema::hasColumn('branches', $column))
                ->all();

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};

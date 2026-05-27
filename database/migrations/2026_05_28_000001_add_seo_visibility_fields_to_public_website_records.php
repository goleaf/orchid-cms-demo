<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_pages', function (Blueprint $table): void {
            if (! Schema::hasColumn('site_pages', 'canonical_url')) {
                $table->string('canonical_url')->nullable()->after('og_image');
            }
        });

        Schema::table('training_programs', function (Blueprint $table): void {
            if (! Schema::hasColumn('training_programs', 'is_indexable')) {
                $table->boolean('is_indexable')->default(true)->after('is_visible_on_site')->index();
            }
        });

        Schema::table('branches', function (Blueprint $table): void {
            if (! Schema::hasColumn('branches', 'is_indexable')) {
                $table->boolean('is_indexable')->default(true)->after('is_visible_on_site')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table): void {
            if (Schema::hasColumn('branches', 'is_indexable')) {
                $table->dropColumn('is_indexable');
            }
        });

        Schema::table('training_programs', function (Blueprint $table): void {
            if (Schema::hasColumn('training_programs', 'is_indexable')) {
                $table->dropColumn('is_indexable');
            }
        });

        Schema::table('site_pages', function (Blueprint $table): void {
            if (Schema::hasColumn('site_pages', 'canonical_url')) {
                $table->dropColumn('canonical_url');
            }
        });
    }
};

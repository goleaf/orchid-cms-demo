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
        Schema::table('marketing_leads', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->unique()->after('id');
            $table->string('landing_page')->nullable()->after('referrer_url');
            $table->string('form_page')->nullable()->after('landing_page');
            $table->string('form_name')->nullable()->after('form_page')->index();
            $table->string('locale', 12)->nullable()->after('form_name')->index();
            $table->string('ip_address', 45)->nullable()->after('locale');
            $table->text('user_agent')->nullable()->after('ip_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_leads', function (Blueprint $table) {
            $table->dropUnique(['uuid']);
            $table->dropIndex(['form_name']);
            $table->dropIndex(['locale']);
            $table->dropColumn([
                'uuid',
                'landing_page',
                'form_page',
                'form_name',
                'locale',
                'ip_address',
                'user_agent',
            ]);
        });
    }
};

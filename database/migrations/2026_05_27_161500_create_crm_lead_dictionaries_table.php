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
        Schema::create('lead_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name')->nullable();
            $table->json('name_translations')->nullable();
            $table->boolean('is_system')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('lead_sources', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name')->nullable();
            $table->json('name_translations')->nullable();
            $table->boolean('is_system')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('lead_lost_reasons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name')->nullable();
            $table->json('name_translations')->nullable();
            $table->boolean('is_system')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('lead_tags', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name')->nullable();
            $table->json('name_translations')->nullable();
            $table->boolean('is_system')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        Schema::table('marketing_leads', function (Blueprint $table) {
            $table->string('lost_reason_code')->nullable()->after('rejection_reason')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_leads', function (Blueprint $table) {
            $table->dropColumn('lost_reason_code');
        });

        Schema::dropIfExists('lead_tags');
        Schema::dropIfExists('lead_lost_reasons');
        Schema::dropIfExists('lead_sources');
        Schema::dropIfExists('lead_statuses');
    }
};

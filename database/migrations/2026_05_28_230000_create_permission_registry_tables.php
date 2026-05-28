<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->createPermissionGroupsTable();
        $this->createPermissionRegistryItemsTable();
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_registry_items');
        Schema::dropIfExists('permission_groups');
    }

    private function createPermissionGroupsTable(): void
    {
        if (Schema::hasTable('permission_groups')) {
            return;
        }

        Schema::create('permission_groups', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->json('name_translations')->nullable();
            $table->json('description_translations')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->integer('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_system')->default(false)->index();
            $table->timestamps();
        });
    }

    private function createPermissionRegistryItemsTable(): void
    {
        if (Schema::hasTable('permission_registry_items')) {
            return;
        }

        Schema::create('permission_registry_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('permission_group_id')
                ->nullable()
                ->constrained('permission_groups')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->string('code')->unique();
            $table->json('name_translations')->nullable();
            $table->json('description_translations')->nullable();
            $table->string('module')->nullable()->index();
            $table->string('risk_level')->default('normal')->index();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_system')->default(true)->index();
            $table->integer('sort_order')->default(0)->index();
            $table->timestamps();

            $table->index(['permission_group_id', 'sort_order'], 'registry_items_group_sort_idx');
            $table->index(['module', 'risk_level'], 'registry_items_module_risk_idx');
        });
    }
};

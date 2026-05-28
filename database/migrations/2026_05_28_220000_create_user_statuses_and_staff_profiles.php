<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->createUserStatusesTable();
        $this->createStaffProfilesTable();
        $this->extendUsersTable();
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_profiles');

        $this->dropColumnsIfExist('users', [
            'status_id',
            'timezone',
            'last_login_at',
            'last_seen_at',
            'must_change_password',
        ]);

        Schema::dropIfExists('user_statuses');
    }

    private function createUserStatusesTable(): void
    {
        if (Schema::hasTable('user_statuses')) {
            return;
        }

        Schema::create('user_statuses', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->json('name_translations')->nullable();
            $table->json('description_translations')->nullable();
            $table->string('color')->nullable();
            $table->integer('sort_order')->default(0)->index();
            $table->boolean('is_default')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_blocked')->default(false)->index();
            $table->boolean('is_archived')->default(false)->index();
            $table->boolean('is_final')->default(false)->index();
            $table->timestamps();
        });
    }

    private function createStaffProfilesTable(): void
    {
        if (Schema::hasTable('staff_profiles')) {
            return;
        }

        Schema::create('staff_profiles', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('staff_number')->nullable()->unique();
            $table->foreignId('branch_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->json('display_name_translations')->nullable();
            $table->json('job_title_translations')->nullable();
            $table->json('public_bio_translations')->nullable();
            $table->string('phone')->nullable();
            $table->string('work_email')->nullable();
            $table->string('preferred_locale', 12)->nullable()->index();
            $table->string('timezone', 64)->nullable();
            $table->string('avatar')->nullable();
            $table->boolean('is_visible_on_site')->default(false)->index();
            $table->text('internal_notes')->nullable();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['branch_id', 'is_visible_on_site'], 'staff_profiles_branch_visible_idx');
        });
    }

    private function extendUsersTable(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'status_id')) {
                $table->foreignId('status_id')->nullable()->constrained('user_statuses')->cascadeOnUpdate()->nullOnDelete();
            }

            if (! Schema::hasColumn('users', 'preferred_locale')) {
                $table->string('preferred_locale', 12)->nullable()->index();
            }

            if (! Schema::hasColumn('users', 'timezone')) {
                $table->string('timezone', 64)->nullable();
            }

            if (! Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->index();
            }

            if (! Schema::hasColumn('users', 'last_seen_at')) {
                $table->timestamp('last_seen_at')->nullable()->index();
            }

            if (! Schema::hasColumn('users', 'password_changed_at')) {
                $table->timestamp('password_changed_at')->nullable();
            }

            if (! Schema::hasColumn('users', 'must_change_password')) {
                $table->boolean('must_change_password')->default(false)->index();
            }
        });
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function dropColumnsIfExist(string $tableName, array $columns): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        $existing = array_values(array_filter(
            $columns,
            fn (string $column): bool => Schema::hasColumn($tableName, $column),
        ));

        if ($existing === []) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($existing): void {
            $table->dropColumn($existing);
        });
    }
};

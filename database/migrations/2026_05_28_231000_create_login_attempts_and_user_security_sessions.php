<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('login_attempts')) {
            Schema::table('login_attempts', function (Blueprint $table): void {
                if (! Schema::hasColumn('login_attempts', 'uuid')) {
                    $table->uuid('uuid')->nullable()->unique()->after('id');
                }

                if (! Schema::hasColumn('login_attempts', 'email')) {
                    $table->string('email')->nullable()->index()->after('user_id');
                }

                if (! Schema::hasColumn('login_attempts', 'guard')) {
                    $table->string('guard')->nullable()->after('email');
                }

                if (! Schema::hasColumn('login_attempts', 'user_agent')) {
                    $table->text('user_agent')->nullable()->after('ip_address');
                }

                if (! Schema::hasColumn('login_attempts', 'attempted_at')) {
                    $table->timestamp('attempted_at')->nullable()->index()->after('failure_reason');
                }

                if (! Schema::hasColumn('login_attempts', 'metadata')) {
                    $table->json('metadata')->nullable()->after('attempted_at');
                }
            });
        }

        if (! Schema::hasTable('user_security_sessions')) {
            Schema::create('user_security_sessions', function (Blueprint $table): void {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('session_id_hash', 64)->unique();
                $table->string('guard')->nullable()->index();
                $table->string('ip_address', 45)->nullable()->index();
                $table->text('user_agent')->nullable();
                $table->string('device_name')->nullable();
                $table->string('browser_name')->nullable();
                $table->string('platform_name')->nullable();
                $table->string('country')->nullable();
                $table->string('city')->nullable();
                $table->timestamp('logged_in_at')->nullable()->index();
                $table->timestamp('last_activity_at')->nullable()->index();
                $table->timestamp('logged_out_at')->nullable()->index();
                $table->timestamp('revoked_at')->nullable()->index();
                $table->foreignId('revoked_by_id')->nullable()->constrained('users')->nullOnDelete();
                $table->boolean('is_current')->default(false)->index();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'revoked_at']);
                $table->index(['user_id', 'logged_out_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_security_sessions');

        if (Schema::hasTable('login_attempts')) {
            Schema::table('login_attempts', function (Blueprint $table): void {
                foreach ([
                    'metadata',
                    'attempted_at',
                    'user_agent',
                    'guard',
                    'email',
                    'uuid',
                ] as $column) {
                    if (Schema::hasColumn('login_attempts', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};

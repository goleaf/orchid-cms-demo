<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->extendAdmissionChecklistItems();
        $this->extendSessionChecklistItems();
    }

    public function down(): void
    {
        $this->dropColumnsIfExist('exam_checklist_items', [
            'passed',
            'message_key',
            'checked_at',
            'checked_by',
        ]);

        $this->dropColumnsIfExist('exam_admission_checklist_items', [
            'key',
            'required',
            'passed',
            'message_key',
            'checked_by',
        ]);
    }

    private function extendAdmissionChecklistItems(): void
    {
        if (! Schema::hasTable('exam_admission_checklist_items')) {
            return;
        }

        Schema::table('exam_admission_checklist_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('exam_admission_checklist_items', 'key')) {
                $table->string('key')->nullable()->after('code')->index();
            }

            if (! Schema::hasColumn('exam_admission_checklist_items', 'required')) {
                $table->boolean('required')->default(true)->after('title_translations')->index();
            }

            if (! Schema::hasColumn('exam_admission_checklist_items', 'passed')) {
                $table->boolean('passed')->default(false)->after('required')->index();
            }

            if (! Schema::hasColumn('exam_admission_checklist_items', 'message_key')) {
                $table->string('message_key')->nullable()->after('status');
            }

            if (! Schema::hasColumn('exam_admission_checklist_items', 'checked_by')) {
                $table->unsignedBigInteger('checked_by')->nullable()->after('checked_by_id')->index('exam_admission_items_checked_by_idx');
            }
        });
    }

    private function extendSessionChecklistItems(): void
    {
        if (! Schema::hasTable('exam_checklist_items')) {
            return;
        }

        Schema::table('exam_checklist_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('exam_checklist_items', 'passed')) {
                $table->boolean('passed')->default(false)->after('required')->index();
            }

            if (! Schema::hasColumn('exam_checklist_items', 'message_key')) {
                $table->string('message_key')->nullable()->after('status');
            }

            if (! Schema::hasColumn('exam_checklist_items', 'checked_at')) {
                $table->timestamp('checked_at')->nullable()->after('message_key')->index();
            }

            if (! Schema::hasColumn('exam_checklist_items', 'checked_by')) {
                $table->unsignedBigInteger('checked_by')->nullable()->after('checked_at')->index('exam_checklist_items_checked_by_idx');
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

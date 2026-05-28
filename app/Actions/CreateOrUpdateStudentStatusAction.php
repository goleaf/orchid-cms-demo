<?php

namespace App\Actions;

use App\Models\StudentStatus;
use Illuminate\Validation\ValidationException;

class CreateOrUpdateStudentStatusAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(StudentStatus|int|string|null $status, array $data): StudentStatus
    {
        $model = $status instanceof StudentStatus
            ? $status
            : (filled($status) ? StudentStatus::query()->findOrFail($status) : new StudentStatus);

        $this->assertSaveAllowed($model, $data);

        $model->fill($data);
        $model->save();

        if ((bool) $model->is_default) {
            StudentStatus::query()
                ->whereKeyNot($model->getKey())
                ->update(['is_default' => false]);
        }

        return $model->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function assertSaveAllowed(StudentStatus $status, array $data): void
    {
        if ($status->exists && (bool) $status->is_system && array_key_exists('code', $data) && (string) $data['code'] !== (string) $status->code) {
            throw ValidationException::withMessages([
                'status.code' => tkey('students.validation.system_status_code_locked'),
            ]);
        }

        if ((bool) ($data['is_default'] ?? false) && ! (bool) ($data['is_active'] ?? false)) {
            throw ValidationException::withMessages([
                'status.is_default' => tkey('students.validation.default_status_inactive'),
            ]);
        }
    }
}

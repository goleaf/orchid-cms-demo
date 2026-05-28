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

        if ($status->exists && (bool) $status->is_default && ! (bool) ($data['is_default'] ?? false) && ! $this->anotherDefaultStatusExists($status)) {
            throw ValidationException::withMessages([
                'status.is_default' => tkey('students.validation.default_status_required'),
            ]);
        }

        if ($status->exists && (bool) $status->is_system && ((bool) $status->is_final || (bool) $status->is_archived)) {
            if (array_key_exists('is_final', $data) && ! (bool) $data['is_final']) {
                throw ValidationException::withMessages([
                    'status.is_final' => tkey('students.validation.final_status_locked'),
                ]);
            }

            if (array_key_exists('is_archived', $data) && ! (bool) $data['is_archived'] && (bool) $status->is_archived) {
                throw ValidationException::withMessages([
                    'status.is_archived' => tkey('students.validation.final_status_locked'),
                ]);
            }

            if (array_key_exists('is_active', $data) && ! (bool) $data['is_active']) {
                throw ValidationException::withMessages([
                    'status.is_active' => tkey('students.validation.final_status_locked'),
                ]);
            }
        }
    }

    private function anotherDefaultStatusExists(StudentStatus $status): bool
    {
        return StudentStatus::query()
            ->whereKeyNot($status->getKey())
            ->where('is_default', true)
            ->exists();
    }
}

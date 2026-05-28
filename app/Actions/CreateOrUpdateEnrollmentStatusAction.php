<?php

namespace App\Actions;

use App\Models\EnrollmentStatus;
use Illuminate\Validation\ValidationException;

class CreateOrUpdateEnrollmentStatusAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(EnrollmentStatus|int|string|null $status, array $data): EnrollmentStatus
    {
        $model = $status instanceof EnrollmentStatus
            ? $status
            : (filled($status) ? EnrollmentStatus::query()->findOrFail($status) : new EnrollmentStatus);

        $this->assertSaveAllowed($model, $data);

        $model->fill($data);
        $model->save();

        if ((bool) $model->is_default) {
            EnrollmentStatus::query()
                ->whereKeyNot($model->getKey())
                ->update(['is_default' => false]);
        }

        return $model->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function assertSaveAllowed(EnrollmentStatus $status, array $data): void
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

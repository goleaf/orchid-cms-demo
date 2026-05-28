<?php

namespace App\Actions;

use App\Models\TrainingGroupStatus;
use Illuminate\Validation\ValidationException;

class CreateOrUpdateTrainingGroupStatusAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(TrainingGroupStatus|int|string|null $status, array $data): TrainingGroupStatus
    {
        $model = $status instanceof TrainingGroupStatus
            ? $status
            : (filled($status) ? TrainingGroupStatus::query()->findOrFail($status) : new TrainingGroupStatus);

        $this->assertSaveAllowed($model, $data);

        $model->fill($data);
        $model->save();

        if ((bool) $model->is_default) {
            TrainingGroupStatus::query()
                ->whereKeyNot($model->getKey())
                ->update(['is_default' => false]);
        }

        return $model->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function assertSaveAllowed(TrainingGroupStatus $status, array $data): void
    {
        if ($status->exists && (bool) $status->is_system && array_key_exists('code', $data) && (string) $data['code'] !== (string) $status->code) {
            throw ValidationException::withMessages([
                'status.code' => tkey('education.validation.system_status_code_locked'),
            ]);
        }

        if ((bool) ($data['is_default'] ?? false) && ! (bool) ($data['is_active'] ?? false)) {
            throw ValidationException::withMessages([
                'status.is_default' => tkey('education.validation.default_status_inactive'),
            ]);
        }
    }
}

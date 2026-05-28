<?php

namespace App\Actions;

use App\Models\TrainingGroupStatus;
use Illuminate\Validation\ValidationException;

class DeleteTrainingGroupStatusAction
{
    public function handle(TrainingGroupStatus|int|string $status): void
    {
        $model = $status instanceof TrainingGroupStatus
            ? $status
            : TrainingGroupStatus::query()->findOrFail($status);

        if ((bool) $model->is_system || (bool) $model->is_default || $model->groups()->exists()) {
            throw ValidationException::withMessages([
                'record' => tkey('education.validation.dictionary_item_cannot_be_deleted'),
            ]);
        }

        $model->delete();
    }
}

<?php

namespace App\Actions;

use App\Models\EnrollmentStatus;
use App\Rules\DictionaryItemCanBeDeletedRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DeleteEnrollmentStatusAction
{
    public function handle(EnrollmentStatus|int|string $status): void
    {
        $model = $status instanceof EnrollmentStatus
            ? $status
            : EnrollmentStatus::query()->findOrFail($status);

        $validator = Validator::make(
            ['record' => $model->getKey()],
            ['record' => [new DictionaryItemCanBeDeletedRule('enrollment-statuses')]],
        );

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $model->delete();
    }
}

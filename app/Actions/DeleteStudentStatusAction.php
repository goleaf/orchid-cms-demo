<?php

namespace App\Actions;

use App\Models\StudentStatus;
use App\Rules\DictionaryItemCanBeDeletedRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DeleteStudentStatusAction
{
    public function handle(StudentStatus|int|string $status): void
    {
        $model = $status instanceof StudentStatus
            ? $status
            : StudentStatus::query()->findOrFail($status);

        $validator = Validator::make(
            ['record' => $model->getKey()],
            ['record' => [new DictionaryItemCanBeDeletedRule('student-statuses')]],
        );

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $model->delete();
    }
}

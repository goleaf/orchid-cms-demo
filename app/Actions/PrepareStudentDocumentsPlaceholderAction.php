<?php

namespace App\Actions;

use App\Models\Student;
use App\Models\User;

class PrepareStudentDocumentsPlaceholderAction
{
    public function handle(Student $student, ?User $user = null): Student
    {
        $summary = [
            'identity_document' => 'missing',
            'medical_certificate' => 'missing',
            'photo' => 'missing',
            'contract' => 'not_created',
        ];

        $student->forceFill([
            'documents_summary' => $summary,
            'updated_by_id' => $user?->id ?? $student->updated_by_id,
        ])->save();

        app(RecordStudentActivityAction::class)->handle(
            $student->refresh(),
            $user,
            'document_placeholder_created',
            tkey('students.activities.titles.document_placeholder_created'),
            null,
            null,
            null,
            $summary,
        );

        return $student->refresh();
    }
}

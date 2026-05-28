<?php

namespace App\Support\Students;

use App\Models\EnrollmentStatus;
use App\Models\StudentStatus;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StudentDictionaryRegistry
{
    /**
     * @return array<string, array{
     *     model: class-string<Model>,
     *     key_column: string,
     *     usage_relation: string,
     *     unavailable_key: string,
     *     system_record_key: string,
     *     system_code_key: string,
     *     default_required_key: string,
     *     used_key: string
     * }>
     */
    public static function definitions(): array
    {
        return [
            'student-statuses' => [
                'model' => StudentStatus::class,
                'key_column' => 'code',
                'usage_relation' => 'students',
                'unavailable_key' => 'students.validation.dictionary_record_unavailable',
                'system_record_key' => 'students.validation.dictionary_system_record_locked',
                'system_code_key' => 'students.validation.system_status_code_locked',
                'default_required_key' => 'students.validation.default_status_required',
                'used_key' => 'students.validation.dictionary_item_in_use',
            ],
            'enrollment-statuses' => [
                'model' => EnrollmentStatus::class,
                'key_column' => 'code',
                'usage_relation' => 'enrollments',
                'unavailable_key' => 'students.validation.dictionary_record_unavailable',
                'system_record_key' => 'students.validation.dictionary_system_record_locked',
                'system_code_key' => 'students.validation.system_status_code_locked',
                'default_required_key' => 'students.validation.default_status_required',
                'used_key' => 'students.validation.dictionary_item_in_use',
            ],
        ];
    }

    /**
     * @return array{
     *     model: class-string<Model>,
     *     key_column: string,
     *     usage_relation: string,
     *     unavailable_key: string,
     *     system_record_key: string,
     *     system_code_key: string,
     *     default_required_key: string,
     *     used_key: string
     * }
     */
    public static function definition(string $dictionary): array
    {
        return static::definitions()[$dictionary]
            ?? throw new NotFoundHttpException('Unknown student dictionary.');
    }
}

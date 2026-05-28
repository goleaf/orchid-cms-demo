<?php

namespace App\Actions;

use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class FindMatchingStudentsAction
{
    /**
     * @param  array<string, mixed>  $data
     * @return Collection<int, array{student: Student, reason: string}>
     */
    public function handle(array $data, ?Student $ignore = null, bool $withTrashed = false): Collection
    {
        $matches = collect();
        $seen = [];
        $normalizedPhone = app(NormalizeStudentPhoneAction::class)->handle($data['phone'] ?? $data['normalized_phone'] ?? null);
        $email = filled($data['email'] ?? null) ? (string) $data['email'] : null;
        $personalCode = filled($data['personal_code'] ?? null) ? (string) $data['personal_code'] : null;
        $fullName = filled($data['full_name'] ?? null) ? (string) $data['full_name'] : null;

        foreach ([
            'phone' => fn (Builder $query): Builder => $query->where('normalized_phone', $normalizedPhone),
            'email' => fn (Builder $query): Builder => $query->where('email', 'like', $email),
            'personal_code' => fn (Builder $query): Builder => $query->where(function (Builder $query) use ($personalCode): void {
                $query->where('personal_code', $personalCode)
                    ->orWhere('national_id', $personalCode);
            }),
            'name' => fn (Builder $query): Builder => $query->where('full_name', 'like', $fullName),
        ] as $reason => $scope) {
            if (! $this->hasSearchValue($reason, $normalizedPhone, $email, $personalCode, $fullName)) {
                continue;
            }

            $query = Student::query()
                ->select([
                    'id',
                    'uuid',
                    'student_number',
                    'full_name',
                    'first_name',
                    'middle_name',
                    'last_name',
                    'phone',
                    'normalized_phone',
                    'email',
                    'personal_code',
                    'national_id',
                    'status',
                    'status_id',
                    'deleted_at',
                ])
                ->orderBy('id')
                ->limit(10);

            if ($withTrashed) {
                $query->withTrashed();
            }

            if ($ignore !== null) {
                $query->whereKeyNot($ignore->getKey());
            }

            foreach ($scope($query)->get() as $student) {
                if (isset($seen[$student->id])) {
                    continue;
                }

                $seen[$student->id] = true;
                $matches->push([
                    'student' => $student,
                    'reason' => $reason,
                ]);
            }
        }

        return $matches;
    }

    private function hasSearchValue(string $reason, ?string $phone, ?string $email, ?string $personalCode, ?string $fullName): bool
    {
        return match ($reason) {
            'phone' => filled($phone),
            'email' => filled($email),
            'personal_code' => filled($personalCode),
            'name' => filled($fullName),
            default => false,
        };
    }
}

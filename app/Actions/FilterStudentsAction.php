<?php

namespace App\Actions;

use App\Enums\EnrollmentStatus;
use App\Enums\StudentStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class FilterStudentsAction
{
    /**
     * @param  Builder<\App\Models\Student>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<\App\Models\Student>
     */
    public function handle(Builder $query, array $filters, ?User $user = null): Builder
    {
        return $query
            ->when($this->filled($filters, 'search'), fn (Builder $query): Builder => $this->applySearch($query, $this->value($filters, 'search')))
            ->when($this->filled($filters, 'status'), fn (Builder $query): Builder => $query->byStatus($this->value($filters, 'status')))
            ->when($this->filled($filters, 'enrollment_status'), fn (Builder $query): Builder => $query->whereHas(
                'enrollments',
                fn (Builder $enrollment): Builder => $enrollment->byStatus($this->value($filters, 'enrollment_status'))
            ))
            ->when($this->filled($filters, 'manager_id'), fn (Builder $query): Builder => $query->byManager($this->value($filters, 'manager_id')))
            ->when($this->filled($filters, 'administrator_id'), fn (Builder $query): Builder => $query->where('administrator_id', $this->value($filters, 'administrator_id')))
            ->when($this->filled($filters, 'course_id', 'training_program_id'), fn (Builder $query): Builder => $query->whereHas(
                'enrollments',
                fn (Builder $enrollment): Builder => $enrollment->byCourse($this->value($filters, 'course_id', 'training_program_id'))
            ))
            ->when($this->filled($filters, 'course_category_id'), fn (Builder $query): Builder => $query->whereHas(
                'enrollments',
                fn (Builder $enrollment): Builder => $enrollment->where('course_category_id', $this->value($filters, 'course_category_id'))
            ))
            ->when($this->filled($filters, 'branch_id'), fn (Builder $query): Builder => $query->where(function (Builder $query) use ($filters): void {
                $branchId = $this->value($filters, 'branch_id');

                $query->where('branch_id', $branchId)
                    ->orWhereHas('enrollments', fn (Builder $enrollment): Builder => $enrollment->byBranch($branchId));
            }))
            ->when($this->filled($filters, 'training_group_id'), fn (Builder $query): Builder => $query->whereHas(
                'enrollments',
                fn (Builder $enrollment): Builder => $enrollment->byTrainingGroup($this->value($filters, 'training_group_id'))
            ))
            ->when($this->filled($filters, 'created_from'), fn (Builder $query): Builder => $query->whereDate('created_at', '>=', $this->value($filters, 'created_from')))
            ->when($this->filled($filters, 'created_to'), fn (Builder $query): Builder => $query->whereDate('created_at', '<=', $this->value($filters, 'created_to')))
            ->when($this->flag($filters, 'only_active'), fn (Builder $query): Builder => $query->where('status', StudentStatus::Active->value))
            ->when($this->flag($filters, 'only_archived'), fn (Builder $query): Builder => $query->archived())
            ->when($this->flag($filters, 'only_blocked'), fn (Builder $query): Builder => $query->blocked())
            ->when($this->flag($filters, 'only_with_active_enrollment'), fn (Builder $query): Builder => $query->withActiveEnrollment())
            ->when($this->flag($filters, 'only_without_active_enrollment'), fn (Builder $query): Builder => $query->withoutActiveEnrollment())
            ->when($this->flag($filters, 'only_waiting_documents'), fn (Builder $query): Builder => $query->whereHas('enrollments', fn (Builder $enrollment): Builder => $enrollment->waitingDocuments()))
            ->when($this->flag($filters, 'only_waiting_payment'), fn (Builder $query): Builder => $query->whereHas('enrollments', fn (Builder $enrollment): Builder => $enrollment->waitingPayment()))
            ->when($this->flag($filters, 'only_waiting_start'), fn (Builder $query): Builder => $query->whereHas('enrollments', fn (Builder $enrollment): Builder => $enrollment->waitingStart()))
            ->when($this->flag($filters, 'only_without_group'), fn (Builder $query): Builder => $query->whereHas('activeEnrollments', fn (Builder $enrollment): Builder => $enrollment->whereNull('training_group_id')))
            ->when($this->filled($filters, 'segment'), fn (Builder $query): Builder => $this->applySegment($query, $this->value($filters, 'segment'), $user));
    }

    private function applySearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $query) use ($search): void {
            $query
                ->search($search)
                ->orWhereHas('enrollments', fn (Builder $enrollment): Builder => $enrollment->search($search));
        });
    }

    private function applySegment(Builder $query, string $segment, ?User $user): Builder
    {
        return match ($segment) {
            'all' => $query,
            'active' => $query->where('status', StudentStatus::Active->value),
            'new' => $this->applyNewSegment($query),
            'waiting_documents' => $query->whereHas('enrollments', fn (Builder $enrollment): Builder => $enrollment->waitingDocuments()),
            'waiting_payment' => $query->whereHas('enrollments', fn (Builder $enrollment): Builder => $enrollment->waitingPayment()),
            'waiting_start' => $query->whereHas('enrollments', fn (Builder $enrollment): Builder => $enrollment->waitingStart()),
            'without_group' => $query->whereHas('activeEnrollments', fn (Builder $enrollment): Builder => $enrollment->whereNull('training_group_id')),
            'in_training' => $query->whereHas('enrollments', fn (Builder $enrollment): Builder => $enrollment->whereIn('status', [
                EnrollmentStatus::Active->value,
                EnrollmentStatus::Theory->value,
                EnrollmentStatus::Practice->value,
            ])),
            'paused' => $query->whereHas('enrollments', fn (Builder $enrollment): Builder => $enrollment->where('status', EnrollmentStatus::Paused->value)),
            'completed' => $query->whereHas('enrollments', fn (Builder $enrollment): Builder => $enrollment->completed()),
            'archived' => $query->archived(),
            'my' => $user === null ? $query : $query->byManager($user->id),
            default => $query,
        };
    }

    private function applyNewSegment(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query
                ->where('created_at', '>=', now()->subDays(7))
                ->orWhereHas('enrollments', fn (Builder $enrollment): Builder => $enrollment->whereIn('status', [
                    EnrollmentStatus::WaitingDocuments->value,
                    EnrollmentStatus::WaitingStart->value,
                ]));
        });
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function filled(array $filters, string ...$keys): bool
    {
        return filled($this->value($filters, ...$keys));
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function flag(array $filters, string ...$keys): bool
    {
        return in_array($this->value($filters, ...$keys), ['1', 1, true, 'true'], true);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function value(array $filters, string ...$keys): mixed
    {
        foreach ($keys as $key) {
            if (filled($filters[$key] ?? null)) {
                return is_string($filters[$key]) ? trim($filters[$key]) : $filters[$key];
            }
        }

        return '';
    }
}

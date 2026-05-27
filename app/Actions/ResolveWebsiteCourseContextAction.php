<?php

namespace App\Actions;

use App\Models\Branch;
use App\Models\Course;
use App\Models\TrainingGroup;
use Illuminate\Validation\ValidationException;

class ResolveWebsiteCourseContextAction
{
    /**
     * @param  array<string, mixed>  $data
     * @return array{course_id: int|null, course_category_id: int|null, branch_id: int|null, training_group_id: int|null}
     */
    public function handle(array $data, bool $public = true): array
    {
        $courseId = filled($data['course_id'] ?? null)
            ? (int) $data['course_id']
            : (filled($data['training_program_id'] ?? null) ? (int) $data['training_program_id'] : null);
        $categoryId = filled($data['course_category_id'] ?? null) ? (int) $data['course_category_id'] : null;
        $branchId = filled($data['branch_id'] ?? null) ? (int) $data['branch_id'] : null;
        $groupId = filled($data['training_group_id'] ?? null) ? (int) $data['training_group_id'] : null;

        $group = null;

        if ($groupId !== null) {
            $group = TrainingGroup::query()
                ->with(['course:id,course_category_id,is_active,is_visible_on_site', 'branch:id,is_active,is_visible_on_site'])
                ->whereKey($groupId)
                ->first();

            if ($group === null || ($public && ! $group->acceptsPublicApplications())) {
                throw ValidationException::withMessages([
                    'training_group_id' => tkey('website.validation.invalid_public_group'),
                ]);
            }

            if ($public && $group->is_full && ! filter_var($data['allow_overbooking'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                throw ValidationException::withMessages([
                    'training_group_id' => tkey('website.validation.group_is_full'),
                ]);
            }

            $courseId ??= $group->training_program_id;
            $branchId ??= $group->branch_id;
            $categoryId ??= $group->course_category_id ?: $group->course?->course_category_id;
        }

        if ($courseId !== null) {
            $course = Course::query()
                ->select(['id', 'course_category_id', 'is_active', 'is_visible_on_site'])
                ->whereKey($courseId)
                ->first();

            if ($course === null || ($public && (! $course->is_active || ! $course->is_visible_on_site))) {
                throw ValidationException::withMessages([
                    'course_id' => tkey('website.validation.invalid_public_course'),
                ]);
            }

            $categoryId ??= $course->course_category_id;
        }

        if ($branchId !== null) {
            $branch = Branch::query()
                ->select(['id', 'is_active', 'is_visible_on_site'])
                ->whereKey($branchId)
                ->first();

            if ($branch === null || ($public && (! $branch->is_active || ! $branch->is_visible_on_site))) {
                throw ValidationException::withMessages([
                    'branch_id' => tkey('website.validation.invalid_public_branch'),
                ]);
            }
        }

        return [
            'course_id' => $courseId,
            'course_category_id' => $categoryId,
            'branch_id' => $branchId,
            'training_group_id' => $groupId,
        ];
    }
}

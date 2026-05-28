<?php

namespace App\Http\Requests;

use App\Enums\GroupStatus;
use App\Http\Requests\Concerns\HasWebsiteValidationAttributes;
use App\Models\Branch;
use App\Models\CourseCategory;
use App\Models\Instructor;
use App\Models\LearningProgram;
use App\Models\TrainingGroup;
use App\Models\TrainingGroupStatus;
use App\Models\TrainingProgram;
use App\Models\User;
use App\Rules\ActiveTrainingGroupStatusRule;
use App\Rules\ValidTrainingGroupStatusRule;
use App\Services\TranslatableContentManager;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TrainingGroupRequest extends FormRequest
{
    use HasWebsiteValidationAttributes;

    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess([
            'platform.operations.groups',
            'website.manage_groups',
            'education.groups.create',
            'education.groups.update',
        ]) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $groupId = $this->input('group.id');

        return [
            'group.id' => ['nullable', 'integer', Rule::exists(TrainingGroup::class, 'id')],
            'group.group_number' => ['nullable', 'string', 'max:120', Rule::unique('training_groups', 'group_number')->ignore($groupId)],
            'group.branch_id' => ['nullable', 'integer', Rule::exists(Branch::class, 'id')],
            'group.course_id' => ['nullable', 'integer', Rule::exists(TrainingProgram::class, 'id')],
            'group.training_program_id' => ['nullable', 'integer', Rule::exists(TrainingProgram::class, 'id')],
            'group.course_category_id' => ['nullable', 'integer', Rule::exists(CourseCategory::class, 'id')],
            'group.instructor_id' => ['nullable', 'integer', Rule::exists(Instructor::class, 'id')],
            'group.learning_program_id' => ['nullable', 'integer', Rule::exists(LearningProgram::class, 'id')],
            'group.manager_id' => ['nullable', 'integer', Rule::exists(User::class, 'id')],
            'group.administrator_id' => ['nullable', 'integer', Rule::exists(User::class, 'id')],
            'group.teacher_id' => ['nullable', 'integer', Rule::exists(User::class, 'id')],
            'group.status_id' => ['nullable', 'integer', Rule::exists(TrainingGroupStatus::class, 'id'), new ActiveTrainingGroupStatusRule],
            'group.code' => [
                'required',
                'string',
                'max:80',
                Rule::unique('training_groups', 'code')->ignore($groupId),
            ],
            'group.status' => ['nullable', Rule::enum(GroupStatus::class), new ValidTrainingGroupStatusRule],
            'group.capacity' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'group.capacity_total' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'group.capacity_reserved' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'group.capacity_taken' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'group.capacity_waitlist' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'group.places_taken' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'group.starts_on' => ['nullable', 'date'],
            'group.ends_on' => ['nullable', 'date', 'after_or_equal:group.starts_on'],
            'group.start_date' => ['nullable', 'date'],
            'group.planned_end_date' => ['nullable', 'date', 'after_or_equal:group.start_date'],
            'group.actual_end_date' => ['nullable', 'date', 'after_or_equal:group.start_date'],
            'group.enrollment_closes_on' => ['nullable', 'date', 'before_or_equal:group.starts_on'],
            'group.meeting_days' => ['nullable', 'string', 'max:255'],
            'group.meeting_time' => ['nullable', 'date_format:H:i'],
            'group.end_time' => ['nullable', 'date_format:H:i', 'after:group.meeting_time'],
            'group.classroom' => ['nullable', 'string', 'max:120'],
            'group.learning_notes' => ['nullable', 'string', 'max:5000'],
            'group.schedule_notes' => ['nullable', 'string', 'max:5000'],
            'group.timezone' => ['nullable', 'string', 'max:64'],
            'group.default_lesson_duration_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'group.is_visible_on_site' => ['nullable', 'boolean'],
            'group.is_featured' => ['nullable', 'boolean'],
            'group.is_accepting_applications' => ['nullable', 'boolean'],
            'group.notes' => ['nullable', 'string', 'max:5000'],
            'group.internal_notes' => ['nullable', 'string', 'max:5000'],
            ...app(TranslatableContentManager::class)->validationRules(['name', 'description', 'public_description', 'schedule_summary']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function groupData(): array
    {
        $validated = $this->validated();
        $group = $validated['group'];
        $translations = app(TranslatableContentManager::class)->extract($this, ['name', 'description', 'public_description', 'schedule_summary']);
        $courseId = $group['course_id'] ?? $group['training_program_id'] ?? null;
        $trainingProgramId = $group['training_program_id'] ?? $group['course_id'] ?? null;
        $statusCode = $group['status'] ?? null;

        if (blank($statusCode) && filled($group['status_id'] ?? null)) {
            $statusCode = TrainingGroupStatus::query()
                ->whereKey((int) $group['status_id'])
                ->value('code');
        }

        $capacity = (int) ($group['capacity_total'] ?? $group['capacity'] ?? 12);
        $taken = (int) ($group['capacity_taken'] ?? $group['places_taken'] ?? 0);

        $data = [
            'group_number' => $group['group_number'] ?? null,
            'branch_id' => $group['branch_id'] ?? null,
            'course_id' => $courseId,
            'training_program_id' => $trainingProgramId,
            'course_category_id' => $group['course_category_id'] ?? null,
            'instructor_id' => $group['instructor_id'] ?? null,
            'learning_program_id' => $group['learning_program_id'] ?? null,
            'manager_id' => $group['manager_id'] ?? null,
            'administrator_id' => $group['administrator_id'] ?? null,
            'teacher_id' => $group['teacher_id'] ?? null,
            'status_id' => $group['status_id'] ?? (filled($statusCode) ? TrainingGroupStatus::query()->where('code', $statusCode)->value('id') : null),
            'code' => $group['code'],
            'status' => $statusCode,
            'capacity' => $capacity,
            'capacity_total' => $capacity,
            'capacity_reserved' => (int) ($group['capacity_reserved'] ?? 0),
            'capacity_taken' => $taken,
            'capacity_waitlist' => (int) ($group['capacity_waitlist'] ?? 0),
            'places_taken' => $taken,
            'starts_on' => $group['starts_on'] ?? null,
            'ends_on' => $group['ends_on'] ?? null,
            'start_date' => $group['start_date'] ?? $group['starts_on'] ?? null,
            'planned_end_date' => $group['planned_end_date'] ?? $group['ends_on'] ?? null,
            'actual_end_date' => $group['actual_end_date'] ?? null,
            'enrollment_closes_on' => $group['enrollment_closes_on'] ?? null,
            'meeting_days' => $this->days($group['meeting_days'] ?? null),
            'meeting_time' => $group['meeting_time'] ?? null,
            'end_time' => $group['end_time'] ?? null,
            'classroom' => $group['classroom'] ?? null,
            'learning_notes' => $group['learning_notes'] ?? null,
            'schedule_notes' => $group['schedule_notes'] ?? null,
            'timezone' => $group['timezone'] ?? null,
            'default_lesson_duration_minutes' => $group['default_lesson_duration_minutes'] ?? null,
            'is_visible_on_site' => (bool) ($group['is_visible_on_site'] ?? false),
            'is_featured' => (bool) ($group['is_featured'] ?? false),
            'is_accepting_applications' => (bool) ($group['is_accepting_applications'] ?? false),
            'notes' => $group['notes'] ?? null,
            'internal_notes' => $group['internal_notes'] ?? null,
            ...$translations,
            'name' => $this->fallbackScalar($translations, 'name', tkey('website.groups.fields.name')),
        ];

        if (filled($this->input('group.id'))) {
            foreach ([
                'group_number',
                'capacity_reserved',
                'capacity_taken',
                'capacity_waitlist',
                'places_taken',
                'actual_end_date',
                'is_featured',
                'is_accepting_applications',
                'notes',
                'internal_notes',
            ] as $field) {
                if (! $this->has('group.'.$field)) {
                    unset($data[$field]);
                }
            }
        }

        return $data;
    }

    /**
     * @return array<int, string>|null
     */
    private function days(?string $value): ?array
    {
        if (! filled($value)) {
            return null;
        }

        return str($value)
            ->explode(',')
            ->map(fn (string $day): string => trim($day))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  array<string, array<string, mixed>>  $translations
     */
    private function fallbackScalar(array $translations, string $field, ?string $fallback = null): ?string
    {
        $value = app(TranslatableContentManager::class)
            ->defaultValue($translations[$field.'_translations'] ?? []);

        return filled($value) ? (string) $value : $fallback;
    }
}

<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\HasWebsiteValidationAttributes;
use App\Enums\GroupStatus;
use App\Models\Branch;
use App\Models\Instructor;
use App\Models\TrainingGroup;
use App\Models\TrainingGroupStatus;
use App\Models\TrainingProgram;
use App\Services\TranslatableContentManager;
use App\Rules\ActiveTrainingGroupStatusRule;
use App\Rules\ValidTrainingGroupStatusRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TrainingGroupRequest extends FormRequest
{
    use HasWebsiteValidationAttributes;

    public function authorize(): bool
    {
        return $this->user()?->hasAnyAccess(['platform.operations.groups', 'website.manage_groups']) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $groupId = $this->input('group.id');

        return [
            'group.id' => ['nullable', 'integer', Rule::exists(TrainingGroup::class, 'id')],
            'group.branch_id' => ['required', 'integer', Rule::exists(Branch::class, 'id')],
            'group.training_program_id' => ['required', 'integer', Rule::exists(TrainingProgram::class, 'id')],
            'group.instructor_id' => ['nullable', 'integer', Rule::exists(Instructor::class, 'id')],
            'group.status_id' => ['nullable', 'integer', Rule::exists(TrainingGroupStatus::class, 'id'), new ActiveTrainingGroupStatusRule],
            'group.code' => [
                'required',
                'string',
                'max:80',
                Rule::unique('training_groups', 'code')->ignore($groupId),
            ],
            'group.status' => ['required', Rule::enum(GroupStatus::class), new ValidTrainingGroupStatusRule],
            'group.capacity' => ['required', 'integer', 'min:1', 'max:1000'],
            'group.places_taken' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'group.starts_on' => ['nullable', 'date'],
            'group.ends_on' => ['nullable', 'date', 'after_or_equal:group.starts_on'],
            'group.enrollment_closes_on' => ['nullable', 'date', 'before_or_equal:group.starts_on'],
            'group.meeting_days' => ['nullable', 'string', 'max:255'],
            'group.meeting_time' => ['nullable', 'date_format:H:i'],
            'group.end_time' => ['nullable', 'date_format:H:i', 'after:group.meeting_time'],
            'group.classroom' => ['nullable', 'string', 'max:120'],
            'group.learning_notes' => ['nullable', 'string', 'max:5000'],
            'group.schedule_notes' => ['nullable', 'string', 'max:5000'],
            'group.is_visible_on_site' => ['nullable', 'boolean'],
            ...app(TranslatableContentManager::class)->validationRules(['name']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function groupData(): array
    {
        $validated = $this->validated();
        $group = $validated['group'];
        $translations = app(TranslatableContentManager::class)->extract($this, ['name']);

        return [
            'branch_id' => $group['branch_id'],
            'training_program_id' => $group['training_program_id'],
            'instructor_id' => $group['instructor_id'] ?? null,
            'status_id' => $group['status_id'] ?? TrainingGroupStatus::query()->where('code', $group['status'])->value('id'),
            'code' => $group['code'],
            'status' => $group['status'],
            'capacity' => (int) $group['capacity'],
            'places_taken' => (int) ($group['places_taken'] ?? 0),
            'starts_on' => $group['starts_on'] ?? null,
            'ends_on' => $group['ends_on'] ?? null,
            'enrollment_closes_on' => $group['enrollment_closes_on'] ?? null,
            'meeting_days' => $this->days($group['meeting_days'] ?? null),
            'meeting_time' => $group['meeting_time'] ?? null,
            'end_time' => $group['end_time'] ?? null,
            'classroom' => $group['classroom'] ?? null,
            'learning_notes' => $group['learning_notes'] ?? null,
            'schedule_notes' => $group['schedule_notes'] ?? null,
            'is_visible_on_site' => (bool) ($group['is_visible_on_site'] ?? false),
            ...$translations,
            'name' => $this->fallbackScalar($translations, 'name', tkey('website.groups.fields.name')),
        ];
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

<?php

namespace App\Rules;

use App\Models\TrainingGroupSchedulePattern;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class DuplicateSchedulePatternRule implements DataAwareRule, ValidationRule
{
    /**
     * @var array<string, mixed>
     */
    private array $data = [];

    public function __construct(private readonly ?int $ignoreId = null) {}

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $groupId = data_get($this->data, 'pattern.training_group_id');
        $day = data_get($this->data, 'pattern.day_of_week');
        $type = data_get($this->data, 'pattern.type', data_get($this->data, 'pattern.lesson_type'));
        $start = data_get($this->data, 'pattern.start_time', data_get($this->data, 'pattern.starts_at'));
        $end = data_get($this->data, 'pattern.end_time', data_get($this->data, 'pattern.ends_at'));

        if (! filled($groupId) || ! filled($day) || ! filled($type) || ! filled($start) || ! filled($end)) {
            return;
        }

        $exists = TrainingGroupSchedulePattern::query()
            ->active()
            ->where('training_group_id', $groupId)
            ->where('day_of_week', $day)
            ->where(fn ($query) => $query->where('type', $type)->orWhere('lesson_type', $type))
            ->where(fn ($query) => $query->where('start_time', $start)->orWhere('starts_at', $start))
            ->where(fn ($query) => $query->where('end_time', $end)->orWhere('ends_at', $end))
            ->when($this->ignoreId !== null, fn ($query) => $query->whereKeyNot($this->ignoreId))
            ->exists();

        if ($exists) {
            $fail(tkey('education.groups.validation.duplicate_schedule_pattern'));
        }
    }
}

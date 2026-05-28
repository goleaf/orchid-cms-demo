<?php

namespace Database\Factories;

use App\Models\Instructor;
use App\Models\TrainingGroup;
use App\Models\TrainingGroupSchedulePattern;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TrainingGroupSchedulePattern>
 */
class TrainingGroupSchedulePatternFactory extends Factory
{
    protected $model = TrainingGroupSchedulePattern::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'training_group_id' => TrainingGroup::factory(),
            'title_translations' => $this->translations('Теория вечером', 'Evening theory', 'Vakaro teorija', 'Teoria wieczorem'),
            'type' => 'theory',
            'day_of_week' => $this->faker->numberBetween(1, 7),
            'start_time' => '18:00',
            'end_time' => '20:00',
            'starts_at' => '18:00',
            'ends_at' => '20:00',
            'lesson_type' => 'theory',
            'classroom' => 'Room '.$this->faker->numberBetween(1, 5),
            'classroom_id' => null,
            'location_translations' => null,
            'notes_translations' => null,
            'instructor_id' => Instructor::factory(),
            'is_active' => true,
            'sort_order' => 0,
            'created_by_id' => null,
            'updated_by_id' => null,
        ];
    }

    public function theory(): static
    {
        return $this->state(fn (): array => ['type' => 'theory', 'lesson_type' => 'theory']);
    }

    public function practice(): static
    {
        return $this->state(fn (): array => ['type' => 'practice', 'lesson_type' => 'practice']);
    }

    public function consultation(): static
    {
        return $this->state(fn (): array => ['type' => 'consultation', 'lesson_type' => 'consultation']);
    }

    public function examPreparation(): static
    {
        return $this->state(fn (): array => ['type' => 'exam_preparation', 'lesson_type' => 'exam_preparation']);
    }

    public function mondayEvening(): static
    {
        return $this->timeSlot(1, '18:00', '20:00');
    }

    public function wednesdayEvening(): static
    {
        return $this->timeSlot(3, '18:00', '20:00');
    }

    public function weekendMorning(): static
    {
        return $this->timeSlot(6, '10:00', '12:00');
    }

    public function weekdayMorning(): static
    {
        return $this->timeSlot(2, '09:00', '11:00');
    }

    public function active(): static
    {
        return $this->state(fn (): array => ['is_active' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }

    public function translated(): static
    {
        return $this->state(fn (): array => [
            'title_translations' => $this->translations('Теория группы', 'Group theory', 'Grupes teorija', 'Teoria grupowa'),
            'location_translations' => $this->translations('Учебный класс', 'Classroom', 'Klase', 'Sala lekcyjna'),
            'notes_translations' => $this->translations('Базовый шаблон расписания.', 'Basic schedule pattern.', 'Bazinis tvarkarascio sablonas.', 'Podstawowy wzorzec harmonogramu.'),
        ]);
    }

    private function timeSlot(int $day, string $start, string $end): static
    {
        return $this->state(fn (): array => [
            'day_of_week' => $day,
            'start_time' => $start,
            'end_time' => $end,
            'starts_at' => $start,
            'ends_at' => $end,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function translations(string $ru, string $en, string $lt, string $pl): array
    {
        return compact('ru', 'en', 'lt', 'pl');
    }
}

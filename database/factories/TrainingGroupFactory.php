<?php

namespace Database\Factories;

use App\Enums\GroupStatus;
use App\Models\Branch;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Instructor;
use App\Models\LearningProgram;
use App\Models\TrainingGroup;
use App\Models\TrainingGroupStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TrainingGroup>
 */
class TrainingGroupFactory extends Factory
{
    protected $model = TrainingGroup::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $capacity = $this->faker->numberBetween(8, 16);
        $placesTaken = $this->faker->numberBetween(0, 4);
        $startsOn = now()->addDays($this->faker->numberBetween(7, 30));
        $endsOn = (clone $startsOn)->addMonths($this->faker->numberBetween(3, 6));

        return [
            'uuid' => (string) Str::uuid(),
            'group_number' => strtoupper($this->faker->unique()->bothify('GROUP-####')),
            'branch_id' => Branch::factory(),
            'training_program_id' => Course::factory(),
            'course_category_id' => null,
            'instructor_id' => Instructor::factory(),
            'name' => 'Group '.$this->faker->unique()->bothify('B-##'),
            'name_translations' => $this->translations('Вечерняя группа', 'Evening group', 'Vakaro grupe', 'Grupa wieczorowa'),
            'description_translations' => null,
            'public_description_translations' => null,
            'schedule_summary_translations' => null,
            'code' => strtoupper($this->faker->unique()->bothify('GRP-####')),
            'status' => GroupStatus::Recruiting,
            'learning_program_id' => LearningProgram::factory(),
            'manager_id' => null,
            'administrator_id' => null,
            'teacher_id' => null,
            'capacity' => $capacity,
            'places_taken' => $placesTaken,
            'starts_on' => $startsOn,
            'ends_on' => $endsOn,
            'start_date' => $startsOn->toDateString(),
            'planned_end_date' => $endsOn->toDateString(),
            'actual_end_date' => null,
            'meeting_days' => ['monday', 'wednesday'],
            'meeting_time' => '18:00',
            'end_time' => '20:00',
            'classroom' => 'Room '.$this->faker->numberBetween(1, 6),
            'timezone' => 'Europe/Vilnius',
            'default_lesson_duration_minutes' => 120,
            'notes' => null,
            'internal_notes' => null,
            'is_visible_on_site' => true,
            'is_featured' => false,
            'is_accepting_applications' => true,
            'sort_order' => 0,
            'created_by_id' => null,
            'updated_by_id' => null,
        ];
    }

    public function publicVisible(): static
    {
        return $this->recruiting()->visibleOnSite()->acceptingApplications();
    }

    public function draft(): static
    {
        return $this->statusState('draft', GroupStatus::Planned, 'draft')
            ->hiddenFromSite()
            ->notAcceptingApplications();
    }

    public function recruiting(): static
    {
        return $this->statusState('recruiting', GroupStatus::Recruiting, 'recruiting');
    }

    public function almostFull(): static
    {
        return $this->statusState('almost_full', GroupStatus::AlmostFull, 'almostFull')
            ->almostFullCapacity();
    }

    public function full(): static
    {
        return $this->statusState('full', GroupStatus::AlmostFull, 'full')
            ->fullCapacity()
            ->notAcceptingApplications();
    }

    public function closed(): static
    {
        return $this->statusState('closed', GroupStatus::Closed, 'closed')
            ->notAcceptingApplications();
    }

    public function scheduled(): static
    {
        return $this->statusState('scheduled', GroupStatus::Planned, 'scheduled');
    }

    public function active(): static
    {
        return $this->statusState('active', GroupStatus::Active, 'active')
            ->started();
    }

    public function paused(): static
    {
        return $this->statusState('paused', GroupStatus::Active, 'paused');
    }

    public function completed(): static
    {
        return $this->statusState('completed', GroupStatus::Completed, 'completed')
            ->ended()
            ->notAcceptingApplications();
    }

    public function cancelled(): static
    {
        return $this->statusState('cancelled', GroupStatus::Cancelled, 'cancelled')
            ->ended()
            ->hiddenFromSite();
    }

    public function archived(): static
    {
        return $this->statusState('archived', GroupStatus::Closed, 'archived')
            ->hiddenFromSite();
    }

    public function visibleOnSite(): static
    {
        return $this->state(fn (): array => ['is_visible_on_site' => true]);
    }

    public function hiddenFromSite(): static
    {
        return $this->state(fn (): array => [
            'is_visible_on_site' => false,
            'is_accepting_applications' => false,
        ]);
    }

    public function acceptingApplications(): static
    {
        return $this->state(fn (): array => [
            'is_visible_on_site' => true,
            'is_accepting_applications' => true,
        ]);
    }

    public function notAcceptingApplications(): static
    {
        return $this->state(fn (): array => ['is_accepting_applications' => false]);
    }

    public function featured(): static
    {
        return $this->state(fn (): array => ['is_featured' => true]);
    }

    public function startingSoon(): static
    {
        return $this->state(fn (): array => [
            'starts_on' => now()->addDays(7),
            'ends_on' => now()->addMonths(4),
            'start_date' => now()->addDays(7)->toDateString(),
            'planned_end_date' => now()->addMonths(4)->toDateString(),
        ]);
    }

    public function started(): static
    {
        return $this->state(fn (): array => [
            'starts_on' => now()->subDays(14)->toDateString(),
            'start_date' => now()->subDays(14)->toDateString(),
            'ends_on' => now()->addMonths(3)->toDateString(),
            'planned_end_date' => now()->addMonths(3)->toDateString(),
        ]);
    }

    public function ended(): static
    {
        return $this->state(fn (): array => [
            'starts_on' => now()->subMonths(4)->toDateString(),
            'start_date' => now()->subMonths(4)->toDateString(),
            'ends_on' => now()->subDay()->toDateString(),
            'planned_end_date' => now()->subDay()->toDateString(),
            'actual_end_date' => now()->subDay()->toDateString(),
        ]);
    }

    public function evening(): static
    {
        return $this->state(fn (): array => [
            'meeting_days' => ['monday', 'wednesday'],
            'meeting_time' => '18:00',
            'end_time' => '20:00',
            'schedule_summary_translations' => $this->translations('Вечером два раза в неделю.', 'Evenings twice per week.', 'Vakarais du kartus per savaite.', 'Wieczorami dwa razy w tygodniu.'),
        ]);
    }

    public function morning(): static
    {
        return $this->state(fn (): array => [
            'meeting_days' => ['tuesday', 'thursday'],
            'meeting_time' => '09:00',
            'end_time' => '11:00',
            'schedule_summary_translations' => $this->translations('Утром два раза в неделю.', 'Mornings twice per week.', 'Rytais du kartus per savaite.', 'Poranki dwa razy w tygodniu.'),
        ]);
    }

    public function weekend(): static
    {
        return $this->state(fn (): array => [
            'meeting_days' => ['saturday'],
            'meeting_time' => '10:00',
            'end_time' => '14:00',
            'schedule_summary_translations' => $this->translations('Занятия по субботам.', 'Saturday lessons.', 'Pamokos sestadieniais.', 'Zajecia w soboty.'),
        ]);
    }

    public function withCapacity(int $total = 12, int $taken = 0): static
    {
        return $this->state(fn (): array => [
            'capacity' => $total,
            'capacity_total' => $total,
            'capacity_taken' => min($taken, $total),
            'capacity_reserved' => 0,
            'capacity_waitlist' => 0,
            'places_taken' => min($taken, $total),
        ]);
    }

    public function fullCapacity(): static
    {
        return $this->withCapacity(12, 12);
    }

    public function almostFullCapacity(): static
    {
        return $this->withCapacity(12, 10);
    }

    public function emptyCapacity(): static
    {
        return $this->withCapacity(12, 0);
    }

    public function withCourse(): static
    {
        return $this->state(fn (): array => [
            'training_program_id' => Course::factory(),
            'course_id' => null,
        ]);
    }

    public function withCourseCategory(): static
    {
        return $this->state(fn (): array => ['course_category_id' => CourseCategory::factory()]);
    }

    public function withBranch(): static
    {
        return $this->state(fn (): array => ['branch_id' => Branch::factory()]);
    }

    public function withLearningProgram(): static
    {
        return $this->state(fn (): array => ['learning_program_id' => LearningProgram::factory()]);
    }

    public function withManager(): static
    {
        return $this->state(fn (): array => ['manager_id' => User::factory()]);
    }

    public function withAdministrator(): static
    {
        return $this->state(fn (): array => ['administrator_id' => User::factory()]);
    }

    public function withTeacher(): static
    {
        return $this->state(fn (): array => ['teacher_id' => User::factory()]);
    }

    public function translated(): static
    {
        return $this->state(fn (): array => [
            'name' => 'Evening Category B Group',
            'name_translations' => $this->translations('Вечерняя группа категории B', 'Evening Category B group', 'Vakaro B kategorijos grupe', 'Wieczorowa grupa kategorii B'),
            'description_translations' => $this->translations('Открытая группа для записи с сайта.', 'Open group for website enrollment.', 'Atvira grupe registracijai svetaineje.', 'Otwarta grupa do zapisow ze strony.'),
            'schedule_summary_translations' => $this->translations('Занятия вечером два раза в неделю.', 'Evening classes twice per week.', 'Vakariniai uzsiemimai du kartus per savaite.', 'Zajecia wieczorne dwa razy w tygodniu.'),
        ]);
    }

    private function statusState(string $code, GroupStatus $legacyStatus, string $statusFactoryState): static
    {
        return $this->state(function () use ($code, $legacyStatus, $statusFactoryState): array {
            $statusId = TrainingGroupStatus::query()
                ->where('code', $code)
                ->value('id');

            return [
                'status' => $legacyStatus,
                'status_id' => $statusId ?: TrainingGroupStatus::factory()->{$statusFactoryState}(),
            ];
        });
    }

    /**
     * @return array<string, string>
     */
    private function translations(string $ru, ?string $en = null, ?string $lt = null, ?string $pl = null): array
    {
        return [
            'ru' => $ru,
            'en' => $en ?? $ru,
            'lt' => $lt ?? $en ?? $ru,
            'pl' => $pl ?? $en ?? $ru,
        ];
    }
}

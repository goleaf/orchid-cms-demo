<?php

namespace Database\Seeders;

use App\Enums\GroupStatus;
use App\Models\Branch;
use App\Models\LearningProgram;
use App\Models\TrainingGroup;
use App\Models\TrainingGroupSchedulePattern;
use App\Models\TrainingGroupStatus;
use Illuminate\Database\Seeder;

class DemoTrainingGroupSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            TrainingGroupStatusSeeder::class,
            LearningProgramModuleSeeder::class,
        ]);

        $branch = $this->branch();

        foreach ($this->groups() as $sortOrder => $definition) {
            $program = LearningProgram::query()->where('code', $definition['program'])->first();

            if ($program === null || $program->course_id === null) {
                continue;
            }

            $status = TrainingGroupStatus::query()->where('code', $definition['status'])->first();
            $factory = TrainingGroup::factory()
                ->{$definition['factory']}()
                ->{$definition['group_schedule']}()
                ->withCapacity($definition['capacity'], $definition['taken'])
                ->translated();

            $group = $factory->make([
                'group_number' => $definition['group_number'],
                'code' => $definition['code'],
                'name' => $definition['name']['en'],
                'name_translations' => $definition['name'],
                'training_program_id' => $program->course_id,
                'course_id' => $program->course_id,
                'course_category_id' => $program->course_category_id,
                'branch_id' => $branch->id,
                'learning_program_id' => $program->id,
                'status_id' => $status?->id,
                'status' => $this->legacyStatus($definition['status']),
                'is_visible_on_site' => $definition['visible'],
                'is_accepting_applications' => $definition['accepting'],
                'sort_order' => ($sortOrder + 1) * 10,
            ]);

            $attributes = $group->only($group->getFillable());
            unset($attributes['code']);

            $group = TrainingGroup::query()->updateOrCreate(
                ['code' => $definition['code']],
                $attributes,
            );

            $this->pattern($group, $definition['schedule']);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function groups(): array
    {
        return [
            [
                'code' => 'DEMO-B-EVENING',
                'group_number' => 'GRP-DEMO-B-EVENING',
                'program' => 'category_b_standard',
                'status' => 'recruiting',
                'factory' => 'recruiting',
                'group_schedule' => 'evening',
                'schedule' => 'mondayEvening',
                'capacity' => 12,
                'taken' => 4,
                'visible' => true,
                'accepting' => true,
                'name' => $this->translations('Категория B вечерняя группа', 'Category B evening group', 'B kategorijos vakarine grupe', 'Wieczorowa grupa kategorii B'),
            ],
            [
                'code' => 'DEMO-B-WEEKEND',
                'group_number' => 'GRP-DEMO-B-WEEKEND',
                'program' => 'category_b_standard',
                'status' => 'recruiting',
                'factory' => 'recruiting',
                'group_schedule' => 'weekend',
                'schedule' => 'weekendMorning',
                'capacity' => 10,
                'taken' => 3,
                'visible' => true,
                'accepting' => true,
                'name' => $this->translations('Категория B группа выходного дня', 'Category B weekend group', 'B kategorijos savaitgalio grupe', 'Weekendowa grupa kategorii B'),
            ],
            [
                'code' => 'DEMO-B-INTENSIVE',
                'group_number' => 'GRP-DEMO-B-INTENSIVE',
                'program' => 'category_b_intensive',
                'status' => 'almost_full',
                'factory' => 'almostFull',
                'group_schedule' => 'morning',
                'schedule' => 'weekdayMorning',
                'capacity' => 8,
                'taken' => 7,
                'visible' => true,
                'accepting' => true,
                'name' => $this->translations('Категория B интенсив', 'Category B intensive group', 'B kategorijos intensyvi grupe', 'Intensywna grupa kategorii B'),
            ],
            [
                'code' => 'DEMO-EXAM-PREP',
                'group_number' => 'GRP-DEMO-EXAM-PREP',
                'program' => 'exam_preparation',
                'status' => 'recruiting',
                'factory' => 'recruiting',
                'group_schedule' => 'evening',
                'schedule' => 'wednesdayEvening',
                'capacity' => 8,
                'taken' => 2,
                'visible' => true,
                'accepting' => true,
                'name' => $this->translations('Подготовка к экзамену', 'Exam preparation group', 'Pasiruosimo egzaminui grupe', 'Grupa przygotowania do egzaminu'),
            ],
            [
                'code' => 'DEMO-INDIVIDUAL',
                'group_number' => 'GRP-DEMO-INDIVIDUAL',
                'program' => 'individual_lessons',
                'status' => 'scheduled',
                'factory' => 'scheduled',
                'group_schedule' => 'morning',
                'schedule' => 'weekdayMorning',
                'capacity' => 6,
                'taken' => 1,
                'visible' => false,
                'accepting' => false,
                'name' => $this->translations('Индивидуальные уроки', 'Individual lessons group', 'Individualiu pamoku grupe', 'Grupa lekcji indywidualnych'),
            ],
        ];
    }

    private function branch(): Branch
    {
        $branch = Branch::factory()->translated()->make([
            'code' => 'DEMO_VILNIUS',
            'slug' => 'demo-vilnius-main',
            'name' => 'Demo Vilnius Branch',
        ]);

        $attributes = $branch->only($branch->getFillable());
        unset($attributes['slug']);

        return Branch::query()->updateOrCreate(
            ['slug' => 'demo-vilnius-main'],
            $attributes,
        );
    }

    private function pattern(TrainingGroup $group, string $state): void
    {
        $pattern = TrainingGroupSchedulePattern::factory()
            ->theory()
            ->{$state}()
            ->translated()
            ->make([
                'training_group_id' => $group->id,
                'sort_order' => 10,
            ]);

        $attributes = $pattern->only($pattern->getFillable());
        unset($attributes['training_group_id'], $attributes['day_of_week'], $attributes['start_time']);

        TrainingGroupSchedulePattern::query()->updateOrCreate(
            [
                'training_group_id' => $group->id,
                'day_of_week' => $pattern->day_of_week,
                'start_time' => $pattern->start_time,
            ],
            $attributes,
        );
    }

    private function legacyStatus(string $code): GroupStatus
    {
        return match ($code) {
            'recruiting' => GroupStatus::Recruiting,
            'almost_full', 'full' => GroupStatus::AlmostFull,
            'closed', 'archived' => GroupStatus::Closed,
            'active', 'paused' => GroupStatus::Active,
            'completed' => GroupStatus::Completed,
            'cancelled' => GroupStatus::Cancelled,
            default => GroupStatus::Planned,
        };
    }

    /**
     * @return array<string, string>
     */
    private function translations(string $ru, string $en, string $lt, string $pl): array
    {
        return compact('ru', 'en', 'lt', 'pl');
    }
}

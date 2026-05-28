<?php

namespace Database\Seeders;

use App\Models\LearningProgram;
use App\Models\LearningProgramModule;
use App\Models\LearningTopic;
use App\Models\Course;
use App\Models\TrainingGroup;
use App\Models\TrainingGroupSchedulePattern;
use Illuminate\Database\Seeder;

class EducationGroupSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            TrainingGroupStatusSeeder::class,
            WebsiteTrainingGroupSeeder::class,
        ]);

        $course = Course::query()
            ->where('slug', 'category-b-manual')
            ->first();

        if ($course === null) {
            return;
        }

        $program = LearningProgram::query()->updateOrCreate(
            ['code' => 'category_b_standard'],
            LearningProgram::factory()
                ->default()
                ->make([
                    'course_id' => $course->id,
                    'course_category_id' => $course->course_category_id,
                    'code' => 'category_b_standard',
                    'sort_order' => 10,
                ])
                ->only((new LearningProgram)->getFillable()),
        );

        $module = LearningProgramModule::query()->updateOrCreate(
            [
                'learning_program_id' => $program->id,
                'code' => 'B_THEORY_BASICS',
            ],
            LearningProgramModule::factory()
                ->theory()
                ->make([
                    'learning_program_id' => $program->id,
                    'code' => 'B_THEORY_BASICS',
                    'sort_order' => 10,
                ])
                ->only((new LearningProgramModule)->getFillable()),
        );

        LearningTopic::query()->updateOrCreate(
            [
                'learning_program_module_id' => $module->id,
                'code' => 'B_TRAFFIC_RULES',
            ],
            LearningTopic::factory()
                ->theory()
                ->make([
                    'training_program_id' => $course->id,
                    'learning_program_module_id' => $module->id,
                    'code' => 'B_TRAFFIC_RULES',
                    'sort_order' => 10,
                ])
                ->only((new LearningTopic)->getFillable()),
        );

        $group = TrainingGroup::query()->where('code', 'B-VNO-001')->first();

        if ($group !== null) {
            $group->forceFill(['learning_program_id' => $program->id])->save();

            TrainingGroupSchedulePattern::query()->updateOrCreate(
                [
                    'training_group_id' => $group->id,
                    'day_of_week' => 1,
                    'start_time' => '18:00',
                ],
                TrainingGroupSchedulePattern::factory()
                    ->theory()
                    ->make([
                        'training_group_id' => $group->id,
                        'day_of_week' => 1,
                        'start_time' => '18:00',
                        'end_time' => '20:00',
                        'starts_at' => '18:00',
                        'ends_at' => '20:00',
                        'sort_order' => 10,
                    ])
                    ->only((new TrainingGroupSchedulePattern)->getFillable()),
            );
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\LearningProgram;
use App\Models\LearningProgramModule;
use App\Models\LearningTopic;
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

        $program = LearningProgram::query()
            ->where('slug', 'category-b-manual')
            ->first();

        if ($program === null) {
            return;
        }

        $module = LearningProgramModule::query()->updateOrCreate(
            [
                'training_program_id' => $program->id,
                'code' => 'B_THEORY_BASICS',
            ],
            LearningProgramModule::factory()
                ->theory()
                ->make([
                    'training_program_id' => $program->id,
                    'code' => 'B_THEORY_BASICS',
                    'sort_order' => 10,
                ])
                ->only((new LearningProgramModule)->getFillable()),
        );

        LearningTopic::query()->updateOrCreate(
            [
                'training_program_id' => $program->id,
                'code' => 'B_TRAFFIC_RULES',
            ],
            LearningTopic::factory()
                ->theory()
                ->make([
                    'training_program_id' => $program->id,
                    'course_module_id' => $module->id,
                    'code' => 'B_TRAFFIC_RULES',
                    'sort_order' => 10,
                ])
                ->only((new LearningTopic)->getFillable()),
        );

        $group = TrainingGroup::query()->where('code', 'B-VNO-001')->first();

        if ($group !== null) {
            TrainingGroupSchedulePattern::query()->updateOrCreate(
                [
                    'training_group_id' => $group->id,
                    'day_of_week' => 1,
                    'starts_at' => '18:00',
                ],
                TrainingGroupSchedulePattern::factory()
                    ->theory()
                    ->make([
                        'training_group_id' => $group->id,
                        'day_of_week' => 1,
                        'starts_at' => '18:00',
                        'ends_at' => '20:00',
                        'sort_order' => 10,
                    ])
                    ->only((new TrainingGroupSchedulePattern)->getFillable()),
            );
        }
    }
}

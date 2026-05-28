<?php

namespace Database\Seeders;

use App\Models\LearningProgram;
use App\Models\LearningProgramModule;
use App\Models\LearningTopic;
use Illuminate\Database\Seeder;

class LearningProgramModuleSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(LearningProgramSeeder::class);

        foreach ($this->outline() as $programCode => $modules) {
            $program = LearningProgram::query()->where('code', $programCode)->first();

            if ($program === null) {
                continue;
            }

            foreach ($modules as $moduleIndex => $definition) {
                $module = $this->module($program, $definition, ($moduleIndex + 1) * 10);

                foreach ($definition['topics'] as $topicIndex => $topic) {
                    $this->topic($program, $module, $topic, ($topicIndex + 1) * 10);
                }
            }
        }
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function outline(): array
    {
        return [
            'category_b_standard' => [
                [
                    'code' => 'category_b_theory',
                    'state' => 'theory',
                    'hours' => 40,
                    'name' => $this->translations('Теория', 'Theory', 'Teorija', 'Teoria'),
                    'topics' => [
                        ['code' => 'category_b_traffic_rules', 'state' => 'trafficRules'],
                        ['code' => 'category_b_road_signs', 'state' => 'roadSigns'],
                        ['code' => 'category_b_road_safety', 'state' => 'safety'],
                    ],
                ],
                [
                    'code' => 'category_b_practice',
                    'state' => 'practice',
                    'hours' => 30,
                    'name' => $this->translations('Практика', 'Practice', 'Praktika', 'Praktyka'),
                    'topics' => [
                        ['code' => 'category_b_first_drive', 'state' => 'firstDrive'],
                        ['code' => 'category_b_parking', 'state' => 'parking'],
                        ['code' => 'category_b_city_driving', 'state' => 'cityDriving'],
                        ['code' => 'category_b_highway_driving', 'state' => 'highwayDriving'],
                    ],
                ],
                [
                    'code' => 'category_b_exam_preparation',
                    'state' => 'examPreparation',
                    'hours' => 6,
                    'name' => $this->translations('Подготовка к экзамену', 'Exam preparation', 'Pasiruosimas egzaminui', 'Przygotowanie do egzaminu'),
                    'topics' => [
                        ['code' => 'category_b_exam_route', 'state' => 'examRoute'],
                    ],
                ],
                [
                    'code' => 'category_b_internal_exam',
                    'state' => 'internalExam',
                    'hours' => 2,
                    'name' => $this->translations('Внутренний экзамен', 'Internal exam preparation', 'Vidinio egzamino parengimas', 'Przygotowanie do egzaminu wewnetrznego'),
                    'topics' => [],
                ],
            ],
            'category_b_intensive' => [
                [
                    'code' => 'category_b_intensive_theory',
                    'state' => 'theory',
                    'hours' => 40,
                    'name' => $this->translations('Интенсивная теория', 'Intensive theory', 'Intensyvi teorija', 'Teoria intensywna'),
                    'topics' => [
                        ['code' => 'category_b_intensive_traffic_rules', 'state' => 'trafficRules'],
                        ['code' => 'category_b_intensive_road_signs', 'state' => 'roadSigns'],
                    ],
                ],
                [
                    'code' => 'category_b_intensive_practice',
                    'state' => 'practice',
                    'hours' => 34,
                    'name' => $this->translations('Интенсивная практика', 'Intensive practice', 'Intensyvi praktika', 'Praktyka intensywna'),
                    'topics' => [
                        ['code' => 'category_b_intensive_city_driving', 'state' => 'cityDriving'],
                        ['code' => 'category_b_intensive_exam_route', 'state' => 'examRoute'],
                    ],
                ],
            ],
            'individual_lessons' => [
                [
                    'code' => 'individual_practice',
                    'state' => 'practice',
                    'hours' => 10,
                    'name' => $this->translations('Индивидуальная практика', 'Individual practice', 'Individuali praktika', 'Praktyka indywidualna'),
                    'topics' => [
                        ['code' => 'individual_first_drive', 'state' => 'firstDrive'],
                        ['code' => 'individual_parking', 'state' => 'parking'],
                    ],
                ],
            ],
            'exam_preparation' => [
                [
                    'code' => 'exam_route_preparation',
                    'state' => 'examPreparation',
                    'hours' => 8,
                    'name' => $this->translations('Маршрут экзамена', 'Exam route preparation', 'Egzamino marsruto parengimas', 'Przygotowanie trasy egzaminacyjnej'),
                    'topics' => [
                        ['code' => 'exam_preparation_route', 'state' => 'examRoute'],
                        ['code' => 'exam_preparation_safety', 'state' => 'safety'],
                    ],
                ],
            ],
            'skill_recovery' => [
                [
                    'code' => 'skill_recovery_practice',
                    'state' => 'practice',
                    'hours' => 8,
                    'name' => $this->translations('Восстановление практики', 'Skill recovery practice', 'Igudziu atkurimo praktika', 'Praktyka odzyskiwania umiejetnosci'),
                    'topics' => [
                        ['code' => 'skill_recovery_city_driving', 'state' => 'cityDriving'],
                        ['code' => 'skill_recovery_parking', 'state' => 'parking'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    private function module(LearningProgram $program, array $definition, int $sortOrder): LearningProgramModule
    {
        $module = LearningProgramModule::factory()
            ->{$definition['state']}()
            ->make([
                'learning_program_id' => $program->id,
                'code' => $definition['code'],
                'name_translations' => $definition['name'],
                'description_translations' => $definition['name'],
                'required_hours' => $definition['hours'],
                'sort_order' => $sortOrder,
                'is_required' => true,
                'is_active' => true,
            ]);

        $attributes = $module->only($module->getFillable());
        unset($attributes['learning_program_id'], $attributes['code']);

        return LearningProgramModule::query()->updateOrCreate(
            [
                'learning_program_id' => $program->id,
                'code' => $definition['code'],
            ],
            $attributes,
        );
    }

    /**
     * @param  array<string, string>  $definition
     */
    private function topic(LearningProgram $program, LearningProgramModule $module, array $definition, int $sortOrder): LearningTopic
    {
        $topic = LearningTopic::factory()
            ->{$definition['state']}()
            ->make([
                'training_program_id' => $program->course_id,
                'learning_program_module_id' => $module->id,
                'code' => $definition['code'],
                'sort_order' => $sortOrder,
                'is_required' => true,
                'is_active' => true,
            ]);

        $attributes = $topic->only($topic->getFillable());
        unset($attributes['learning_program_module_id'], $attributes['code']);

        return LearningTopic::query()->updateOrCreate(
            [
                'learning_program_module_id' => $module->id,
                'code' => $definition['code'],
            ],
            $attributes,
        );
    }

    /**
     * @return array<string, string>
     */
    private function translations(string $ru, string $en, string $lt, string $pl): array
    {
        return compact('ru', 'en', 'lt', 'pl');
    }
}

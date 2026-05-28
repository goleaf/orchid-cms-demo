<?php

namespace Database\Seeders;

use App\Models\ExamAdmissionRule;
use App\Models\ExamAttemptStatus;
use App\Models\ExamResultStatus;
use App\Models\ExamStatus;
use App\Models\ExamType;
use Database\Seeders\Concerns\SeedsFactoryBackedDictionaries;
use Illuminate\Database\Seeder;

class ExamDictionarySeeder extends Seeder
{
    use SeedsFactoryBackedDictionaries;

    public function run(): void
    {
        $this->seedFactoryBackedDictionary(ExamType::class, 'code', [
            ['code' => 'internal_theory', 'state' => 'internalTheory'],
            ['code' => 'internal_practical', 'state' => 'internalPractical'],
            ['code' => 'state_theory', 'state' => 'stateTheory'],
            ['code' => 'state_practical', 'state' => 'statePractical'],
        ]);

        $this->seedFactoryBackedDictionary(ExamStatus::class, 'code', [
            ['code' => 'draft', 'state' => 'draft'],
            ['code' => 'scheduled', 'state' => 'scheduled'],
            ['code' => 'open', 'state' => 'open'],
            ['code' => 'in_progress', 'state' => 'inProgress'],
            ['code' => 'completed', 'state' => 'completed'],
            ['code' => 'cancelled', 'state' => 'cancelled'],
            ['code' => 'archived', 'state' => 'archived'],
        ]);

        $this->seedFactoryBackedDictionary(ExamAttemptStatus::class, 'code', [
            ['code' => 'planned', 'state' => 'planned'],
            ['code' => 'allowed', 'state' => 'allowed'],
            ['code' => 'blocked', 'state' => 'blocked'],
            ['code' => 'in_progress', 'state' => 'inProgress'],
            ['code' => 'passed', 'state' => 'passed'],
            ['code' => 'failed', 'state' => 'failed'],
            ['code' => 'no_show', 'state' => 'noShow'],
            ['code' => 'cancelled', 'state' => 'cancelled'],
            ['code' => 'archived', 'state' => 'archived'],
        ]);

        $this->seedFactoryBackedDictionary(ExamResultStatus::class, 'code', [
            ['code' => 'pending', 'state' => 'pending'],
            ['code' => 'passed', 'state' => 'passed'],
            ['code' => 'failed', 'state' => 'failed'],
            ['code' => 'needs_retake', 'state' => 'needsRetake'],
            ['code' => 'cancelled', 'state' => 'cancelled'],
        ]);

        $this->seedAdmissionRules();
    }

    private function seedAdmissionRules(): void
    {
        $rules = [
            'internal_theory' => [
                'required_theory_hours' => 40,
                'required_practice_hours' => 0,
                'require_documents' => true,
                'require_no_debt' => true,
                'require_internal_exam_passed' => false,
            ],
            'internal_practical' => [
                'required_theory_hours' => 40,
                'required_practice_hours' => 30,
                'require_documents' => true,
                'require_no_debt' => true,
                'require_internal_exam_passed' => false,
            ],
            'state_theory' => [
                'required_theory_hours' => 40,
                'required_practice_hours' => 0,
                'require_documents' => true,
                'require_no_debt' => true,
                'require_internal_exam_passed' => true,
            ],
            'state_practical' => [
                'required_theory_hours' => 40,
                'required_practice_hours' => 30,
                'require_documents' => true,
                'require_no_debt' => true,
                'require_internal_exam_passed' => true,
            ],
        ];

        foreach ($rules as $typeCode => $attributes) {
            $type = ExamType::query()->where('code', $typeCode)->first();

            if ($type === null) {
                continue;
            }

            $rule = ExamAdmissionRule::factory()
                ->forExamType($type)
                ->make([
                    'course_id' => null,
                    'course_category_id' => null,
                    ...$attributes,
                    'is_active' => true,
                ]);

            ExamAdmissionRule::query()->updateOrCreate(
                [
                    'exam_type_id' => $type->id,
                    'course_id' => null,
                    'course_category_id' => null,
                ],
                $rule->only((new ExamAdmissionRule)->getFillable()),
            );
        }
    }
}

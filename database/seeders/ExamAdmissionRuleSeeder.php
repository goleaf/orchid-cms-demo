<?php

namespace Database\Seeders;

use App\Models\ExamAdmissionRule;
use App\Models\ExamType;
use Illuminate\Database\Seeder;

class ExamAdmissionRuleSeeder extends Seeder
{
    public function run(): void
    {
        if (! ExamType::query()->exists()) {
            $this->call(ExamTypeSeeder::class);
        }

        foreach ($this->rules() as $typeCode => $attributes) {
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

    /**
     * @return array<string, array<string, mixed>>
     */
    private function rules(): array
    {
        return [
            'internal_theory' => [
                'required_theory_hours' => 40,
                'required_practice_hours' => 0,
                'require_documents' => true,
                'require_no_debt' => true,
                'require_internal_exam_passed' => false,
                'is_active' => true,
            ],
            'internal_practical' => [
                'required_theory_hours' => 40,
                'required_practice_hours' => 30,
                'require_documents' => true,
                'require_no_debt' => true,
                'require_internal_exam_passed' => false,
                'is_active' => true,
            ],
            'official_theory_placeholder' => [
                'required_theory_hours' => 40,
                'required_practice_hours' => 0,
                'require_documents' => true,
                'require_no_debt' => true,
                'require_internal_exam_passed' => true,
                'is_active' => true,
            ],
            'official_practical_placeholder' => [
                'required_theory_hours' => 40,
                'required_practice_hours' => 30,
                'require_documents' => true,
                'require_no_debt' => true,
                'require_internal_exam_passed' => true,
                'is_active' => true,
            ],
            'state_theory' => [
                'required_theory_hours' => 40,
                'required_practice_hours' => 0,
                'require_documents' => true,
                'require_no_debt' => true,
                'require_internal_exam_passed' => true,
                'is_active' => false,
            ],
            'state_practical' => [
                'required_theory_hours' => 40,
                'required_practice_hours' => 30,
                'require_documents' => true,
                'require_no_debt' => true,
                'require_internal_exam_passed' => true,
                'is_active' => false,
            ],
        ];
    }
}

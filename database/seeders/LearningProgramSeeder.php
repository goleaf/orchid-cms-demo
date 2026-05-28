<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\LearningProgram;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class LearningProgramSeeder extends Seeder
{
    public function run(): void
    {
        $contexts = $this->contexts();

        foreach ($this->programs($contexts) as $definition) {
            $program = $definition['factory']->make($definition['attributes']);

            $this->upsert(LearningProgram::class, 'code', $definition['code'], $program);
        }

        LearningProgram::query()
            ->where('code', '!=', 'category_b_standard')
            ->update(['is_default' => false]);
    }

    /**
     * @return array<string, array{category: CourseCategory, course: Course}>
     */
    private function contexts(): array
    {
        $categoryB = $this->category('category_b', 'categoryB');
        $categoryA = $this->category('category_a', 'categoryA');
        $individual = $this->category('individual_lessons', 'individualLessons');
        $exam = $this->category('exam_preparation', 'examPreparation');
        $recovery = $this->category('skill_recovery', 'skillRecovery');

        return [
            'category_b' => [
                'category' => $categoryB,
                'course' => $this->course('category-b-manual', 'categoryB', $categoryB),
            ],
            'category_a' => [
                'category' => $categoryA,
                'course' => $this->course('category-a-motorcycle', 'categoryA', $categoryA),
            ],
            'individual' => [
                'category' => $individual,
                'course' => $this->course('individual-driving-lessons', 'individualLessons', $individual),
            ],
            'exam' => [
                'category' => $exam,
                'course' => $this->course('exam-preparation', 'examPreparation', $exam),
            ],
            'recovery' => [
                'category' => $recovery,
                'course' => $this->course('skill-recovery', 'skillRecovery', $recovery),
            ],
        ];
    }

    /**
     * @param  array<string, array{category: CourseCategory, course: Course}>  $contexts
     * @return array<int, array{code: string, factory: mixed, attributes: array<string, mixed>}>
     */
    private function programs(array $contexts): array
    {
        return [
            [
                'code' => 'category_b_standard',
                'factory' => LearningProgram::factory()->forCategoryB()->default(),
                'attributes' => [
                    'course_id' => $contexts['category_b']['course']->id,
                    'course_category_id' => $contexts['category_b']['category']->id,
                    'code' => 'category_b_standard',
                    'is_default' => true,
                    'sort_order' => 10,
                ],
            ],
            [
                'code' => 'category_b_intensive',
                'factory' => LearningProgram::factory()->forCategoryB()->intensive(),
                'attributes' => [
                    'course_id' => $contexts['category_b']['course']->id,
                    'course_category_id' => $contexts['category_b']['category']->id,
                    'code' => 'category_b_intensive',
                    'name_translations' => $this->translations('Категория B интенсив', 'Category B Intensive Program', 'B kategorijos intensyvi programa', 'Program intensywny kategorii B'),
                    'sort_order' => 20,
                ],
            ],
            [
                'code' => 'individual_lessons',
                'factory' => LearningProgram::factory()->forIndividualLessons(),
                'attributes' => [
                    'course_id' => $contexts['individual']['course']->id,
                    'course_category_id' => $contexts['individual']['category']->id,
                    'code' => 'individual_lessons',
                    'sort_order' => 30,
                ],
            ],
            [
                'code' => 'exam_preparation',
                'factory' => LearningProgram::factory()->forExamPreparation(),
                'attributes' => [
                    'course_id' => $contexts['exam']['course']->id,
                    'course_category_id' => $contexts['exam']['category']->id,
                    'code' => 'exam_preparation',
                    'sort_order' => 40,
                ],
            ],
            [
                'code' => 'skill_recovery',
                'factory' => LearningProgram::factory()->standard(),
                'attributes' => [
                    'course_id' => $contexts['recovery']['course']->id,
                    'course_category_id' => $contexts['recovery']['category']->id,
                    'code' => 'skill_recovery',
                    'name_translations' => $this->translations('Восстановление навыков', 'Skill Recovery Program', 'Igudziu atkurimo programa', 'Program odzyskiwania umiejetnosci'),
                    'sort_order' => 50,
                ],
            ],
        ];
    }

    private function category(string $code, string $state): CourseCategory
    {
        $category = CourseCategory::factory()->{$state}()->make();

        return $this->upsert(CourseCategory::class, 'code', $code, $category);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function course(string $slug, ?string $state, CourseCategory $category, array $overrides = []): Course
    {
        $factory = Course::factory();

        if ($state !== null) {
            $factory = $factory->{$state}();
        }

        $course = $factory->make([
            'course_category_id' => $category->id,
            'slug' => $slug,
            ...$overrides,
        ]);

        return $this->upsert(Course::class, 'slug', $slug, $course);
    }

    /**
     * @template T of Model
     *
     * @param  class-string<T>  $modelClass
     * @return T
     */
    private function upsert(string $modelClass, string $keyColumn, string $keyValue, Model $model): Model
    {
        $attributes = $model->only($model->getFillable());
        unset($attributes[$keyColumn]);

        return $modelClass::query()->updateOrCreate(
            [$keyColumn => $keyValue],
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

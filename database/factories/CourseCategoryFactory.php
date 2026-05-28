<?php

namespace Database\Factories;

use App\Models\CourseCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CourseCategory>
 */
class CourseCategoryFactory extends Factory
{
    protected $model = CourseCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $code = $this->faker->unique()->slug(2);
        $nameTranslations = $this->translations('Категория курса', 'Course category', 'Kurso kategorija', 'Kategoria kursu');
        $descriptionTranslations = $this->translations('Категория публичных курсов автошколы.', 'Public driving school course category.', 'Vairavimo mokyklos kursu kategorija.', 'Kategoria kursow szkoly jazdy.');
        $shortDescriptionTranslations = $this->translations('Курсы автошколы.', 'Driving school courses.', 'Vairavimo kursai.', 'Kursy jazdy.');

        return [
            'uuid' => (string) Str::uuid(),
            'code' => $code,
            'slug' => $code,
            'name_translations' => $nameTranslations,
            'description_translations' => $descriptionTranslations,
            'short_description_translations' => $shortDescriptionTranslations,
            'seo_title_translations' => null,
            'seo_description_translations' => null,
            'image' => null,
            'icon' => null,
            'is_active' => true,
            'is_visible_on_site' => true,
            'sort_order' => 0,
            'created_by_id' => null,
            'updated_by_id' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (): array => ['is_active' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }

    public function visibleOnSite(): static
    {
        return $this->state(fn (): array => ['is_visible_on_site' => true]);
    }

    public function hiddenFromSite(): static
    {
        return $this->state(fn (): array => ['is_visible_on_site' => false]);
    }

    public function translated(): static
    {
        return $this->state(fn (): array => [
            'name_translations' => $this->translations('Категория курса', 'Course category', 'Kurso kategorija', 'Kategoria kursu'),
            'description_translations' => $this->translations('Категория публичных курсов автошколы.', 'Public driving school course category.', 'Vairavimo mokyklos kursu kategorija.', 'Kategoria kursow szkoly jazdy.'),
            'short_description_translations' => $this->translations('Курсы автошколы.', 'Driving school courses.', 'Vairavimo kursai.', 'Kursy jazdy.'),
        ]);
    }

    public function categoryB(): static
    {
        return $this->state(fn (): array => [
            'code' => 'category_b',
            'slug' => 'category-b',
            'name_translations' => $this->translations('Категория B', 'Category B', 'B kategorija', 'Kategoria B'),
            'sort_order' => 10,
        ]);
    }

    public function categoryA(): static
    {
        return $this->state(fn (): array => [
            'code' => 'category_a',
            'slug' => 'category-a',
            'name_translations' => $this->translations('Категория A', 'Category A', 'A kategorija', 'Kategoria A'),
            'sort_order' => 20,
        ]);
    }

    public function individualLessons(): static
    {
        return $this->state(fn (): array => [
            'code' => 'individual_lessons',
            'slug' => 'individual-lessons',
            'name_translations' => $this->translations('Индивидуальные уроки', 'Individual lessons', 'Individualios pamokos', 'Lekcje indywidualne'),
            'sort_order' => 30,
        ]);
    }

    public function examPreparation(): static
    {
        return $this->state(fn (): array => [
            'code' => 'exam_preparation',
            'slug' => 'exam-preparation',
            'name_translations' => $this->translations('Подготовка к экзамену', 'Exam preparation', 'Pasiruosimas egzaminui', 'Przygotowanie do egzaminu'),
            'sort_order' => 40,
        ]);
    }

    public function skillRecovery(): static
    {
        return $this->state(fn (): array => [
            'code' => 'skill_recovery',
            'slug' => 'skill-recovery',
            'name_translations' => $this->translations('Восстановление навыков', 'Skill recovery', 'Igudziu atkurimas', 'Odswiezenie umiejetnosci'),
            'sort_order' => 50,
        ]);
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

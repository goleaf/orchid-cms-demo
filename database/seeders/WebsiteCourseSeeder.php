<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Seeder;

class WebsiteCourseSeeder extends Seeder
{
    public function run(): void
    {
        $categories = $this->seedCategories();
        $this->seedCourses($categories);
    }

    /**
     * @return array<string, CourseCategory>
     */
    private function seedCategories(): array
    {
        $states = [
            'category-b' => 'categoryB',
            'category-a' => 'categoryA',
            'individual-lessons' => 'individualLessons',
            'exam-preparation' => 'examPreparation',
            'skill-recovery' => 'skillRecovery',
        ];

        $categories = [];

        foreach ($states as $slug => $state) {
            $payload = CourseCategory::factory()
                ->translated()
                ->{$state}()
                ->active()
                ->visibleOnSite()
                ->make(['slug' => $slug])
                ->only((new CourseCategory)->getFillable());

            $categories[$slug] = CourseCategory::query()->updateOrCreate(['slug' => $slug], $payload);
        }

        return $categories;
    }

    /**
     * @param  array<string, CourseCategory>  $categories
     */
    private function seedCourses(array $categories): void
    {
        $courses = [
            'category-b-manual' => [
                'state' => 'categoryB',
                'category' => 'category-b',
                'code' => 'CATEGORY_B_MANUAL',
                'sort_order' => 10,
                'price' => 1290,
                'title' => ['ru' => 'Категория B в Вильнюсе', 'en' => 'Category B in Vilnius', 'lt' => 'B kategorija Vilniuje', 'pl' => 'Kategoria B w Wilnie'],
            ],
            'category-a-motorcycle' => [
                'state' => 'categoryA',
                'category' => 'category-a',
                'code' => 'CATEGORY_A_MOTORCYCLE',
                'sort_order' => 20,
                'price' => 890,
                'title' => ['ru' => 'Категория A', 'en' => 'Category A', 'lt' => 'A kategorija', 'pl' => 'Kategoria A'],
            ],
            'individual-driving-lessons' => [
                'state' => 'individualLessons',
                'category' => 'individual-lessons',
                'code' => 'INDIVIDUAL_DRIVING_LESSONS',
                'sort_order' => 30,
                'price' => 490,
                'title' => ['ru' => 'Индивидуальные уроки вождения', 'en' => 'Individual driving lessons', 'lt' => 'Individualios vairavimo pamokos', 'pl' => 'Indywidualne lekcje jazdy'],
            ],
            'exam-preparation' => [
                'state' => 'examPreparation',
                'category' => 'exam-preparation',
                'code' => 'EXAM_PREPARATION',
                'sort_order' => 40,
                'price' => 450,
                'title' => ['ru' => 'Подготовка к экзамену', 'en' => 'Exam preparation', 'lt' => 'Pasiruosimas egzaminui', 'pl' => 'Przygotowanie do egzaminu'],
            ],
            'skill-recovery' => [
                'state' => null,
                'category' => 'skill-recovery',
                'code' => 'SKILL_RECOVERY',
                'sort_order' => 50,
                'price' => 390,
                'title' => ['ru' => 'Восстановление навыков', 'en' => 'Skill recovery', 'lt' => 'Igudziu atkurimas', 'pl' => 'Odswiezenie umiejetnosci'],
            ],
        ];

        foreach ($courses as $slug => $course) {
            $factory = Course::factory()->active()->visibleOnSite()->translated()->withPrice($course['price']);

            if ($course['state'] !== null) {
                /** @var Factory $factory */
                $factory = $factory->{$course['state']}();
            }

            $payload = $factory
                ->make([
                    'slug' => $slug,
                    'code' => $course['code'],
                    'course_category_id' => $categories[$course['category']]?->id,
                    'title' => $course['title']['en'],
                    'title_translations' => $course['title'],
                    'name_translations' => $course['title'],
                    'short_description_translations' => [
                        'ru' => 'Курс автошколы с теорией, практикой и подготовкой к экзамену.',
                        'en' => 'Driving school course with theory, practice, and exam preparation.',
                        'lt' => 'Vairavimo mokyklos kursas su teorija, praktika ir pasirengimu egzaminui.',
                        'pl' => 'Kurs szkoly jazdy z teoria, praktyka i przygotowaniem do egzaminu.',
                    ],
                    'description_translations' => [
                        'ru' => 'Описание курса для сайта автошколы: программа, формат обучения, цена и ближайшие группы.',
                        'en' => 'Public course description for the driving school website.',
                        'lt' => 'Kurso aprasymas vairavimo mokyklos svetainei: programa, mokymo formatas, kaina ir artimiausios grupes.',
                        'pl' => 'Opis kursu na stronie szkoly jazdy: program, format szkolenia, cena i najblizsze grupy.',
                    ],
                    'is_featured' => $slug === 'category-b-manual',
                    'sort_order' => $course['sort_order'],
                ])
                ->only((new Course)->getFillable());

            Course::query()->updateOrCreate(['slug' => $slug], $payload);
        }
    }
}

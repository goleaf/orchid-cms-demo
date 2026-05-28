<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WebsiteCourseSeeder extends Seeder
{
    private const LOCALES = ['ru', 'en', 'lt', 'pl'];

    private const IMAGE_PATH = 'images/driving-school-hero.png';

    private const REQUIRED_DOCUMENTS = ['ID card', 'Medical certificate'];

    public function run(): void
    {
        $categories = $this->seedCategories();
        $this->seedCourses($categories);
        $this->backfillVisibleCourses($categories['category-b']);
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
                'old_price' => 1490,
                'license_category' => 'B',
                'transmission' => 'manual',
                'theory_hours' => 45,
                'practice_hours' => 30,
                'duration_weeks' => 14,
                'format' => 'hybrid',
                'icon' => 'car-front',
                'is_featured' => true,
            ],
            'category-a-motorcycle' => [
                'state' => 'categoryA',
                'category' => 'category-a',
                'code' => 'CATEGORY_A_MOTORCYCLE',
                'sort_order' => 20,
                'price' => 890,
                'old_price' => 990,
                'license_category' => 'A',
                'transmission' => 'manual',
                'theory_hours' => 30,
                'practice_hours' => 12,
                'duration_weeks' => 6,
                'format' => 'group',
                'icon' => 'bike',
                'is_featured' => false,
            ],
            'individual-driving-lessons' => [
                'state' => 'individualLessons',
                'category' => 'individual-lessons',
                'code' => 'INDIVIDUAL_DRIVING_LESSONS',
                'sort_order' => 30,
                'price' => 490,
                'old_price' => 590,
                'license_category' => 'B',
                'transmission' => 'manual',
                'theory_hours' => 4,
                'practice_hours' => 10,
                'duration_weeks' => 3,
                'format' => 'individual',
                'icon' => 'user-round-check',
                'is_featured' => false,
            ],
            'exam-preparation' => [
                'state' => 'examPreparation',
                'category' => 'exam-preparation',
                'code' => 'EXAM_PREPARATION',
                'sort_order' => 40,
                'price' => 450,
                'old_price' => 520,
                'license_category' => 'B',
                'transmission' => 'manual',
                'theory_hours' => 8,
                'practice_hours' => 8,
                'duration_weeks' => 2,
                'format' => 'individual',
                'icon' => 'clipboard-check',
                'is_featured' => false,
            ],
            'skill-recovery' => [
                'state' => 'skillRecovery',
                'category' => 'skill-recovery',
                'code' => 'SKILL_RECOVERY',
                'sort_order' => 50,
                'price' => 390,
                'old_price' => 460,
                'license_category' => 'B',
                'transmission' => 'automatic',
                'theory_hours' => 3,
                'practice_hours' => 8,
                'duration_weeks' => 2,
                'format' => 'individual',
                'icon' => 'refresh-cw',
                'is_featured' => false,
            ],
        ];

        foreach ($courses as $slug => $course) {
            /** @var Factory $factory */
            $factory = Course::factory()
                ->active()
                ->visibleOnSite()
                ->translated()
                ->withPrice($course['price'])
                ->withOldPrice($course['old_price'])
                ->{$course['state']}();

            $payload = $factory
                ->make([
                    'slug' => $slug,
                    'code' => $course['code'],
                    'course_category_id' => $categories[$course['category']]?->id,
                    'license_category' => $course['license_category'],
                    'transmission' => $course['transmission'],
                    'theory_hours' => $course['theory_hours'],
                    'practice_hours' => $course['practice_hours'],
                    'duration_weeks' => $course['duration_weeks'],
                    'format' => $course['format'],
                    'available_languages' => self::LOCALES,
                    'required_documents' => self::REQUIRED_DOCUMENTS,
                    'canonical_url' => url('/courses/'.$slug),
                    'open_graph_image' => self::IMAGE_PATH,
                    'og_image' => self::IMAGE_PATH,
                    'image_path' => self::IMAGE_PATH,
                    'icon' => $course['icon'],
                    'structured_data' => [
                        'type' => 'Course',
                        'provider' => 'DrivePro Academy',
                        'courseMode' => $course['format'],
                        'inLanguage' => ['ru', 'en', 'lt', 'pl'],
                    ],
                    'is_featured' => $course['is_featured'],
                    'sort_order' => $course['sort_order'],
                ])
                ->only((new Course)->getFillable());

            $this->updateOrCreateCourse($slug, $course['code'], $payload);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function updateOrCreateCourse(string $slug, string $code, array $payload): Course
    {
        $course = Course::query()
            ->where('slug', $slug)
            ->orWhere('code', $code)
            ->first();

        if ($course === null) {
            return Course::query()->create($payload);
        }

        $course->fill($payload)->save();

        return $course;
    }

    private function backfillVisibleCourses(CourseCategory $fallbackCategory): void
    {
        Course::query()
            ->active()
            ->visibleOnSite()
            ->chunkById(100, function ($courses) use ($fallbackCategory): void {
                foreach ($courses as $course) {
                    $title = $this->completeTranslations(
                        $course->getTranslations('name') ?: $course->getTranslations('title'),
                        $this->translationsFromValue($course->title ?: Str::headline($course->slug ?: 'Driving course')),
                    );
                    $shortDescription = $this->completeTranslations(
                        $course->getTranslations('short_description'),
                        [
                            'ru' => 'Курс автошколы с теорией, практикой и сопровождением ученика.',
                            'en' => 'Driving school course with theory, practice, and student support.',
                            'lt' => 'Vairavimo mokyklos kursas su teorija, praktika ir mokinio pagalba.',
                            'pl' => 'Kurs szkoly jazdy z teoria, praktyka i wsparciem ucznia.',
                        ],
                    );
                    $description = $this->completeTranslations(
                        $course->getTranslations('description'),
                        [
                            'ru' => 'Подробная программа курса с форматом обучения, часами практики, документами и подготовкой к экзамену.',
                            'en' => 'Detailed course program with study format, practice hours, documents, and exam preparation.',
                            'lt' => 'Išsami kurso programa su mokymo formatu, praktikos valandomis, dokumentais ir pasiruošimu egzaminui.',
                            'pl' => 'Szczegolowy program kursu z formatem nauki, godzinami praktyki, dokumentami i przygotowaniem do egzaminu.',
                        ],
                    );
                    $summary = $this->completeTranslations(
                        $course->getTranslations('program_summary'),
                        [
                            'ru' => 'Понятный маршрут обучения от заявки до уверенной практики.',
                            'en' => 'A clear learning path from application to confident practice.',
                            'lt' => 'Aiškus mokymosi kelias nuo paraiškos iki užtikrintos praktikos.',
                            'pl' => 'Jasna sciezka nauki od zgloszenia do pewnej praktyki.',
                        ],
                    );
                    $requirements = $this->completeTranslations(
                        $course->getTranslations('requirements'),
                        [
                            'ru' => 'Нужны документы ученика, медицинская справка и согласованный учебный план.',
                            'en' => 'Student documents, medical certificate, and an agreed training plan are required.',
                            'lt' => 'Reikia mokinio dokumentų, medicininės pažymos ir suderinto mokymo plano.',
                            'pl' => 'Wymagane sa dokumenty ucznia, zaswiadczenie lekarskie i uzgodniony plan szkolenia.',
                        ],
                    );
                    $included = $this->completeTranslations(
                        $course->getTranslations('includes') ?: $course->getTranslations('included_items'),
                        [
                            'ru' => 'Теория, практика, учебные материалы, сопровождение заявки и консультация менеджера.',
                            'en' => 'Theory, practice, study materials, application support, and manager consultation.',
                            'lt' => 'Teorija, praktika, mokomoji medžiaga, paraiškos palyda ir vadybininko konsultacija.',
                            'pl' => 'Teoria, praktyka, materialy, obsluga zgloszenia i konsultacja menedzera.',
                        ],
                    );
                    $extraCosts = $this->completeTranslations(
                        $course->getTranslations('excludes') ?: $course->getTranslations('extra_costs'),
                        [
                            'ru' => 'Госэкзамены, медицинская справка и дополнительные часы оплачиваются отдельно.',
                            'en' => 'State exams, medical certificate, and extra hours are paid separately.',
                            'lt' => 'Valstybiniai egzaminai, medicininė pažyma ir papildomos valandos apmokami atskirai.',
                            'pl' => 'Egzaminy panstwowe, zaswiadczenie lekarskie i dodatkowe godziny sa platne osobno.',
                        ],
                    );
                    $duration = $this->completeTranslations(
                        $course->getTranslations('duration'),
                        [
                            'ru' => ($course->duration_weeks ?: 8).' недель',
                            'en' => ($course->duration_weeks ?: 8).' weeks',
                            'lt' => ($course->duration_weeks ?: 8).' savaitės',
                            'pl' => ($course->duration_weeks ?: 8).' tygodni',
                        ],
                    );
                    $theoryProgram = $this->completeTranslations(
                        $course->getTranslations('theory_program'),
                        [
                            'ru' => 'ПДД, безопасность, дорожные ситуации и подготовка к теоретическому тесту.',
                            'en' => 'Traffic rules, safety, road situations, and theory test preparation.',
                            'lt' => 'Kelių eismo taisyklės, sauga, eismo situacijos ir teorijos testo pasiruošimas.',
                            'pl' => 'Przepisy, bezpieczenstwo, sytuacje drogowe i przygotowanie do testu teoretycznego.',
                        ],
                    );
                    $practiceProgram = $this->completeTranslations(
                        $course->getTranslations('practice_program'),
                        [
                            'ru' => 'Маршруты, парковка, манёвры, городская езда и подготовка к практическому экзамену.',
                            'en' => 'Routes, parking, maneuvers, city driving, and practical exam preparation.',
                            'lt' => 'Maršrutai, parkavimas, manevrai, vairavimas mieste ir pasiruošimas praktikos egzaminui.',
                            'pl' => 'Trasy, parkowanie, manewry, jazda miejska i przygotowanie do egzaminu praktycznego.',
                        ],
                    );
                    $seoTitle = $this->completeTranslations(
                        $course->getTranslations('seo_title'),
                        [
                            'ru' => $title['ru'].' | автошкола',
                            'en' => $title['en'].' | driving school',
                            'lt' => $title['lt'].' | vairavimo mokykla',
                            'pl' => $title['pl'].' | szkola jazdy',
                        ],
                    );
                    $seoDescription = $this->completeTranslations($course->getTranslations('seo_description'), $shortDescription);

                    $priceCents = (int) ($course->price_cents ?: 129_000);
                    $oldPriceCents = (int) ($course->old_price_cents ?: $priceCents + 20_000);

                    $course->fill([
                        'title' => $title['en'],
                        'title_translations' => $title,
                        'name_translations' => $title,
                        'short_description' => $shortDescription['en'],
                        'short_description_translations' => $shortDescription,
                        'description' => $description['en'],
                        'description_translations' => $description,
                        'program_summary_translations' => $summary,
                        'admission_requirements' => $requirements['en'],
                        'requirements_translations' => $requirements,
                        'included_items' => $included['en'],
                        'included_items_translations' => $included,
                        'includes_translations' => $included,
                        'extra_costs' => $extraCosts['en'],
                        'extra_costs_translations' => $extraCosts,
                        'excludes_translations' => $extraCosts,
                        'duration_translations' => $duration,
                        'theory_program' => $theoryProgram['en'],
                        'theory_program_translations' => $theoryProgram,
                        'practice_program' => $practiceProgram['en'],
                        'practice_program_translations' => $practiceProgram,
                        'seo_title' => $seoTitle['en'],
                        'seo_title_translations' => $seoTitle,
                        'meta_description' => $seoDescription['en'],
                        'seo_description_translations' => $seoDescription,
                        'og_title' => $seoTitle['en'],
                        'og_title_translations' => $this->completeTranslations($course->getTranslations('og_title'), $seoTitle),
                        'og_description' => $seoDescription['en'],
                        'og_description_translations' => $this->completeTranslations($course->getTranslations('og_description'), $seoDescription),
                        'license_category' => $course->license_category ?: 'B',
                        'transmission' => $course->transmission ?: 'manual',
                        'theory_hours' => $course->theory_hours ?: 30,
                        'practice_hours' => $course->practice_hours ?: 20,
                        'duration_weeks' => $course->duration_weeks ?: 8,
                        'format' => $course->format ?: 'hybrid',
                        'available_languages' => self::LOCALES,
                        'required_documents' => $course->required_documents ?: self::REQUIRED_DOCUMENTS,
                        'price_cents' => $priceCents,
                        'old_price_cents' => $oldPriceCents,
                        'price' => $course->price ?: $priceCents / 100,
                        'old_price' => $course->old_price ?: $oldPriceCents / 100,
                        'currency' => $course->currency ?: 'EUR',
                        'canonical_url' => $course->canonical_url ?: url('/courses/'.$course->slug),
                        'open_graph_image' => $course->open_graph_image ?: self::IMAGE_PATH,
                        'og_image' => $course->og_image ?: self::IMAGE_PATH,
                        'image_path' => $course->image_path ?: self::IMAGE_PATH,
                        'icon' => $course->icon ?: 'car-front',
                        'structured_data' => $course->structured_data ?: [
                            'type' => 'Course',
                            'provider' => 'DrivePro Academy',
                            'courseMode' => $course->format ?: 'hybrid',
                            'inLanguage' => self::LOCALES,
                        ],
                        'course_category_id' => $course->course_category_id ?: $fallbackCategory->id,
                    ])->save();
                }
            });
    }

    /**
     * @param  array<string, mixed>  $current
     * @param  array<string, string>  $fallback
     * @return array<string, string>
     */
    private function completeTranslations(array $current, array $fallback): array
    {
        $translations = [];

        foreach (self::LOCALES as $locale) {
            $value = trim((string) ($current[$locale] ?? ''));
            $translations[$locale] = $value !== '' ? $value : $fallback[$locale];
        }

        return $translations;
    }

    /**
     * @return array<string, string>
     */
    private function translationsFromValue(string $value): array
    {
        return [
            'ru' => $value,
            'en' => $value,
            'lt' => $value,
            'pl' => $value,
        ];
    }
}

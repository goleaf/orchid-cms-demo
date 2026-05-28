<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\CourseCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    protected $model = Course::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $titleTranslations = $this->translations('Категория B Стандарт', 'Category B Standard', 'B kategorija standartas', 'Kategoria B Standard');
        $shortDescriptionTranslations = $this->translations('Базовый курс автошколы.', 'Core driving school course.', 'Pagrindinis vairavimo kursas.', 'Podstawowy kurs jazdy.');
        $descriptionTranslations = $this->translations('Курс включает теорию, практику и подготовку к экзамену.', 'The course includes theory, practice, and exam preparation.', 'Kursas apima teorija, praktika ir pasirengima egzaminui.', 'Kurs obejmuje teorie, praktyke i przygotowanie do egzaminu.');
        $programSummaryTranslations = $this->translations('Теория, практика и сопровождение до экзамена.', 'Theory, practice, and support up to the exam.', 'Teorija, praktika ir pagalba iki egzamino.', 'Teoria, praktyka i wsparcie do egzaminu.');
        $requirementsTranslations = $this->translations('Нужны возрастное соответствие, медсправка и документы.', 'Age eligibility, medical certificate, and documents are required.', 'Reikia tinkamo amziaus, medicinines pazymos ir dokumentu.', 'Wymagany jest odpowiedni wiek, zaswiadczenie lekarskie i dokumenty.');
        $includedTranslations = $this->translations('Теория, практика, материалы и консультация менеджера.', 'Theory, practice, materials, and manager consultation.', 'Teorija, praktika, medziaga ir vadybininko konsultacija.', 'Teoria, praktyka, materialy i konsultacja menedzera.');
        $extraCostTranslations = $this->translations('Госэкзамены и медсправка оплачиваются отдельно.', 'State exams and medical certificate are paid separately.', 'Valstybiniai egzaminai ir medicinine pazyma apmokami atskirai.', 'Egzaminy panstwowe i zaswiadczenie lekarskie sa platne osobno.');
        $theoryProgramTranslations = $this->translations('Правила дорожного движения, безопасность и подготовка к тесту.', 'Traffic rules, safety, and test preparation.', 'Keliu eismo taisykles, saugumas ir pasirengimas testui.', 'Przepisy ruchu, bezpieczenstwo i przygotowanie do testu.');
        $practiceProgramTranslations = $this->translations('Управление автомобилем, городские маршруты и экзаменационные упражнения.', 'Vehicle control, city routes, and exam maneuvers.', 'Automobilio valdymas, miesto marsrutai ir egzamino pratimai.', 'Prowadzenie auta, trasy miejskie i manewry egzaminacyjne.');
        $priceCents = $this->faker->numberBetween(80_000, 160_000);

        return [
            'uuid' => (string) Str::uuid(),
            'course_category_id' => CourseCategory::factory(),
            'code' => strtoupper($this->faker->unique()->bothify('COURSE-####')),
            'title' => $titleTranslations['en'],
            'title_translations' => $titleTranslations,
            'name_translations' => $titleTranslations,
            'slug' => $this->faker->unique()->slug(3),
            'license_category' => 'B',
            'transmission' => $this->faker->randomElement(['manual', 'automatic']),
            'theory_hours' => $this->faker->numberBetween(20, 45),
            'practice_hours' => $this->faker->numberBetween(10, 35),
            'duration_weeks' => $this->faker->numberBetween(6, 16),
            'duration_translations' => $this->translations('8 недель', '8 weeks', '8 savaites', '8 tygodni'),
            'format' => $this->faker->randomElement(['offline', 'online', 'hybrid', 'individual', 'group']),
            'available_languages' => ['ru', 'en', 'lt', 'pl'],
            'price_cents' => $priceCents,
            'old_price_cents' => null,
            'price' => $priceCents / 100,
            'old_price' => null,
            'currency' => 'EUR',
            'description' => $descriptionTranslations['en'],
            'short_description' => $shortDescriptionTranslations['en'],
            'short_description_translations' => $shortDescriptionTranslations,
            'description_translations' => $descriptionTranslations,
            'program_summary_translations' => $programSummaryTranslations,
            'required_documents' => ['ID card', 'Medical certificate'],
            'admission_requirements' => $requirementsTranslations['en'],
            'requirements_translations' => $requirementsTranslations,
            'included_items' => $includedTranslations['en'],
            'included_items_translations' => $includedTranslations,
            'includes_translations' => $includedTranslations,
            'extra_costs' => $extraCostTranslations['en'],
            'extra_costs_translations' => $extraCostTranslations,
            'excludes_translations' => $extraCostTranslations,
            'theory_program' => $theoryProgramTranslations['en'],
            'theory_program_translations' => $theoryProgramTranslations,
            'practice_program' => $practiceProgramTranslations['en'],
            'practice_program_translations' => $practiceProgramTranslations,
            'is_active' => true,
            'is_visible_on_site' => true,
            'is_indexable' => true,
            'is_featured' => false,
            'seo_title' => null,
            'seo_title_translations' => null,
            'meta_description' => null,
            'seo_description_translations' => null,
            'canonical_url' => null,
            'open_graph_image' => null,
            'og_image' => null,
            'image_path' => null,
            'icon' => null,
            'og_title' => null,
            'og_title_translations' => null,
            'og_description' => null,
            'og_description_translations' => null,
            'structured_data' => null,
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

    public function featured(): static
    {
        return $this->state(fn (): array => ['is_featured' => true]);
    }

    public function translated(): static
    {
        return $this->state(fn (): array => [
            'title' => 'Category B Standard',
            'title_translations' => $this->translations('Категория B Стандарт', 'Category B Standard', 'B kategorija standartas', 'Kategoria B Standard'),
            'name_translations' => $this->translations('Категория B Стандарт', 'Category B Standard', 'B kategorija standartas', 'Kategoria B Standard'),
            'short_description_translations' => $this->translations('Базовый курс автошколы.', 'Core driving school course.', 'Pagrindinis vairavimo kursas.', 'Podstawowy kurs jazdy.'),
            'description_translations' => $this->translations('Курс включает теорию, практику и подготовку к экзамену.', 'The course includes theory, practice, and exam preparation.', 'Kursas apima teorija, praktika ir pasirengima egzaminui.', 'Kurs obejmuje teorie, praktyke i przygotowanie do egzaminu.'),
        ]);
    }

    public function categoryB(): static
    {
        return $this->state(fn (): array => [
            'course_category_id' => CourseCategory::factory()->categoryB(),
            'code' => 'CATEGORY_B_MANUAL',
            'slug' => 'category-b-manual',
            'license_category' => 'B',
            'title' => 'Category B Manual',
            'title_translations' => $this->translations('Категория B', 'Category B', 'B kategorija', 'Kategoria B'),
            'name_translations' => $this->translations('Категория B', 'Category B', 'B kategorija', 'Kategoria B'),
        ]);
    }

    public function categoryA(): static
    {
        return $this->state(fn (): array => [
            'course_category_id' => CourseCategory::factory()->categoryA(),
            'code' => 'CATEGORY_A_MOTORCYCLE',
            'slug' => 'category-a-motorcycle',
            'license_category' => 'A',
            'title' => 'Category A Motorcycle',
            'title_translations' => $this->translations('Категория A', 'Category A', 'A kategorija', 'Kategoria A'),
            'name_translations' => $this->translations('Категория A', 'Category A', 'A kategorija', 'Kategoria A'),
        ]);
    }

    public function individualLessons(): static
    {
        return $this->state(fn (): array => [
            'course_category_id' => CourseCategory::factory()->individualLessons(),
            'code' => 'INDIVIDUAL_LESSONS',
            'slug' => 'individual-driving-lessons',
            'format' => 'individual',
            'title' => 'Individual driving lessons',
            'title_translations' => $this->translations('Индивидуальные уроки вождения', 'Individual driving lessons', 'Individualios vairavimo pamokos', 'Indywidualne lekcje jazdy'),
            'name_translations' => $this->translations('Индивидуальные уроки вождения', 'Individual driving lessons', 'Individualios vairavimo pamokos', 'Indywidualne lekcje jazdy'),
        ]);
    }

    public function examPreparation(): static
    {
        return $this->state(fn (): array => [
            'course_category_id' => CourseCategory::factory()->examPreparation(),
            'code' => 'EXAM_PREPARATION',
            'slug' => 'exam-preparation',
            'title' => 'Exam preparation',
            'title_translations' => $this->translations('Подготовка к экзамену', 'Exam preparation', 'Pasiruosimas egzaminui', 'Przygotowanie do egzaminu'),
            'name_translations' => $this->translations('Подготовка к экзамену', 'Exam preparation', 'Pasiruosimas egzaminui', 'Przygotowanie do egzaminu'),
        ]);
    }

    public function withPrice(float|int $price = 1290): static
    {
        return $this->state(fn (): array => [
            'price' => $price,
            'price_cents' => (int) round(((float) $price) * 100),
            'currency' => 'EUR',
        ]);
    }

    public function withOldPrice(float|int $oldPrice = 1490): static
    {
        return $this->state(fn (): array => [
            'old_price' => $oldPrice,
            'old_price_cents' => (int) round(((float) $oldPrice) * 100),
        ]);
    }

    public function online(): static
    {
        return $this->state(fn (): array => ['format' => 'online']);
    }

    public function offline(): static
    {
        return $this->state(fn (): array => ['format' => 'offline']);
    }

    public function hybrid(): static
    {
        return $this->state(fn (): array => ['format' => 'hybrid']);
    }

    public function group(): static
    {
        return $this->state(fn (): array => ['format' => 'group']);
    }

    public function individual(): static
    {
        return $this->state(fn (): array => ['format' => 'individual']);
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

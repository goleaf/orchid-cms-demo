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

        return [
            'uuid' => (string) Str::uuid(),
            'code' => $code,
            'slug' => $code,
            ...$this->genericContent(),
            'image' => 'images/driving-school-hero.png',
            'icon' => 'car-front',
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
        return $this->state(fn (): array => $this->genericContent());
    }

    public function categoryB(): static
    {
        return $this->state(fn (): array => [
            'code' => 'category_b',
            'slug' => 'category-b',
            'icon' => 'car-front',
            'sort_order' => 10,
            ...$this->contentState(
                $this->translations('Категория B', 'Category B', 'B kategorija', 'Kategoria B'),
                $this->translations('Полные курсы для легкового автомобиля.', 'Complete passenger car courses.', 'Pilni lengvojo automobilio kursai.', 'Pelne kursy samochodu osobowego.'),
                $this->translations('Категория B объединяет основные программы автошколы: теорию, практику, группы, документы и подготовку к экзамену.', 'Category B covers the main driving school programs: theory, practice, groups, documents, and exam preparation.', 'B kategorija apima pagrindines vairavimo mokyklos programas: teorija, praktika, grupes, dokumentus ir egzamina.', 'Kategoria B obejmuje glowne programy szkoly jazdy: teorie, praktyke, grupy, dokumenty i egzamin.'),
                $this->translations('Курсы категории B в Вильнюсе', 'Category B courses in Vilnius', 'B kategorijos kursai Vilniuje', 'Kursy kategorii B w Wilnie'),
                $this->translations('Курсы категории B с теорией, практикой, ближайшими группами и подготовкой к экзамену.', 'Category B courses with theory, practice, upcoming groups, and exam preparation.', 'B kategorijos kursai su teorija, praktika, artimiausiomis grupemis ir pasirengimu egzaminui.', 'Kursy kategorii B z teoria, praktyka, najblizszymi grupami i przygotowaniem do egzaminu.'),
            ),
        ]);
    }

    public function categoryA(): static
    {
        return $this->state(fn (): array => [
            'code' => 'category_a',
            'slug' => 'category-a',
            'icon' => 'bike',
            'sort_order' => 20,
            ...$this->contentState(
                $this->translations('Категория A', 'Category A', 'A kategorija', 'Kategoria A'),
                $this->translations('Курсы для будущих мотоциклистов.', 'Courses for future motorcycle riders.', 'Kursai busimiems motociklininkams.', 'Kursy dla przyszlych motocyklistow.'),
                $this->translations('Категория A включает теорию, площадку, городские маршруты и подготовку к экзаменационным упражнениям.', 'Category A includes theory, closed-area practice, city routes, and preparation for exam maneuvers.', 'A kategorija apima teorija, aikstele, miesto marsrutus ir pasirengima egzamino pratimams.', 'Kategoria A obejmuje teorie, plac manewrowy, trasy miejskie i przygotowanie do manewrow egzaminacyjnych.'),
                $this->translations('Курсы категории A', 'Category A motorcycle courses', 'A kategorijos motociklo kursai', 'Kursy motocyklowe kategorii A'),
                $this->translations('Обучение на категорию A с теорией, площадкой, городом и сопровождением до экзамена.', 'Category A training with theory, closed-area practice, city riding, and support until the exam.', 'A kategorijos mokymas su teorija, aikstele, miestu ir pagalba iki egzamino.', 'Szkolenie kategorii A z teoria, placem, miastem i wsparciem do egzaminu.'),
            ),
        ]);
    }

    public function individualLessons(): static
    {
        return $this->state(fn (): array => [
            'code' => 'individual_lessons',
            'slug' => 'individual-lessons',
            'icon' => 'user-round-check',
            'sort_order' => 30,
            ...$this->contentState(
                $this->translations('Индивидуальные уроки', 'Individual lessons', 'Individualios pamokos', 'Lekcje indywidualne'),
                $this->translations('Персональные занятия с инструктором.', 'Personal lessons with an instructor.', 'Asmenines pamokos su instruktoriumi.', 'Indywidualne lekcje z instruktorem.'),
                $this->translations('Индивидуальные уроки подходят для парковки, сложных маршрутов, восстановления уверенности и подготовки к экзамену.', 'Individual lessons help with parking, difficult routes, confidence recovery, and exam preparation.', 'Individualios pamokos padeda parkavimui, sudetingiems marsrutams, pasitikejimo atkurimui ir egzaminui.', 'Indywidualne lekcje pomagaja w parkowaniu, trudnych trasach, odzyskaniu pewnosci i egzaminie.'),
                $this->translations('Индивидуальные уроки вождения', 'Individual driving lessons', 'Individualios vairavimo pamokos', 'Indywidualne lekcje jazdy'),
                $this->translations('Персональные уроки вождения с инструктором для практики, маршрутов, парковки и экзамена.', 'Personal driving lessons with an instructor for practice, routes, parking, and the exam.', 'Asmenines vairavimo pamokos su instruktoriumi praktikai, marsrutams, parkavimui ir egzaminui.', 'Indywidualne lekcje jazdy z instruktorem do praktyki, tras, parkowania i egzaminu.'),
            ),
        ]);
    }

    public function examPreparation(): static
    {
        return $this->state(fn (): array => [
            'code' => 'exam_preparation',
            'slug' => 'exam-preparation',
            'icon' => 'clipboard-check',
            'sort_order' => 40,
            ...$this->contentState(
                $this->translations('Подготовка к экзамену', 'Exam preparation', 'Pasiruošimas egzaminui', 'Przygotowanie do egzaminu'),
                $this->translations('Занятия перед теоретическим или практическим экзаменом.', 'Lessons before the theory or practical exam.', 'Pamokos pries teorijos arba praktikos egzamina.', 'Zajecia przed egzaminem teoretycznym albo praktycznym.'),
                $this->translations('Подготовка к экзамену помогает повторить слабые темы, отработать маршруты, ошибки и уверенную сдачу.', 'Exam preparation helps review weak topics, practice routes, mistakes, and confident exam performance.', 'Pasiruosimas egzaminui padeda pakartoti silpnas temas, marsrutus, klaidas ir uztikrintai laikyti egzamina.', 'Przygotowanie do egzaminu pomaga powtorzyc slabe tematy, trasy, bledy i pewne podejscie.'),
                $this->translations('Подготовка к экзамену по вождению', 'Driving exam preparation', 'Pasiruošimas vairavimo egzaminui', 'Przygotowanie do egzaminu na prawo jazdy'),
                $this->translations('Подготовка к экзамену с инструктором: теория, маршруты, разбор ошибок и практические упражнения.', 'Exam preparation with an instructor: theory, routes, mistake review, and practical exercises.', 'Pasiruosimas egzaminui su instruktoriumi: teorija, marsrutai, klaidu analize ir praktiniai pratimai.', 'Przygotowanie do egzaminu z instruktorem: teoria, trasy, analiza bledow i cwiczenia praktyczne.'),
            ),
        ]);
    }

    public function skillRecovery(): static
    {
        return $this->state(fn (): array => [
            'code' => 'skill_recovery',
            'slug' => 'skill-recovery',
            'icon' => 'refresh-cw',
            'sort_order' => 50,
            ...$this->contentState(
                $this->translations('Восстановление навыков', 'Skill recovery', 'Įgūdžių atkūrimas', 'Odświeżenie umiejętności'),
                $this->translations('Практика для водителей после перерыва.', 'Practice for drivers after a break.', 'Praktika vairuotojams po pertraukos.', 'Praktyka dla kierowcow po przerwie.'),
                $this->translations('Восстановление навыков помогает спокойно вернуться за руль, повторить город, парковку и сложные дорожные ситуации.', 'Skill recovery helps return to driving calmly and review city driving, parking, and difficult road situations.', 'Igudziu atkurimas padeda ramiai grizti prie vairo, pakartoti miesta, parkavima ir sudetingas eismo situacijas.', 'Odswiezenie umiejetnosci pomaga spokojnie wrocic za kierownice, powtorzyc miasto, parkowanie i trudne sytuacje.'),
                $this->translations('Восстановление навыков вождения', 'Driving skill recovery', 'Vairavimo igudziu atkurimas', 'Odświeżenie umiejętności jazdy'),
                $this->translations('Индивидуальная практика после перерыва в вождении: город, парковка, перестроения и уверенность.', 'Individual practice after a driving break: city routes, parking, lane changes, and confidence.', 'Individuali praktika po vairavimo pertraukos: miestas, parkavimas, persirikiavimas ir pasitikejimas.', 'Indywidualna praktyka po przerwie: miasto, parkowanie, zmiana pasa i pewnosc jazdy.'),
            ),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function genericContent(): array
    {
        return $this->contentState(
            $this->translations('Категория курса', 'Course category', 'Kurso kategorija', 'Kategoria kursu'),
            $this->translations('Курсы автошколы.', 'Driving school courses.', 'Vairavimo kursai.', 'Kursy jazdy.'),
            $this->translations('Категория публичных курсов автошколы для группировки программ, цен и заявок.', 'Public driving school course category for grouping programs, prices, and applications.', 'Vairavimo mokyklos kursu kategorija programoms, kainoms ir paraiskoms grupuoti.', 'Kategoria kursow szkoly jazdy do grupowania programow, cen i zgloszen.'),
            $this->translations('Категория курсов автошколы', 'Driving school course category', 'Vairavimo mokyklos kursu kategorija', 'Kategoria kursow szkoly jazdy'),
            $this->translations('Категории курсов автошколы с программами, ценами, группами и заявками.', 'Driving school course categories with programs, prices, groups, and applications.', 'Vairavimo mokyklos kursu kategorijos su programomis, kainomis, grupemis ir paraiskomis.', 'Kategorie kursow szkoly jazdy z programami, cenami, grupami i zgloszeniami.'),
        );
    }

    /**
     * @param  array<string, string>  $name
     * @param  array<string, string>  $shortDescription
     * @param  array<string, string>  $description
     * @param  array<string, string>  $seoTitle
     * @param  array<string, string>  $seoDescription
     * @return array<string, mixed>
     */
    private function contentState(
        array $name,
        array $shortDescription,
        array $description,
        array $seoTitle,
        array $seoDescription,
    ): array {
        return [
            'name_translations' => $name,
            'short_description_translations' => $shortDescription,
            'description_translations' => $description,
            'seo_title_translations' => $seoTitle,
            'seo_description_translations' => $seoDescription,
            'image' => 'images/driving-school-hero.png',
        ];
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

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
        $slug = $this->faker->unique()->slug(3);
        $priceCents = $this->faker->numberBetween(80_000, 160_000);

        return [
            'uuid' => (string) Str::uuid(),
            'course_category_id' => CourseCategory::factory(),
            'code' => strtoupper($this->faker->unique()->bothify('COURSE-####')),
            'slug' => $slug,
            'license_category' => 'B',
            'transmission' => $this->faker->randomElement(['manual', 'automatic']),
            'theory_hours' => $this->faker->numberBetween(20, 45),
            'practice_hours' => $this->faker->numberBetween(10, 35),
            'duration_weeks' => $this->faker->numberBetween(6, 16),
            'format' => $this->faker->randomElement(['offline', 'online', 'hybrid', 'individual', 'group']),
            'available_languages' => ['ru', 'en', 'lt', 'pl'],
            'price_cents' => $priceCents,
            'old_price_cents' => null,
            'price' => $priceCents / 100,
            'old_price' => null,
            'currency' => 'EUR',
            'required_documents' => ['ID card', 'Medical certificate'],
            'is_active' => true,
            'is_visible_on_site' => true,
            'is_indexable' => true,
            'is_featured' => false,
            'canonical_url' => url('/courses/'.$slug),
            'open_graph_image' => 'images/driving-school-hero.png',
            'og_image' => 'images/driving-school-hero.png',
            'image_path' => 'images/driving-school-hero.png',
            'icon' => 'car-front',
            'structured_data' => ['type' => 'Course', 'provider' => 'DrivePro Academy'],
            'sort_order' => 0,
            'created_by_id' => null,
            'updated_by_id' => null,
            ...$this->categoryBContent(),
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

    /**
     * @param  array<string, string>  $translations
     */
    public function publicCatalog(array $translations): static
    {
        $title = $this->legacyTranslations($translations, 'title');
        $shortDescription = $this->legacyTranslations($translations, 'short_description');
        $description = $this->legacyTranslations($translations, 'description');
        $included = $this->legacyTranslations($translations, 'included_items');
        $extraCosts = $this->legacyTranslations($translations, 'extra_costs');
        $theory = $this->legacyTranslations($translations, 'theory_program');
        $practice = $this->legacyTranslations($translations, 'practice_program');
        $summary = $this->legacyTranslations($translations, 'program_summary', $description);
        $requirements = $this->legacyTranslations($translations, 'requirements', $shortDescription);
        $duration = $this->legacyTranslations($translations, 'duration', $this->translations('8 недель', '8 weeks', '8 savaitės', '8 tygodni'));
        $seoTitle = $this->legacyTranslations($translations, 'seo_title', $title);
        $seoDescription = $this->legacyTranslations($translations, 'seo_description', $shortDescription);

        return $this->state(fn (): array => $this->contentState(
            $title,
            $shortDescription,
            $description,
            $summary,
            $requirements,
            $included,
            $extraCosts,
            $duration,
            $theory,
            $practice,
            $seoTitle,
            $seoDescription,
        ));
    }

    public function translated(): static
    {
        return $this->state(fn (): array => $this->categoryBContent());
    }

    public function categoryB(): static
    {
        return $this->state(fn (): array => [
            'course_category_id' => CourseCategory::factory()->categoryB(),
            'code' => 'CATEGORY_B_MANUAL',
            'slug' => 'category-b-manual',
            'license_category' => 'B',
            'canonical_url' => url('/courses/category-b-manual'),
            ...$this->categoryBContent(),
        ]);
    }

    public function categoryA(): static
    {
        return $this->state(fn (): array => [
            'course_category_id' => CourseCategory::factory()->categoryA(),
            'code' => 'CATEGORY_A_MOTORCYCLE',
            'slug' => 'category-a-motorcycle',
            'license_category' => 'A',
            'transmission' => 'manual',
            'canonical_url' => url('/courses/category-a-motorcycle'),
            ...$this->categoryAContent(),
        ]);
    }

    public function individualLessons(): static
    {
        return $this->state(fn (): array => [
            'course_category_id' => CourseCategory::factory()->individualLessons(),
            'code' => 'INDIVIDUAL_LESSONS',
            'slug' => 'individual-driving-lessons',
            'format' => 'individual',
            'canonical_url' => url('/courses/individual-driving-lessons'),
            ...$this->individualLessonsContent(),
        ]);
    }

    public function examPreparation(): static
    {
        return $this->state(fn (): array => [
            'course_category_id' => CourseCategory::factory()->examPreparation(),
            'code' => 'EXAM_PREPARATION',
            'slug' => 'exam-preparation',
            'canonical_url' => url('/courses/exam-preparation'),
            ...$this->examPreparationContent(),
        ]);
    }

    public function skillRecovery(): static
    {
        return $this->state(fn (): array => [
            'course_category_id' => CourseCategory::factory()->skillRecovery(),
            'code' => 'SKILL_RECOVERY',
            'slug' => 'skill-recovery',
            'format' => 'individual',
            'canonical_url' => url('/courses/skill-recovery'),
            ...$this->skillRecoveryContent(),
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
     * @return array<string, mixed>
     */
    private function categoryBContent(): array
    {
        return $this->contentState(
            $this->translations('Категория B', 'Category B', 'B kategorija', 'Kategoria B'),
            $this->translations('Курс категории B с теорией, практикой и сопровождением.', 'Category B course with theory, practice, and support.', 'B kategorijos kursas su teorija, praktika ir pagalba.', 'Kurs kategorii B z teorią, praktyką i wsparciem.'),
            $this->translations('Полный курс для будущих водителей категории B: теория, практика с инструктором, документы и подготовка к экзамену.', 'Complete course for future Category B drivers: theory, instructor-led practice, documents, and exam preparation.', 'Pilnas kursas būsimiems B kategorijos vairuotojams: teorija, praktika su instruktoriumi, dokumentai ir egzaminas.', 'Pełny kurs dla przyszłych kierowców kategorii B: teoria, praktyka z instruktorem, dokumenty i egzamin.'),
            $this->translations('Учебный путь от первой заявки до практического экзамена.', 'Training path from first application to the practical exam.', 'Mokymo kelias nuo pirmos paraiškos iki praktikos egzamino.', 'Ścieżka szkolenia od pierwszego zgłoszenia do egzaminu praktycznego.'),
            $this->translations('Нужны возрастное соответствие, медсправка и документы ученика.', 'Age eligibility, medical certificate, and student documents are required.', 'Reikia tinkamo amžiaus, medicininės pažymos ir mokinio dokumentų.', 'Wymagany jest odpowiedni wiek, zaświadczenie lekarskie i dokumenty ucznia.'),
            $this->translations('Теория, практика, учебные материалы, сопровождение заявки и консультация менеджера.', 'Theory, practice, study materials, application support, and manager consultation.', 'Teorija, praktika, mokomoji medžiaga, paraiškos palyda ir vadybininko konsultacija.', 'Teoria, praktyka, materiały, obsługa zgłoszenia i konsultacja menedżera.'),
            $this->translations('Госэкзамены, медсправка и дополнительные часы оплачиваются отдельно.', 'State exams, medical certificate, and extra hours are paid separately.', 'Valstybiniai egzaminai, medicininė pažyma ir papildomos valandos apmokami atskirai.', 'Egzaminy państwowe, zaświadczenie lekarskie i dodatkowe godziny są płatne osobno.'),
            $this->translations('14 недель', '14 weeks', '14 savaičių', '14 tygodni'),
            $this->translations('ПДД, безопасность, дорожные ситуации и подготовка к теоретическому тесту.', 'Traffic rules, safety, road situations, and theory test preparation.', 'Kelių eismo taisyklės, sauga, eismo situacijos ir teorijos testo pasiruošimas.', 'Przepisy, bezpieczeństwo, sytuacje drogowe i przygotowanie do testu teoretycznego.'),
            $this->translations('Вождение в городе, парковка, маршруты, манёвры и подготовка к экзамену.', 'City driving, parking, routes, maneuvers, and practical exam preparation.', 'Vairavimas mieste, parkavimas, maršrutai, manevrai ir pasiruošimas egzaminui.', 'Jazda miejska, parkowanie, trasy, manewry i przygotowanie do egzaminu.'),
            $this->translations('Курс категории B в Вильнюсе', 'Category B driving course in Vilnius', 'B kategorijos vairavimo kursas Vilniuje', 'Kurs prawa jazdy kategorii B w Wilnie'),
            $this->translations('Курс категории B с теорией, практикой, документами, группами и подготовкой к экзамену.', 'Category B course with theory, practice, documents, groups, and exam preparation.', 'B kategorijos kursas su teorija, praktika, dokumentais, grupėmis ir pasiruošimu egzaminui.', 'Kurs kategorii B z teorią, praktyką, dokumentami, grupami i przygotowaniem do egzaminu.'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function categoryAContent(): array
    {
        return $this->contentState(
            $this->translations('Категория A', 'Category A', 'A kategorija', 'Kategoria A'),
            $this->translations('Курс для будущих мотоциклистов.', 'Course for future motorcycle riders.', 'Kursas būsimiems motociklininkams.', 'Kurs dla przyszłych motocyklistów.'),
            $this->translations('Теория, площадка и городская практика для уверенной подготовки к экзамену категории A.', 'Theory, closed-area practice, and city riding for confident Category A exam preparation.', 'Teorija, aikštelė ir miesto praktika patikimam pasiruošimui A kategorijos egzaminui.', 'Teoria, plac manewrowy i jazda miejska dla pewnego przygotowania do egzaminu kategorii A.'),
            $this->translations('Подготовка к управлению мотоциклом в городе и на экзамене.', 'Preparation for motorcycle riding in traffic and at the exam.', 'Pasiruošimas vairuoti motociklą mieste ir egzamino metu.', 'Przygotowanie do jazdy motocyklem w mieście i na egzaminie.'),
            $this->translations('Нужны возрастное соответствие, медсправка и защитная экипировка.', 'Age eligibility, medical certificate, and protective gear are required.', 'Reikia tinkamo amžiaus, medicininės pažymos ir apsauginės aprangos.', 'Wymagany jest odpowiedni wiek, zaświadczenie lekarskie i odzież ochronna.'),
            $this->translations('Теория, площадка, городские маршруты и консультация инструктора.', 'Theory, closed-area practice, city routes, and instructor consultation.', 'Teorija, aikštelė, miesto maršrutai ir instruktoriaus konsultacija.', 'Teoria, plac manewrowy, trasy miejskie i konsultacja instruktora.'),
            $this->translations('Экипировка, госэкзамены и дополнительные занятия оплачиваются отдельно.', 'Gear, state exams, and extra lessons are paid separately.', 'Apranga, valstybiniai egzaminai ir papildomos pamokos apmokami atskirai.', 'Wyposażenie, egzaminy państwowe i dodatkowe lekcje są płatne osobno.'),
            $this->translations('6 недель', '6 weeks', '6 savaitės', '6 tygodni'),
            $this->translations('ПДД, безопасность мотоциклиста и экзаменационные темы.', 'Traffic rules, rider safety, and exam topics.', 'Kelių eismo taisyklės, motociklininko sauga ir egzamino temos.', 'Przepisy ruchu, bezpieczeństwo motocyklisty i tematy egzaminacyjne.'),
            $this->translations('Площадка, манёвры, городские маршруты и экзаменационная траектория.', 'Closed-area maneuvers, city routes, and exam route practice.', 'Aikštelės manevrai, miesto maršrutai ir egzamino maršruto praktika.', 'Manewry na placu, trasy miejskie i ćwiczenie trasy egzaminacyjnej.'),
            $this->translations('Курс категории A в автошколе', 'Category A motorcycle course', 'A kategorijos motociklo kursas', 'Kurs motocyklowy kategorii A'),
            $this->translations('Подготовка к категории A с теорией, площадкой, городом и экзаменационными упражнениями.', 'Category A training with theory, closed-area practice, city riding, and exam maneuvers.', 'A kategorijos mokymas su teorija, aikštele, miestu ir egzamino pratimais.', 'Szkolenie kategorii A z teorią, placem, miastem i manewrami egzaminacyjnymi.'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function individualLessonsContent(): array
    {
        return $this->contentState(
            $this->translations('Индивидуальные уроки вождения', 'Individual driving lessons', 'Individualios vairavimo pamokos', 'Indywidualne lekcje jazdy'),
            $this->translations('Персональные занятия с инструктором под текущую цель.', 'Personal instructor-led lessons for the current goal.', 'Asmeninės pamokos su instruktoriumi pagal dabartinį tikslą.', 'Indywidualne jazdy z instruktorem pod aktualny cel.'),
            $this->translations('Индивидуальные уроки подходят для первой практики, сложных маршрутов, парковки, восстановления уверенности и подготовки к экзамену.', 'Individual lessons are suitable for first practice, difficult routes, parking, confidence recovery, and exam preparation.', 'Individualios pamokos tinka pirmai praktikai, sudėtingiems maršrutams, parkavimui, pasitikėjimo atkūrimui ir egzaminui.', 'Lekcje indywidualne pomagają przy pierwszej praktyce, trudnych trasach, parkowaniu, odzyskaniu pewności i egzaminie.'),
            $this->translations('Занятия строятся вокруг навыков ученика и рекомендаций инструктора.', 'Lessons are built around student skills and instructor recommendations.', 'Pamokos sudaromos pagal mokinio įgūdžius ir instruktoriaus rekomendacijas.', 'Zajęcia są prowadzone według umiejętności ucznia i zaleceń instruktora.'),
            $this->translations('Нужны документы ученика и согласованная цель занятия.', 'Student documents and an agreed lesson goal are required.', 'Reikia mokinio dokumentų ir suderinto pamokos tikslo.', 'Wymagane są dokumenty ucznia i ustalony cel jazdy.'),
            $this->translations('Индивидуальное время инструктора, маршрут и обратная связь после занятия.', 'Instructor time, route planning, and feedback after the lesson.', 'Instruktoriaus laikas, maršruto planavimas ir grįžtamasis ryšys po pamokos.', 'Czas instruktora, plan trasy i informacja zwrotna po lekcji.'),
            $this->translations('Аренда экзаменационного автомобиля и дополнительные часы оплачиваются отдельно.', 'Exam vehicle rental and extra hours are paid separately.', 'Egzamino automobilio nuoma ir papildomos valandos apmokamos atskirai.', 'Wynajem auta egzaminacyjnego i dodatkowe godziny są płatne osobno.'),
            $this->translations('По договорённости', 'By agreement', 'Pagal susitarimą', 'Do uzgodnienia'),
            $this->translations('Разбор правил, ошибок и сложных ситуаций перед практикой.', 'Review of rules, mistakes, and difficult situations before practice.', 'Taisyklių, klaidų ir sudėtingų situacijų aptarimas prieš praktiką.', 'Omówienie zasad, błędów i trudnych sytuacji przed jazdą.'),
            $this->translations('Маршруты, парковка, перекрёстки и упражнения под личную цель.', 'Routes, parking, intersections, and exercises for the personal goal.', 'Maršrutai, parkavimas, sankryžos ir pratimai pagal asmeninį tikslą.', 'Trasy, parkowanie, skrzyżowania i ćwiczenia pod osobisty cel.'),
            $this->translations('Индивидуальные уроки вождения', 'Individual driving lessons', 'Individualios vairavimo pamokos', 'Indywidualne lekcje jazdy'),
            $this->translations('Персональные уроки вождения с инструктором для практики, парковки, маршрутов и подготовки к экзамену.', 'Personal driving lessons with an instructor for practice, parking, routes, and exam preparation.', 'Asmeninės vairavimo pamokos su instruktoriumi praktikai, parkavimui, maršrutams ir egzaminui.', 'Indywidualne lekcje jazdy z instruktorem do praktyki, parkowania, tras i egzaminu.'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function examPreparationContent(): array
    {
        return $this->contentState(
            $this->translations('Подготовка к экзамену', 'Exam preparation', 'Pasiruošimas egzaminui', 'Przygotowanie do egzaminu'),
            $this->translations('Фокус на экзаменационных маршрутах, ошибках и уверенности.', 'Focus on exam routes, mistakes, and confidence.', 'Dėmesys egzamino maršrutams, klaidoms ir pasitikėjimui.', 'Skupienie na trasach egzaminacyjnych, błędach i pewności.'),
            $this->translations('Курс помогает повторить теорию, проверить слабые места, отработать городские маршруты и подготовиться к практическому экзамену.', 'The course helps review theory, find weak points, practice city routes, and prepare for the practical exam.', 'Kursas padeda pakartoti teoriją, rasti silpnas vietas, praktikuoti miesto maršrutus ir pasiruošti praktikos egzaminui.', 'Kurs pomaga powtórzyć teorię, znaleźć słabe miejsca, ćwiczyć trasy miejskie i przygotować się do egzaminu praktycznego.'),
            $this->translations('Подготовка строится вокруг экзаменационных требований и текущих ошибок ученика.', 'Preparation is built around exam requirements and current student mistakes.', 'Pasiruošimas sudaromas pagal egzamino reikalavimus ir mokinio klaidas.', 'Przygotowanie opiera się na wymaganiach egzaminu i aktualnych błędach ucznia.'),
            $this->translations('Нужна базовая подготовка и готовность к повторению теории и практики.', 'Basic preparation and readiness to review theory and practice are required.', 'Reikia bazinio pasiruošimo ir pasirengimo kartoti teoriją bei praktiką.', 'Wymagane jest podstawowe przygotowanie i gotowość do powtórki teorii oraz praktyki.'),
            $this->translations('Диагностика навыков, экзаменационные маршруты и рекомендации инструктора.', 'Skill check, exam routes, and instructor recommendations.', 'Įgūdžių patikra, egzamino maršrutai ir instruktoriaus rekomendacijos.', 'Diagnoza umiejętności, trasy egzaminacyjne i zalecenia instruktora.'),
            $this->translations('Госэкзамен, аренда автомобиля и дополнительные занятия оплачиваются отдельно.', 'State exam, vehicle rental, and extra lessons are paid separately.', 'Valstybinis egzaminas, automobilio nuoma ir papildomos pamokos apmokami atskirai.', 'Egzamin państwowy, wynajem auta i dodatkowe lekcje są płatne osobno.'),
            $this->translations('2 недели', '2 weeks', '2 savaitės', '2 tygodnie'),
            $this->translations('Повторение тем, типовые ошибки и тестовые задания.', 'Topic review, common mistakes, and practice tests.', 'Temų kartojimas, dažnos klaidos ir bandomieji testai.', 'Powtórka tematów, typowe błędy i testy próbne.'),
            $this->translations('Экзаменационные маршруты, парковка, развороты и спокойная езда.', 'Exam routes, parking, turns, and calm driving.', 'Egzamino maršrutai, parkavimas, apsisukimai ir rami vairavimo praktika.', 'Trasy egzaminacyjne, parkowanie, zawracanie i spokojna jazda.'),
            $this->translations('Подготовка к экзамену по вождению', 'Driving exam preparation', 'Pasiruošimas vairavimo egzaminui', 'Przygotowanie do egzaminu na prawo jazdy'),
            $this->translations('Подготовка к практическому экзамену с инструктором, маршрутами, разбором ошибок и повторением теории.', 'Practical exam preparation with an instructor, routes, mistake review, and theory refresh.', 'Praktinio egzamino pasiruošimas su instruktoriumi, maršrutais, klaidų analize ir teorijos kartojimu.', 'Przygotowanie do egzaminu praktycznego z instruktorem, trasami, analizą błędów i powtórką teorii.'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function skillRecoveryContent(): array
    {
        return $this->contentState(
            $this->translations('Восстановление навыков', 'Skill recovery', 'Įgūdžių atkūrimas', 'Odświeżenie umiejętności'),
            $this->translations('Практика для водителей после перерыва или неуверенности.', 'Practice for drivers after a break or loss of confidence.', 'Praktika vairuotojams po pertraukos arba praradus pasitikėjimą.', 'Praktyka dla kierowców po przerwie albo utracie pewności.'),
            $this->translations('Занятия помогают спокойно вернуться за руль, повторить городские ситуации, парковку, перестроения и движение в плотном трафике.', 'Lessons help return to driving calmly, review city situations, parking, lane changes, and dense traffic.', 'Pamokos padeda ramiai grįžti prie vairo, pakartoti miesto situacijas, parkavimą, persirikiavimą ir intensyvų eismą.', 'Zajęcia pomagają spokojnie wrócić za kierownicę, powtórzyć sytuacje miejskie, parkowanie, zmianę pasa i ruch w korkach.'),
            $this->translations('Маршрут и темп подбираются под опыт водителя и уровень уверенности.', 'The route and pace match the driver experience and confidence level.', 'Maršrutas ir tempas parenkami pagal vairuotojo patirtį ir pasitikėjimą.', 'Trasa i tempo są dobierane do doświadczenia i poziomu pewności kierowcy.'),
            $this->translations('Нужны действующие права или согласованный учебный статус.', 'A valid license or agreed training status is required.', 'Reikia galiojančio pažymėjimo arba suderinto mokymo statuso.', 'Wymagane jest ważne prawo jazdy albo uzgodniony status szkolenia.'),
            $this->translations('Индивидуальная практика, маршрут, обратная связь и план дальнейших шагов.', 'Individual practice, route, feedback, and a plan for next steps.', 'Individuali praktika, maršrutas, grįžtamasis ryšys ir tolesnių žingsnių planas.', 'Indywidualna praktyka, trasa, informacja zwrotna i plan kolejnych kroków.'),
            $this->translations('Дополнительные часы и специальные маршруты оплачиваются отдельно.', 'Extra hours and special routes are paid separately.', 'Papildomos valandos ir specialūs maršrutai apmokami atskirai.', 'Dodatkowe godziny i specjalne trasy są płatne osobno.'),
            $this->translations('По договорённости', 'By agreement', 'Pagal susitarimą', 'Do uzgodnienia'),
            $this->translations('Повторение правил, знаков и сложных дорожных ситуаций.', 'Review of rules, signs, and difficult road situations.', 'Taisyklių, ženklų ir sudėtingų eismo situacijų kartojimas.', 'Powtórka przepisów, znaków i trudnych sytuacji drogowych.'),
            $this->translations('Город, парковка, перестроения, круги и маршруты ученика.', 'City driving, parking, lane changes, roundabouts, and student routes.', 'Miestas, parkavimas, persirikiavimas, žiedai ir mokinio maršrutai.', 'Miasto, parkowanie, zmiana pasa, ronda i trasy ucznia.'),
            $this->translations('Восстановление навыков вождения', 'Driving skill recovery', 'Vairavimo įgūdžių atkūrimas', 'Odświeżenie umiejętności jazdy'),
            $this->translations('Индивидуальная практика для водителей после перерыва: город, парковка, перестроения и уверенность.', 'Individual practice after a driving break: city routes, parking, lane changes, and confidence.', 'Individuali praktika po vairavimo pertraukos: miestas, parkavimas, persirikiavimas ir pasitikėjimas.', 'Indywidualna praktyka po przerwie: miasto, parkowanie, zmiana pasa i pewność jazdy.'),
        );
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

    /**
     * @param  array<string, string>  $translations
     * @param  array<string, string>|null  $fallback
     * @return array<string, string>
     */
    private function legacyTranslations(array $translations, string $key, ?array $fallback = null): array
    {
        $ru = $translations[$key] ?? $fallback['ru'] ?? '';
        $en = $translations[$key.'_en'] ?? $fallback['en'] ?? $ru;

        return [
            'ru' => $ru,
            'en' => $en,
            'lt' => $translations[$key.'_lt'] ?? $fallback['lt'] ?? $en,
            'pl' => $translations[$key.'_pl'] ?? $fallback['pl'] ?? $en,
        ];
    }

    /**
     * @param  array<string, string>  $title
     * @param  array<string, string>  $shortDescription
     * @param  array<string, string>  $description
     * @param  array<string, string>  $summary
     * @param  array<string, string>  $requirements
     * @param  array<string, string>  $included
     * @param  array<string, string>  $extraCosts
     * @param  array<string, string>  $duration
     * @param  array<string, string>  $theory
     * @param  array<string, string>  $practice
     * @param  array<string, string>  $seoTitle
     * @param  array<string, string>  $seoDescription
     * @return array<string, mixed>
     */
    private function contentState(
        array $title,
        array $shortDescription,
        array $description,
        array $summary,
        array $requirements,
        array $included,
        array $extraCosts,
        array $duration,
        array $theory,
        array $practice,
        array $seoTitle,
        array $seoDescription,
    ): array {
        return [
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
            'theory_program' => $theory['en'],
            'theory_program_translations' => $theory,
            'practice_program' => $practice['en'],
            'practice_program_translations' => $practice,
            'seo_title' => $seoTitle['en'],
            'seo_title_translations' => $seoTitle,
            'meta_description' => $seoDescription['en'],
            'seo_description_translations' => $seoDescription,
            'og_title' => $seoTitle['en'],
            'og_title_translations' => $seoTitle,
            'og_description' => $seoDescription['en'],
            'og_description_translations' => $seoDescription,
        ];
    }
}

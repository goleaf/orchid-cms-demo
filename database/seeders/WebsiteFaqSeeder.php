<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Course;
use App\Models\Faq;
use App\Models\SitePage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WebsiteFaqSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            WebsitePageSeeder::class,
            WebsiteCourseSeeder::class,
            WebsiteBranchSeeder::class,
        ]);

        $this->seedGlobalFaqs();
        $this->seedCourseFaqs();
        $this->seedBranchFaqs();
        $this->seedPageFaqs();
    }

    private function seedGlobalFaqs(): void
    {
        foreach ([
            [
                'sort_order' => 10,
                'question' => ['ru' => 'Как оставить заявку?', 'en' => 'How do I send an application?', 'lt' => 'Kaip pateikti paraiska?', 'pl' => 'Jak wyslac zgloszenie?'],
                'answer' => ['ru' => 'Заполните форму на сайте, и заявка появится в CRM.', 'en' => 'Submit the website form and the lead will appear in CRM.', 'lt' => 'Uzpildykite forma svetaineje ir uzklausa atsiras CRM.', 'pl' => 'Wypelnij formularz, a zgloszenie pojawi sie w CRM.'],
            ],
            [
                'sort_order' => 20,
                'question' => ['ru' => 'На каких языках можно учиться?', 'en' => 'Which languages are available for training?', 'lt' => 'Kokiomis kalbomis galima mokytis?', 'pl' => 'W jakich jezykach mozna sie uczyc?'],
                'answer' => ['ru' => 'Доступность языка зависит от курса, группы и инструктора. Менеджер подтвердит вариант после заявки.', 'en' => 'Language availability depends on the course, group, and instructor. A manager will confirm the option after the application.', 'lt' => 'Kalba priklauso nuo kurso, grupes ir instruktoriaus. Vadybininkas patvirtins pasirinkima po paraiskos.', 'pl' => 'Dostepnosc jezyka zalezy od kursu, grupy i instruktora. Menedzer potwierdzi opcje po zgloszeniu.'],
            ],
            [
                'sort_order' => 30,
                'question' => ['ru' => 'Что происходит после отправки формы?', 'en' => 'What happens after I submit the form?', 'lt' => 'Kas vyksta issiuntus forma?', 'pl' => 'Co dzieje sie po wyslaniu formularza?'],
                'answer' => ['ru' => 'Менеджер получает заявку, проверяет курс, филиал, группу и связывается с учеником для подтверждения деталей.', 'en' => 'A manager receives the request, checks the course, branch, and group, then contacts the student to confirm details.', 'lt' => 'Vadybininkas gauna uzklausa, patikrina kursa, filiala ir grupe, tada susisiekia del detaliu.', 'pl' => 'Menedzer otrzymuje zgloszenie, sprawdza kurs, oddzial i grupe, a potem kontaktuje sie w celu potwierdzenia szczegolow.'],
            ],
        ] as $item) {
            $this->upsertFaq(null, null, $item['sort_order'], $item['question'], $item['answer']);
        }
    }

    private function seedCourseFaqs(): void
    {
        Course::query()
            ->active()
            ->visibleOnSite()
            ->ordered()
            ->get(['id', 'slug', 'title', 'title_translations', 'name_translations', 'duration_weeks', 'theory_hours', 'practice_hours'])
            ->each(function (Course $course): void {
                foreach ($this->courseFaqItems($course) as $item) {
                    $this->upsertFaq(Course::class, $course->id, $item['sort_order'], $item['question'], $item['answer']);
                }
            });
    }

    private function seedBranchFaqs(): void
    {
        Branch::query()
            ->active()
            ->visibleOnSite()
            ->ordered()
            ->get(['id', 'slug', 'name', 'name_translations', 'city', 'city_translations'])
            ->each(function (Branch $branch): void {
                foreach ($this->branchFaqItems() as $item) {
                    $this->upsertFaq(Branch::class, $branch->id, $item['sort_order'], $item['question'], $item['answer']);
                }
            });
    }

    private function seedPageFaqs(): void
    {
        SitePage::query()
            ->active()
            ->published()
            ->ordered()
            ->get(['id', 'slug', 'type', 'title_translations'])
            ->each(function (SitePage $page): void {
                foreach ($this->pageFaqItems() as $item) {
                    $this->upsertFaq(SitePage::class, $page->id, $item['sort_order'], $item['question'], $item['answer']);
                }
            });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function courseFaqItems(Course $course): array
    {
        $durationWeeks = max(1, (int) ($course->duration_weeks ?: 8));
        $theoryHours = max(0, (int) ($course->theory_hours ?: 0));
        $practiceHours = max(0, (int) ($course->practice_hours ?: 0));

        return [
            [
                'sort_order' => 10,
                'question' => ['ru' => 'Что входит в курс?', 'en' => 'What is included in the course?', 'lt' => 'Kas ieina i kursa?', 'pl' => 'Co obejmuje kurs?'],
                'answer' => ['ru' => 'В курс входят теория, практические занятия, учебные материалы, сопровождение заявки и консультация менеджера.', 'en' => 'The course includes theory, practical lessons, study materials, application support, and manager consultation.', 'lt' => 'Kursa sudaro teorija, praktines pamokos, mokomoji medziaga, paraiskos palyda ir vadybininko konsultacija.', 'pl' => 'Kurs obejmuje teorie, jazdy praktyczne, materialy, obsluge zgloszenia i konsultacje menedzera.'],
            ],
            [
                'sort_order' => 20,
                'question' => ['ru' => 'Можно ли выбрать группу?', 'en' => 'Can I choose a group?', 'lt' => 'Ar galiu pasirinkti grupe?', 'pl' => 'Czy moge wybrac grupe?'],
                'answer' => ['ru' => 'Да, менеджер поможет подобрать подходящую группу, филиал и время занятий для этого курса.', 'en' => 'Yes, a manager will help choose a suitable group, branch, and lesson time for this course.', 'lt' => 'Taip, vadybininkas pades pasirinkti tinkama grupe, filiala ir pamoku laika siam kursui.', 'pl' => 'Tak, menedzer pomoze wybrac odpowiednia grupe, oddzial i godziny zajec dla tego kursu.'],
            ],
            [
                'sort_order' => 30,
                'question' => ['ru' => 'Сколько длится обучение?', 'en' => 'How long does training take?', 'lt' => 'Kiek trunka mokymas?', 'pl' => 'Ile trwa szkolenie?'],
                'answer' => [
                    'ru' => "Ориентир: {$durationWeeks} недель, {$theoryHours} часов теории и {$practiceHours} часов практики. Точный график зависит от группы.",
                    'en' => "The guideline is {$durationWeeks} weeks, {$theoryHours} theory hours, and {$practiceHours} practice hours. The exact schedule depends on the group.",
                    'lt' => "Orientyras: {$durationWeeks} savaites, {$theoryHours} teorijos val. ir {$practiceHours} praktikos val. Tikslus grafikas priklauso nuo grupes.",
                    'pl' => "Orientacyjnie: {$durationWeeks} tygodni, {$theoryHours} godzin teorii i {$practiceHours} godzin praktyki. Dokladny harmonogram zalezy od grupy.",
                ],
            ],
            [
                'sort_order' => 40,
                'question' => ['ru' => 'Какие документы нужны?', 'en' => 'Which documents are needed?', 'lt' => 'Kokiu dokumentu reikia?', 'pl' => 'Jakie dokumenty sa potrzebne?'],
                'answer' => ['ru' => 'Обычно нужны документ личности, медицинская справка, фото и договор обучения. Менеджер уточнит список после заявки.', 'en' => 'Usually ID, medical certificate, photo, and training agreement are needed. A manager will confirm the list after application.', 'lt' => 'Paprastai reikia asmens dokumento, medicinines pazymos, nuotraukos ir mokymo sutarties. Vadybininkas patikslins sarasa po paraiskos.', 'pl' => 'Zwykle potrzebny jest dokument, zaswiadczenie lekarskie, zdjecie i umowa szkoleniowa. Menedzer potwierdzi liste po zgloszeniu.'],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function branchFaqItems(): array
    {
        return [
            [
                'sort_order' => 30,
                'question' => ['ru' => 'Можно прийти в филиал?', 'en' => 'Can I visit the branch?', 'lt' => 'Ar galiu atvykti i filiala?', 'pl' => 'Czy moge przyjsc do oddzialu?'],
                'answer' => ['ru' => 'Да, филиал принимает посетителей в рабочие часы.', 'en' => 'Yes, the branch accepts visitors during working hours.', 'lt' => 'Taip, filialas priima lankytojus darbo metu.', 'pl' => 'Tak, oddzial przyjmuje odwiedzajacych w godzinach pracy.'],
            ],
            [
                'sort_order' => 40,
                'question' => ['ru' => 'Можно выбрать группу в этом филиале?', 'en' => 'Can I choose a group at this branch?', 'lt' => 'Ar galiu pasirinkti grupe siame filiale?', 'pl' => 'Czy moge wybrac grupe w tym oddziale?'],
                'answer' => ['ru' => 'Да, если в филиале есть открытые места. Менеджер проверит ближайшие группы и предложит подходящее расписание.', 'en' => 'Yes, if the branch has open seats. A manager will check upcoming groups and suggest a suitable schedule.', 'lt' => 'Taip, jei filiale yra laisvu vietu. Vadybininkas patikrins artimiausias grupes ir pasiulys tinkama grafika.', 'pl' => 'Tak, jesli oddzial ma wolne miejsca. Menedzer sprawdzi najblizsze grupy i zaproponuje odpowiedni harmonogram.'],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function pageFaqItems(): array
    {
        return [
            [
                'sort_order' => 10,
                'question' => ['ru' => 'Где можно уточнить детали?', 'en' => 'Where can I clarify details?', 'lt' => 'Kur galima pasitikslinti detales?', 'pl' => 'Gdzie moge wyjasnic szczegoly?'],
                'answer' => ['ru' => 'Оставьте заявку или обратный звонок на сайте, и менеджер свяжется с вами для уточнения деталей.', 'en' => 'Send an application or callback request on the website, and a manager will contact you to clarify details.', 'lt' => 'Pateikite paraiska arba perskambinimo uzklausa svetaineje, ir vadybininkas susisieks del detaliu.', 'pl' => 'Wyslij zgloszenie albo prosbe o oddzwonienie na stronie, a menedzer skontaktuje sie w sprawie szczegolow.'],
            ],
            [
                'sort_order' => 20,
                'question' => ['ru' => 'Можно ли получить консультацию перед записью?', 'en' => 'Can I get a consultation before enrollment?', 'lt' => 'Ar galima gauti konsultacija pries registracija?', 'pl' => 'Czy mozna dostac konsultacje przed zapisem?'],
                'answer' => ['ru' => 'Да, менеджер поможет выбрать курс, филиал, группу, документы и удобный формат обучения.', 'en' => 'Yes, a manager will help choose a course, branch, group, documents, and convenient training format.', 'lt' => 'Taip, vadybininkas pades pasirinkti kursa, filiala, grupe, dokumentus ir patogu mokymo formata.', 'pl' => 'Tak, menedzer pomoze wybrac kurs, oddzial, grupe, dokumenty i wygodny format szkolenia.'],
            ],
        ];
    }

    /**
     * @param  array<string, string>  $question
     * @param  array<string, string>  $answer
     */
    private function upsertFaq(?string $faqableType, ?int $faqableId, int $sortOrder, array $question, array $answer): void
    {
        $faq = Faq::query()->firstOrNew([
            'faqable_type' => $faqableType,
            'faqable_id' => $faqableId,
            'sort_order' => $sortOrder,
        ]);

        if (! $faq->exists && blank($faq->uuid)) {
            $faq->uuid = (string) Str::uuid();
        }

        $faq->fill([
            'question_translations' => $question,
            'answer_translations' => $answer,
            'is_active' => true,
        ])->save();
    }
}

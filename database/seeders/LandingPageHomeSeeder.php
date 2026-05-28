<?php

namespace Database\Seeders;

use App\Models\LandingPage;
use Illuminate\Database\Seeder;

class LandingPageHomeSeeder extends Seeder
{
    private const DEFAULT_LOCALE = 'ru';

    public function run(): void
    {
        $translations = $this->translations();
        $page = LandingPage::query()->firstOrNew(['slug' => 'home']);

        $page->fill([
            'title' => $this->fallback($translations, 'title'),
            'title_translations' => $translations['title'],
            'eyebrow' => $this->fallback($translations, 'eyebrow'),
            'eyebrow_translations' => $translations['eyebrow'],
            'hero_title' => $this->fallback($translations, 'hero_title'),
            'hero_title_translations' => $translations['hero_title'],
            'hero_summary' => $this->fallback($translations, 'hero_summary'),
            'hero_summary_translations' => $translations['hero_summary'],
            'primary_button_label' => 'Записаться на курс',
            'primary_button_url' => '#application-form',
            'secondary_button_label' => 'Посмотреть курсы',
            'secondary_button_url' => '#programs',
            'about_heading' => $this->fallback($translations, 'about_heading'),
            'about_heading_translations' => $translations['about_heading'],
            'about_body' => $this->fallback($translations, 'about_body'),
            'about_body_translations' => $translations['about_body'],
            'offer_one_title' => $this->fallback($translations, 'offer_one_title'),
            'offer_one_title_translations' => $translations['offer_one_title'],
            'offer_one_body' => $this->fallback($translations, 'offer_one_body'),
            'offer_one_body_translations' => $translations['offer_one_body'],
            'offer_two_title' => $this->fallback($translations, 'offer_two_title'),
            'offer_two_title_translations' => $translations['offer_two_title'],
            'offer_two_body' => $this->fallback($translations, 'offer_two_body'),
            'offer_two_body_translations' => $translations['offer_two_body'],
            'offer_three_title' => $this->fallback($translations, 'offer_three_title'),
            'offer_three_title_translations' => $translations['offer_three_title'],
            'offer_three_body' => $this->fallback($translations, 'offer_three_body'),
            'offer_three_body_translations' => $translations['offer_three_body'],
            'published_at' => $page->published_at ?? now(),
        ]);

        $page->save();
    }

    /**
     * @param  array<string, array<string, string>>  $translations
     */
    private function fallback(array $translations, string $field): string
    {
        return $translations[$field][self::DEFAULT_LOCALE];
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function translations(): array
    {
        return [
            'title' => [
                'ru' => 'DrivePro Academy',
                'en' => 'DrivePro Academy',
                'lt' => 'DrivePro Academy',
                'pl' => 'DrivePro Academy',
            ],
            'eyebrow' => [
                'ru' => 'Автошкола в Вильнюсе',
                'en' => 'Driving school in Vilnius',
                'lt' => 'Vairavimo mokykla Vilniuje',
                'pl' => 'Szkoła jazdy w Wilnie',
            ],
            'hero_title' => [
                'ru' => 'Права категории B с понятным расписанием и практикой с инструктором',
                'en' => 'Category B driving lessons with clear scheduling and instructor-led practice',
                'lt' => 'B kategorijos vairavimo pamokos su aiškiu grafiku ir instruktoriumi',
                'pl' => 'Kurs prawa jazdy kategorii B z jasnym grafikiem i jazdami z instruktorem',
            ],
            'hero_summary' => [
                'ru' => 'Учитесь в удобной группе, проходите теорию онлайн или в классе и отслеживайте заявки, документы, занятия и экзамены в одной системе.',
                'en' => 'Study in a convenient group, complete theory online or in class, and keep applications, documents, lessons, and exams connected in one system.',
                'lt' => 'Mokykitės patogioje grupėje, teoriją rinkitės internetu arba klasėje, o paraiškas, dokumentus, pamokas ir egzaminus valdykite vienoje sistemoje.',
                'pl' => 'Ucz się w wygodnej grupie, wybierz teorię online albo w sali, a zgłoszenia, dokumenty, jazdy i egzaminy prowadź w jednym systemie.',
            ],
            'about_heading' => [
                'ru' => 'От первой заявки до экзамена',
                'en' => 'From first application to exam day',
                'lt' => 'Nuo pirmos paraiškos iki egzamino',
                'pl' => 'Od pierwszego zgłoszenia do egzaminu',
            ],
            'about_body' => [
                'ru' => 'Главная страница показывает будущему ученику курсы, филиалы, ближайшие группы, цены и форму записи. Администратор сразу видит заявку в CRM и продолжает работу без повторного ввода данных.',
                'en' => 'The homepage shows future students courses, branches, upcoming groups, prices, and the application form. The administrator sees each request in CRM immediately and continues without re-entering data.',
                'lt' => 'Pradžios puslapis būsimam mokiniui rodo kursus, filialus, artimiausias grupes, kainas ir registracijos formą. Administratorius užklausą iškart mato CRM ir dirba be pakartotinio duomenų vedimo.',
                'pl' => 'Strona główna pokazuje przyszłemu uczniowi kursy, oddziały, najbliższe grupy, ceny i formularz zapisu. Administrator od razu widzi zgłoszenie w CRM i pracuje bez ponownego wpisywania danych.',
            ],
            'offer_one_title' => [
                'ru' => 'Заявки сразу в CRM',
                'en' => 'Requests go straight to CRM',
                'lt' => 'Užklausos iškart CRM',
                'pl' => 'Zgłoszenia od razu w CRM',
            ],
            'offer_one_body' => [
                'ru' => 'Формы записи, контакта и обратного звонка сохраняют источник, UTM-метки, курс, филиал и выбранную группу.',
                'en' => 'Application, contact, and callback forms store source, UTM tags, course, branch, and selected group.',
                'lt' => 'Registracijos, kontaktų ir perskambinimo formos išsaugo šaltinį, UTM žymas, kursą, filialą ir pasirinktą grupę.',
                'pl' => 'Formularze zapisu, kontaktu i oddzwonienia zapisują źródło, UTM, kurs, oddział i wybraną grupę.',
            ],
            'offer_two_title' => [
                'ru' => 'Группы и расписание под контролем',
                'en' => 'Groups and schedule under control',
                'lt' => 'Grupės ir grafikas valdomi',
                'pl' => 'Grupy i grafik pod kontrolą',
            ],
            'offer_two_body' => [
                'ru' => 'Публичная витрина показывает только активные курсы, открытые группы и филиалы, которые доступны для записи.',
                'en' => 'The public page shows only active courses, open groups, and branches available for enrollment.',
                'lt' => 'Viešame puslapyje rodomi tik aktyvūs kursai, atviros grupės ir registracijai prieinami filialai.',
                'pl' => 'Publiczna strona pokazuje tylko aktywne kursy, otwarte grupy i oddziały dostępne do zapisu.',
            ],
            'offer_three_title' => [
                'ru' => 'Контент на четырёх языках',
                'en' => 'Content in four languages',
                'lt' => 'Turinys keturiomis kalbomis',
                'pl' => 'Treści w czterech językach',
            ],
            'offer_three_body' => [
                'ru' => 'Админка хранит тексты на русском, английском, литовском и польском, чтобы сайт следовал выбранному языку.',
                'en' => 'The admin stores Russian, English, Lithuanian, and Polish content so the website follows the selected language.',
                'lt' => 'Administravimas saugo rusų, anglų, lietuvių ir lenkų tekstus, todėl svetainė seka pasirinktą kalbą.',
                'pl' => 'Panel zapisuje treści po rosyjsku, angielsku, litewsku i polsku, aby strona działała w wybranym języku.',
            ],
        ];
    }
}

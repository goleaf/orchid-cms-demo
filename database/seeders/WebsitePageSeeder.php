<?php

namespace Database\Seeders;

use App\Models\SitePage;
use Illuminate\Database\Seeder;

class WebsitePageSeeder extends Seeder
{
    private const OG_IMAGE = 'images/driving-school-hero.png';

    public function run(): void
    {
        foreach ($this->pages() as $page) {
            $factory = SitePage::factory()->published()->active();

            if ($page['factory'] !== null) {
                $factory = $factory->{$page['factory']}();
            }

            $payload = $factory
                ->make([
                    'type' => $page['type'],
                    'slug' => $page['slug'],
                    'title_translations' => $page['title'],
                    'subtitle_translations' => $page['subtitle'],
                    'content_translations' => $page['content'],
                    'excerpt_translations' => $page['excerpt'],
                    'seo_title_translations' => $page['seo_title'],
                    'seo_description_translations' => $page['seo_description'],
                    'og_title_translations' => $page['og_title'],
                    'og_description_translations' => $page['og_description'],
                    'og_image' => self::OG_IMAGE,
                    'canonical_url' => url($page['path']),
                    'template' => $page['template'],
                    'is_active' => true,
                    'is_indexable' => $page['is_indexable'] ?? true,
                    'sort_order' => $page['sort_order'],
                    'published_at' => now(),
                ])
                ->only((new SitePage)->getFillable());

            SitePage::query()->updateOrCreate(['slug' => $page['slug']], $payload);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function pages(): array
    {
        return [
            $this->page(
                'home',
                'home',
                'home',
                'home',
                '/',
                10,
                $this->translations('Главная', 'Home', 'Pradžia', 'Strona główna'),
                $this->translations(
                    'Курсы, группы, цены и заявки автошколы в одном месте.',
                    'Driving school courses, groups, prices, and applications in one place.',
                    'Vairavimo mokyklos kursai, grupės, kainos ir registracija vienoje vietoje.',
                    'Kursy, grupy, ceny i zapisy szkoły jazdy w jednym miejscu.'
                ),
                $this->translations(
                    'На главной странице ученик видит доступные курсы, ближайшие группы, филиалы, цены и форму записи. Администратор получает заявку в CRM без повторного ввода данных.',
                    'The homepage shows available courses, upcoming groups, branches, prices, and the application form. The administrator receives the request in CRM without re-entering data.',
                    'Pradžios puslapyje mokinys mato prieinamus kursus, artimiausias grupes, filialus, kainas ir registracijos formą. Administratorius užklausą gauna CRM be pakartotinio duomenų vedimo.',
                    'Strona główna pokazuje dostępne kursy, najbliższe grupy, oddziały, ceny i formularz zapisu. Administrator otrzymuje zgłoszenie w CRM bez ponownego wpisywania danych.'
                ),
                $this->translations(
                    'Главная страница автошколы с курсами, группами и записью.',
                    'Driving school homepage with courses, groups, and enrollment.',
                    'Vairavimo mokyklos pradžios puslapis su kursais, grupėmis ir registracija.',
                    'Strona główna szkoły jazdy z kursami, grupami i zapisami.'
                ),
                $this->translations(
                    'Автошкола в Вильнюсе | Курсы, группы и запись',
                    'Driving school in Vilnius | Courses, groups, and enrollment',
                    'Vairavimo mokykla Vilniuje | Kursai, grupės ir registracija',
                    'Szkoła jazdy w Wilnie | Kursy, grupy i zapisy'
                ),
                $this->translations(
                    'Выберите курс, филиал и группу, отправьте заявку и продолжайте обучение с понятным расписанием.',
                    'Choose a course, branch, and group, send an application, and continue training with a clear schedule.',
                    'Pasirinkite kursą, filialą ir grupę, išsiųskite paraišką ir mokykitės pagal aiškų grafiką.',
                    'Wybierz kurs, oddział i grupę, wyślij zgłoszenie i ucz się według jasnego grafiku.'
                ),
            ),
            $this->page(
                'pricing',
                'pricing',
                'pricing',
                'pricing',
                '/pricing',
                20,
                $this->translations('Цены', 'Pricing', 'Kainos', 'Ceny'),
                $this->translations(
                    'Пакеты обучения, состав курса и условия оплаты для учеников.',
                    'Training packages, course contents, and payment terms for students.',
                    'Mokymo paketai, kurso sudėtis ir apmokėjimo sąlygos mokiniams.',
                    'Pakiety szkoleniowe, zakres kursu i warunki płatności dla uczniów.'
                ),
                $this->translations(
                    'Страница цен помогает сравнить пакеты обучения, понять стоимость теории и практики, увидеть включённые услуги и выбрать удобный формат оплаты.',
                    'The pricing page helps compare training packages, understand theory and practice costs, see included services, and choose a convenient payment format.',
                    'Kainų puslapis padeda palyginti mokymo paketus, suprasti teorijos ir praktikos kainą, matyti įtrauktas paslaugas ir pasirinkti patogų apmokėjimą.',
                    'Strona cen pomaga porównać pakiety szkoleniowe, zrozumieć koszt teorii i praktyki, zobaczyć usługi w pakiecie i wybrać wygodną płatność.'
                ),
                $this->translations(
                    'Цены на обучение, пакеты и условия оплаты.',
                    'Training prices, packages, and payment terms.',
                    'Mokymo kainos, paketai ir apmokėjimo sąlygos.',
                    'Ceny kursów, pakiety i warunki płatności.'
                ),
                $this->translations(
                    'Цены автошколы | Пакеты обучения и оплата',
                    'Driving school pricing | Training packages and payments',
                    'Vairavimo mokyklos kainos | Mokymo paketai ir apmokėjimas',
                    'Ceny szkoły jazdy | Pakiety i płatności'
                ),
                $this->translations(
                    'Сравните пакеты категории B, стоимость занятий, рассрочку и условия оплаты.',
                    'Compare Category B packages, lesson pricing, installment options, and payment terms.',
                    'Palyginkite B kategorijos paketus, pamokų kainas, mokėjimą dalimis ir sąlygas.',
                    'Porównaj pakiety kategorii B, ceny jazd, raty i warunki płatności.'
                ),
            ),
            $this->page(
                'contacts',
                'contacts',
                'contacts',
                'contacts',
                '/contacts',
                30,
                $this->translations('Контакты', 'Contacts', 'Kontaktai', 'Kontakty'),
                $this->translations(
                    'Филиалы, телефоны, рабочее время и формы связи автошколы.',
                    'Branches, phone numbers, opening hours, and driving school contact forms.',
                    'Filialai, telefonai, darbo laikas ir vairavimo mokyklos kontaktų formos.',
                    'Oddziały, telefony, godziny pracy i formularze kontaktowe szkoły jazdy.'
                ),
                $this->translations(
                    'На странице контактов ученик выбирает удобный филиал, отправляет сообщение, заказывает обратный звонок и видит основные способы связи с администратором.',
                    'On the contacts page, a student chooses a convenient branch, sends a message, requests a callback, and sees the main ways to reach the administrator.',
                    'Kontaktų puslapyje mokinys pasirenka patogų filialą, siunčia žinutę, užsako perskambinimą ir mato pagrindinius ryšio būdus su administratoriumi.',
                    'Na stronie kontaktów uczeń wybiera wygodny oddział, wysyła wiadomość, zamawia oddzwonienie i widzi główne sposoby kontaktu z administratorem.'
                ),
                $this->translations(
                    'Контакты филиалов, телефоны и формы связи.',
                    'Branch contacts, phone numbers, and contact forms.',
                    'Filialų kontaktai, telefonai ir kontaktų formos.',
                    'Kontakty oddziałów, telefony i formularze.'
                ),
                $this->translations(
                    'Контакты автошколы | Филиалы и обратный звонок',
                    'Driving school contacts | Branches and callback',
                    'Vairavimo mokyklos kontaktai | Filialai ir perskambinimas',
                    'Kontakt ze szkołą jazdy | Oddziały i oddzwonienie'
                ),
                $this->translations(
                    'Свяжитесь с автошколой, выберите филиал или оставьте заявку на обратный звонок.',
                    'Contact the driving school, choose a branch, or request a callback.',
                    'Susisiekite su vairavimo mokykla, pasirinkite filialą arba užsakykite perskambinimą.',
                    'Skontaktuj się ze szkołą jazdy, wybierz oddział albo zamów oddzwonienie.'
                ),
            ),
            $this->page(
                'thankYou',
                'thank_you',
                'thank-you',
                'thank-you',
                '/thank-you',
                40,
                $this->translations('Спасибо', 'Thank you', 'Ačiū', 'Dziękujemy'),
                $this->translations(
                    'Подтверждение отправки заявки или сообщения в автошколу.',
                    'Confirmation after sending an application or message to the driving school.',
                    'Patvirtinimas išsiuntus registraciją arba žinutę vairavimo mokyklai.',
                    'Potwierdzenie wysłania zgłoszenia albo wiadomości do szkoły jazdy.'
                ),
                $this->translations(
                    'Эта страница подтверждает, что заявка принята. Ученик видит следующий шаг, а менеджер получает обращение в CRM для дальнейшей обработки.',
                    'This page confirms that the request has been received. The student sees the next step, and the manager receives the request in CRM for follow-up.',
                    'Šis puslapis patvirtina, kad užklausa gauta. Mokinys mato kitą žingsnį, o vadybininkas gauna užklausą CRM tolesniam darbui.',
                    'Ta strona potwierdza przyjęcie zgłoszenia. Uczeń widzi kolejny krok, a menedżer otrzymuje zgłoszenie w CRM do dalszej obsługi.'
                ),
                $this->translations(
                    'Подтверждение заявки и следующий шаг для ученика.',
                    'Application confirmation and the next student step.',
                    'Paraiškos patvirtinimas ir kitas mokinio žingsnis.',
                    'Potwierdzenie zgłoszenia i kolejny krok ucznia.'
                ),
                $this->translations(
                    'Спасибо за заявку | Автошкола',
                    'Thank you for your application | Driving school',
                    'Ačiū už paraišką | Vairavimo mokykla',
                    'Dziękujemy za zgłoszenie | Szkoła jazdy'
                ),
                $this->translations(
                    'Заявка отправлена, менеджер автошколы свяжется с учеником для уточнения деталей.',
                    'The application has been sent, and a driving school manager will contact the student to confirm details.',
                    'Paraiška išsiųsta, vairavimo mokyklos vadybininkas susisieks dėl detalių.',
                    'Zgłoszenie zostało wysłane, a menedżer szkoły jazdy skontaktuje się w celu potwierdzenia szczegółów.'
                ),
                false,
            ),
            $this->page(
                'privacyPolicy',
                'privacy_policy',
                'privacy-policy',
                'legal',
                '/pages/privacy-policy',
                50,
                $this->translations('Политика конфиденциальности', 'Privacy policy', 'Privatumo politika', 'Polityka prywatności'),
                $this->translations(
                    'Как автошкола обрабатывает заявки, контакты, документы и учебные данные.',
                    'How the driving school handles applications, contacts, documents, and training data.',
                    'Kaip vairavimo mokykla tvarko paraiškas, kontaktus, dokumentus ir mokymo duomenis.',
                    'Jak szkoła jazdy przetwarza zgłoszenia, kontakty, dokumenty i dane szkoleniowe.'
                ),
                $this->translations(
                    'Политика описывает, какие данные нужны для записи и обучения, как они используются администратором, как защищаются документы и как ученик может запросить уточнение.',
                    'The policy explains which data is needed for enrollment and training, how administrators use it, how documents are protected, and how a student can request clarification.',
                    'Politikoje paaiškinama, kokių duomenų reikia registracijai ir mokymui, kaip juos naudoja administratoriai, kaip saugomi dokumentai ir kaip mokinys gali prašyti paaiškinimo.',
                    'Polityka wyjaśnia, jakie dane są potrzebne do zapisów i szkolenia, jak korzystają z nich administratorzy, jak chronione są dokumenty i jak uczeń może poprosić o wyjaśnienie.'
                ),
                $this->translations(
                    'Правила обработки заявок, контактов, документов и учебных данных.',
                    'Rules for handling applications, contacts, documents, and training data.',
                    'Paraiškų, kontaktų, dokumentų ir mokymo duomenų tvarkymo taisyklės.',
                    'Zasady przetwarzania zgłoszeń, kontaktów, dokumentów i danych szkoleniowych.'
                ),
                $this->translations(
                    'Политика конфиденциальности автошколы',
                    'Driving school privacy policy',
                    'Vairavimo mokyklos privatumo politika',
                    'Polityka prywatności szkoły jazdy'
                ),
                $this->translations(
                    'Узнайте, как автошкола обрабатывает данные заявок, учеников, документов и учебного процесса.',
                    'Learn how the driving school handles application, student, document, and training process data.',
                    'Sužinokite, kaip vairavimo mokykla tvarko paraiškų, mokinių, dokumentų ir mokymo proceso duomenis.',
                    'Dowiedz się, jak szkoła jazdy przetwarza dane zgłoszeń, uczniów, dokumentów i procesu szkolenia.'
                ),
            ),
            $this->page(
                'terms',
                'terms',
                'terms',
                'legal',
                '/pages/terms',
                60,
                $this->translations('Условия обучения', 'Training terms', 'Mokymo sąlygos', 'Warunki szkolenia'),
                $this->translations(
                    'Правила записи, оплаты, посещения занятий и переноса обучения.',
                    'Rules for enrollment, payment, attendance, and rescheduling training.',
                    'Registracijos, apmokėjimo, pamokų lankymo ir mokymo perkėlimo taisyklės.',
                    'Zasady zapisów, płatności, obecności i przenoszenia szkolenia.'
                ),
                $this->translations(
                    'Условия обучения объясняют порядок записи в группу, оплату, посещение теории и практики, перенос занятий, документы и подготовку к экзамену.',
                    'The training terms explain enrollment into a group, payment, theory and practice attendance, lesson rescheduling, documents, and exam preparation.',
                    'Mokymo sąlygos paaiškina registraciją į grupę, apmokėjimą, teorijos ir praktikos lankymą, pamokų perkėlimą, dokumentus ir pasiruošimą egzaminui.',
                    'Warunki szkolenia wyjaśniają zapis do grupy, płatność, obecność na teorii i praktyce, przenoszenie zajęć, dokumenty i przygotowanie do egzaminu.'
                ),
                $this->translations(
                    'Условия записи, оплаты, занятий, документов и подготовки к экзамену.',
                    'Terms for enrollment, payment, lessons, documents, and exam preparation.',
                    'Registracijos, apmokėjimo, pamokų, dokumentų ir pasiruošimo egzaminui sąlygos.',
                    'Warunki zapisów, płatności, zajęć, dokumentów i przygotowania do egzaminu.'
                ),
                $this->translations(
                    'Условия обучения в автошколе',
                    'Driving school training terms',
                    'Vairavimo mokyklos mokymo sąlygos',
                    'Warunki szkolenia w szkole jazdy'
                ),
                $this->translations(
                    'Проверьте правила записи, оплаты, посещения занятий, переноса обучения и подготовки к экзамену.',
                    'Review rules for enrollment, payment, attendance, rescheduling training, and exam preparation.',
                    'Peržiūrėkite registracijos, apmokėjimo, lankymo, mokymo perkėlimo ir pasiruošimo egzaminui taisykles.',
                    'Sprawdź zasady zapisów, płatności, obecności, przenoszenia szkolenia i przygotowania do egzaminu.'
                ),
            ),
        ];
    }

    /**
     * @param  array<string, string>  $title
     * @param  array<string, string>  $subtitle
     * @param  array<string, string>  $content
     * @param  array<string, string>  $excerpt
     * @param  array<string, string>  $seoTitle
     * @param  array<string, string>  $seoDescription
     * @return array<string, mixed>
     */
    private function page(
        ?string $factory,
        string $type,
        string $slug,
        string $template,
        string $path,
        int $sortOrder,
        array $title,
        array $subtitle,
        array $content,
        array $excerpt,
        array $seoTitle,
        array $seoDescription,
        bool $isIndexable = true,
    ): array {
        return [
            'factory' => $factory,
            'type' => $type,
            'slug' => $slug,
            'template' => $template,
            'path' => $path,
            'sort_order' => $sortOrder,
            'title' => $title,
            'subtitle' => $subtitle,
            'content' => $content,
            'excerpt' => $excerpt,
            'seo_title' => $seoTitle,
            'seo_description' => $seoDescription,
            'og_title' => $seoTitle,
            'og_description' => $seoDescription,
            'is_indexable' => $isIndexable,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function translations(string $ru, string $en, string $lt, string $pl): array
    {
        return [
            'ru' => $ru,
            'en' => $en,
            'lt' => $lt,
            'pl' => $pl,
        ];
    }
}

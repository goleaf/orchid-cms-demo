<?php

namespace Database\Factories;

use App\Models\LandingPage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LandingPage>
 */
class LandingPageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'slug' => $this->faker->unique()->slug(2),
            'eyebrow' => 'CMS foundation',
            'hero_title' => $this->faker->sentence(4),
            'hero_summary' => $this->faker->paragraph(2),
            'primary_button_label' => 'Open admin',
            'primary_button_url' => '/admin',
            'secondary_button_label' => 'Preview content',
            'secondary_button_url' => '#content',
            'about_heading' => $this->faker->sentence(4),
            'about_body' => $this->faker->paragraph(3),
            'offer_one_title' => 'Editable homepage',
            'offer_one_body' => $this->faker->sentence(12),
            'offer_two_title' => 'Orchid dashboard',
            'offer_two_body' => $this->faker->sentence(12),
            'offer_three_title' => 'Laravel 12 base',
            'offer_three_body' => $this->faker->sentence(12),
            'published_at' => now(),
        ];
    }

    public function home(): static
    {
        return $this->state(fn (): array => [
            'title' => 'DrivePro Academy',
            'title_translations' => [
                'ru' => 'DrivePro Academy',
                'en' => 'DrivePro Academy',
                'lt' => 'DrivePro Academy',
                'pl' => 'DrivePro Academy',
            ],
            'slug' => 'home',
            'eyebrow' => 'Driving school platform',
            'eyebrow_translations' => [
                'ru' => 'Автошкола в одном месте',
                'en' => 'Driving school platform',
                'lt' => 'Vairavimo mokykla vienoje vietoje',
                'pl' => 'Szkola jazdy w jednym miejscu',
            ],
            'hero_title' => 'Category B in Vilnius with online theory and instructor-led practice',
            'hero_title_translations' => [
                'ru' => 'Категория B в Вильнюсе: теория онлайн и практика с инструктором',
                'en' => 'Category B in Vilnius with online theory and instructor-led practice',
                'lt' => 'B kategorija Vilniuje: teorija internetu ir praktika su instruktoriumi',
                'pl' => 'Kategoria B w Wilnie: teoria online i praktyka z instruktorem',
            ],
            'hero_summary' => 'A public website and Orchid-powered back office for students, instructors, vehicles, schedules, payments, documents, LMS content, and school analytics.',
            'hero_summary_translations' => [
                'ru' => 'Публичный сайт и Orchid-админка для заявок, курсов, филиалов, групп, инструкторов, автопарка и операционной работы автошколы.',
                'en' => 'A public website and Orchid-powered back office for students, instructors, vehicles, schedules, payments, documents, LMS content, and school analytics.',
                'lt' => 'Vieša svetaine ir Orchid administracija uzklausoms, kursams, filialams, grupems, instruktoriams, automobiliams ir kasdieniam vairavimo mokyklos darbui.',
                'pl' => 'Publiczna strona i panel Orchid dla zgloszen, kursow, oddzialow, grup, instruktorow, floty i pracy szkoly jazdy.',
            ],
            'primary_button_label' => 'Open platform',
            'primary_button_url' => '/admin',
            'secondary_button_label' => 'Explore programs',
            'secondary_button_url' => '#content',
            'about_heading' => 'Prepared for a full auto-school operating model',
            'about_heading_translations' => [
                'ru' => 'Основа публичного сайта для локальной автошколы',
                'en' => 'Prepared for a full auto-school operating model',
                'lt' => 'Pagrindas vietines vairavimo mokyklos svetainei',
                'pl' => 'Podstawa strony lokalnej szkoly jazdy',
            ],
            'about_body' => 'The first platform slice connects the website with CRM, LMS, scheduling, fleet, exams, payments, document tracking, and analytics-ready Orchid dashboards.',
            'about_body_translations' => [
                'ru' => 'Первый блок связывает сайт с CRM: курсы, цены, филиалы, группы и формы заявок уже работают через общие модели.',
                'en' => 'The first platform slice connects the website with CRM, LMS, scheduling, fleet, exams, payments, document tracking, and analytics-ready Orchid dashboards.',
                'lt' => 'Pirmas blokas sujungia svetaine su CRM: kursai, kainos, filialai, grupes ir registracijos formos jau veikia per bendrus modelius.',
                'pl' => 'Pierwszy blok laczy strone z CRM: kursy, ceny, oddzialy, grupy i formularze zgloszen dzialaja przez wspolne modele.',
            ],
            'offer_one_title' => 'Student CRM and cabinet base',
            'offer_one_title_translations' => [
                'ru' => 'Заявки сразу попадают в CRM',
                'en' => 'Student CRM and cabinet base',
                'lt' => 'Uzklausos iskart patenka i CRM',
                'pl' => 'Zgloszenia od razu trafiaja do CRM',
            ],
            'offer_one_body' => 'Track leads, enrolled students, programs, instructors, documents, payments, and training progress from one student profile.',
            'offer_one_body_translations' => [
                'ru' => 'Формы записи и обратного звонка создают лиды с источником, UTM-метками, задачами и историей.',
                'en' => 'Track leads, enrolled students, programs, instructors, documents, payments, and training progress from one student profile.',
                'lt' => 'Registracijos ir perskambinimo formos kuria uzklausas su saltiniu, UTM zymomis, uzduotimis ir istorija.',
                'pl' => 'Formularze zapisu i oddzwonienia tworza zgloszenia ze zrodlem, UTM, zadaniami i historia.',
            ],
            'offer_two_title' => 'Scheduling and fleet control',
            'offer_two_title_translations' => [
                'ru' => 'Курсы, цены и группы управляются из Orchid',
                'en' => 'Scheduling and fleet control',
                'lt' => 'Kursai, kainos ir grupes valdomi per Orchid',
                'pl' => 'Kursy, ceny i grupy sa zarzadzane w Orchid',
            ],
            'offer_two_body' => 'Plan lessons by branch, instructor, vehicle, course, and lesson status with indexed calendar data for future availability rules.',
            'offer_two_body_translations' => [
                'ru' => 'Администратор управляет публичными курсами, филиалами и наборами групп без SaaS-логики.',
                'en' => 'Plan lessons by branch, instructor, vehicle, course, and lesson status with indexed calendar data for future availability rules.',
                'lt' => 'Administratorius valdo viesus kursus, filialus ir grupiu registracijas be SaaS logikos.',
                'pl' => 'Administrator zarzadza publicznymi kursami, oddzialami i naborami grup bez logiki SaaS.',
            ],
            'offer_three_title' => 'LMS, exams, and finance',
            'offer_three_title_translations' => [
                'ru' => 'Мультиязычный контент заложен с начала',
                'en' => 'LMS, exams, and finance',
                'lt' => 'Daugiakalbis turinys paruostas nuo pradzios',
                'pl' => 'Tresci wielojezyczne sa gotowe od poczatku',
            ],
            'offer_three_body' => 'Course modules, exam attempts, payment records, and document statuses are modeled for the next interactive workflows.',
            'offer_three_body_translations' => [
                'ru' => 'Публичные тексты и интерфейсные подписи используют переводы и fallback на язык по умолчанию.',
                'en' => 'Course modules, exam attempts, payment records, and document statuses are modeled for the next interactive workflows.',
                'lt' => 'Viesi tekstai ir sasajos uzrasai naudoja vertimus ir atsargini numatytosios kalbos varianta.',
                'pl' => 'Publiczne teksty i etykiety interfejsu uzywaja tlumaczen oraz fallbacku do jezyka domyslnego.',
            ],
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'published_at' => now(),
        ]);
    }
}

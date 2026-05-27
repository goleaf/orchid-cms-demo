<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Course;
use App\Models\Faq;
use Illuminate\Database\Seeder;

class WebsiteFaqSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            WebsiteCourseSeeder::class,
            WebsiteBranchSeeder::class,
        ]);

        $course = Course::query()->where('slug', 'category-b-manual')->first();
        $branch = Branch::query()->where('slug', 'vilnius-main')->first();

        $items = [
            [
                'factory' => Faq::factory()->global(),
                'sort_order' => 10,
                'question' => ['ru' => 'Как оставить заявку?', 'en' => 'How do I send an application?', 'lt' => 'Kaip pateikti paraiska?', 'pl' => 'Jak wyslac zgloszenie?'],
                'answer' => ['ru' => 'Заполните форму на сайте, и заявка появится в CRM.', 'en' => 'Submit the website form and the lead will appear in CRM.', 'lt' => 'Uzpildykite forma svetaineje ir uzklausa atsiras CRM.', 'pl' => 'Wypelnij formularz, a zgloszenie pojawi sie w CRM.'],
            ],
            [
                'factory' => $course === null ? Faq::factory()->global() : Faq::factory()->forCourse($course),
                'sort_order' => 20,
                'question' => ['ru' => 'Можно ли выбрать группу?', 'en' => 'Can I choose a group?', 'lt' => 'Ar galiu pasirinkti grupe?', 'pl' => 'Czy moge wybrac grupe?'],
                'answer' => ['ru' => 'Да, менеджер поможет подобрать подходящую группу и филиал.', 'en' => 'Yes, a manager will help you choose a suitable group and branch.', 'lt' => 'Taip, vadybininkas pades pasirinkti tinkama grupe ir filiala.', 'pl' => 'Tak, menedzer pomoze wybrac odpowiednia grupe i oddzial.'],
            ],
            [
                'factory' => $branch === null ? Faq::factory()->global() : Faq::factory()->forBranch($branch),
                'sort_order' => 30,
                'question' => ['ru' => 'Можно прийти в филиал?', 'en' => 'Can I visit the branch?', 'lt' => 'Ar galiu atvykti i filiala?', 'pl' => 'Czy moge przyjsc do oddzialu?'],
                'answer' => ['ru' => 'Да, филиал принимает посетителей в рабочие часы.', 'en' => 'Yes, the branch accepts visitors during working hours.', 'lt' => 'Taip, filialas priima lankytojus darbo metu.', 'pl' => 'Tak, oddzial przyjmuje odwiedzajacych w godzinach pracy.'],
            ],
        ];

        foreach ($items as $item) {
            $payload = $item['factory']
                ->active()
                ->make([
                    'question_translations' => $item['question'],
                    'answer_translations' => $item['answer'],
                    'sort_order' => $item['sort_order'],
                ])
                ->only((new Faq)->getFillable());

            Faq::query()->updateOrCreate([
                'faqable_type' => $payload['faqable_type'],
                'faqable_id' => $payload['faqable_id'],
                'sort_order' => $payload['sort_order'],
            ], $payload);
        }
    }
}

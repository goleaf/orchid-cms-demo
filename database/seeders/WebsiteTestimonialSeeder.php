<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Course;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class WebsiteTestimonialSeeder extends Seeder
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
                'author' => 'Ieva N.',
                'rating' => 5,
                'text' => [
                    'ru' => 'Отличный курс и внимательные инструкторы.',
                    'en' => 'Great course and helpful instructors.',
                    'lt' => 'Puikus kursas ir demesingi instruktoriai.',
                    'pl' => 'Swietny kurs i pomocni instruktorzy.',
                ],
            ],
            [
                'author' => 'Tomas K.',
                'rating' => 5,
                'text' => [
                    'ru' => 'Менеджер быстро помог выбрать группу.',
                    'en' => 'The manager quickly helped me choose a group.',
                    'lt' => 'Vadybininkas greitai padejo pasirinkti grupe.',
                    'pl' => 'Menedzer szybko pomogl wybrac grupe.',
                ],
            ],
        ];

        foreach ($items as $index => $item) {
            $payload = Testimonial::factory()
                ->active()
                ->published()
                ->withRating($item['rating'])
                ->make([
                    'training_program_id' => $course?->id,
                    'branch_id' => $branch?->id,
                    'author_name' => $item['author'],
                    'name_translations' => [
                        'ru' => $item['author'],
                        'en' => $item['author'],
                        'lt' => $item['author'],
                        'pl' => $item['author'],
                    ],
                    'body' => $item['text']['en'],
                    'text_translations' => $item['text'],
                    'is_featured' => $index === 0,
                    'sort_order' => ($index + 1) * 10,
                ])
                ->only((new Testimonial)->getFillable());

            Testimonial::query()->updateOrCreate([
                'author_name' => $payload['author_name'],
                'training_program_id' => $payload['training_program_id'],
            ], $payload);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Enums\GroupStatus;
use App\Models\Branch;
use App\Models\Course;
use App\Models\TrainingGroup;
use Illuminate\Database\Seeder;

class WebsiteTrainingGroupSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            WebsiteCourseSeeder::class,
            WebsiteBranchSeeder::class,
        ]);

        $categoryB = Course::query()->where('slug', 'category-b-manual')->first();
        $categoryA = Course::query()->where('slug', 'category-a-motorcycle')->first();
        $vilnius = Branch::query()->where('slug', 'vilnius-main')->first();
        $kaunas = Branch::query()->where('slug', 'kaunas-center')->first();

        $groups = [
            'B-VNO-001' => [
                'course' => $categoryB,
                'branch' => $vilnius,
                'state' => 'evening',
                'name' => ['ru' => 'Вечерняя группа категории B', 'en' => 'Evening Category B group', 'lt' => 'Vakaro B kategorijos grupe', 'pl' => 'Wieczorowa grupa kategorii B'],
                'capacity' => 14,
                'taken' => 5,
                'sort_order' => 10,
            ],
            'A-KAU-001' => [
                'course' => $categoryA,
                'branch' => $kaunas,
                'state' => 'weekend',
                'name' => ['ru' => 'Группа категории A выходного дня', 'en' => 'Weekend Category A group', 'lt' => 'Savaitgalio A kategorijos grupe', 'pl' => 'Weekendowa grupa kategorii A'],
                'capacity' => 8,
                'taken' => 2,
                'sort_order' => 20,
            ],
        ];

        foreach ($groups as $code => $group) {
            if ($group['course'] === null || $group['branch'] === null) {
                continue;
            }

            $payload = TrainingGroup::factory()
                ->{$group['state']}()
                ->recruiting()
                ->visibleOnSite()
                ->startingSoon()
                ->withCapacity($group['capacity'], $group['taken'])
                ->make([
                    'group_number' => $code,
                    'code' => $code,
                    'branch_id' => $group['branch']->id,
                    'training_program_id' => $group['course']->id,
                    'course_category_id' => $group['course']->course_category_id,
                    'name' => $group['name']['en'],
                    'name_translations' => $group['name'],
                    'status' => GroupStatus::Recruiting,
                    'is_visible_on_site' => true,
                    'is_featured' => $code === 'B-VNO-001',
                    'sort_order' => $group['sort_order'],
                ])
                ->only((new TrainingGroup)->getFillable());

            TrainingGroup::query()->updateOrCreate(['code' => $code], $payload);
        }
    }
}

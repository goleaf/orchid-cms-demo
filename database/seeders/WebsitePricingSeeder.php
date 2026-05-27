<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\PricingPackage;
use Illuminate\Database\Seeder;

class WebsitePricingSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(WebsiteCourseSeeder::class);

        $course = Course::query()->where('slug', 'category-b-manual')->first();
        $category = CourseCategory::query()->where('slug', 'category-b')->first();

        $packages = [
            'category-b-standard' => ['state' => 'standard', 'code' => 'standard', 'sort_order' => 10],
            'category-b-premium' => ['state' => 'premium', 'code' => 'premium', 'sort_order' => 20],
            'category-b-intensive' => ['state' => 'intensive', 'code' => 'intensive', 'sort_order' => 30],
            'extra-lessons' => ['state' => 'extraLessons', 'code' => 'extra_lessons', 'sort_order' => 40],
        ];

        foreach ($packages as $slug => $package) {
            $payload = PricingPackage::factory()
                ->translated()
                ->{$package['state']}()
                ->active()
                ->visibleOnSite()
                ->make([
                    'course_id' => $course?->id,
                    'course_category_id' => $category?->id,
                    'code' => $package['code'],
                    'slug' => $slug,
                    'sort_order' => $package['sort_order'],
                ])
                ->only((new PricingPackage)->getFillable());

            PricingPackage::query()->updateOrCreate(['slug' => $slug], $payload);
        }
    }
}

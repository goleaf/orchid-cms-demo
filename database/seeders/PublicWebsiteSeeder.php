<?php

namespace Database\Seeders;

use App\Enums\GroupStatus;
use App\Models\Branch;
use App\Models\CourseCategory;
use App\Models\Faq;
use App\Models\PricingPackage;
use App\Models\SitePage;
use App\Models\SiteSetting;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;
use Illuminate\Database\Seeder;

class PublicWebsiteSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPages();
        $this->seedCourseCategories();
        $this->seedBranches();
        $this->seedPrograms();
        $this->seedPricingPackages();
        $this->seedGroups();
        $this->seedFaqs();
        $this->seedSiteSettings();
    }

    private function seedPages(): void
    {
        $pages = [
            'home' => ['type' => 'home', 'title' => 'Главная', 'title_en' => 'Home'],
            'prices' => ['type' => 'pricing', 'title' => 'Цены', 'title_en' => 'Prices'],
            'contacts' => ['type' => 'contacts', 'title' => 'Контакты', 'title_en' => 'Contacts'],
            'thanks' => ['type' => 'thank_you', 'title' => 'Спасибо', 'title_en' => 'Thank you'],
            'privacy-policy' => ['type' => 'privacy_policy', 'title' => 'Политика конфиденциальности', 'title_en' => 'Privacy policy'],
            'terms' => ['type' => 'terms', 'title' => 'Условия обучения', 'title_en' => 'Training terms'],
        ];

        foreach ($pages as $slug => $content) {
            $payload = SitePage::factory()
                ->make([
                    'type' => $content['type'],
                    'slug' => $slug,
                    'title_translations' => [
                        'ru' => $content['title'],
                        'en' => $content['title_en'],
                    ],
                    'excerpt_translations' => [
                        'ru' => 'Публичная страница автошколы.',
                        'en' => 'Public driving school page.',
                    ],
                    'seo_title_translations' => [
                        'ru' => $content['title'],
                        'en' => $content['title_en'],
                    ],
                    'is_active' => true,
                    'is_indexable' => ! in_array($content['type'], ['thank_you'], true),
                    'published_at' => now(),
                ])
                ->only((new SitePage)->getFillable());

            SitePage::query()->updateOrCreate(['slug' => $slug], $payload);
        }
    }

    private function seedCourseCategories(): void
    {
        $categories = [
            'category-b' => [
                'code' => 'category_b',
                'name' => 'Категория B',
                'name_en' => 'Category B',
                'sort_order' => 10,
            ],
            'category-a' => [
                'code' => 'category_a',
                'name' => 'Категория A',
                'name_en' => 'Category A',
                'sort_order' => 20,
            ],
            'individual-lessons' => [
                'code' => 'individual_lessons',
                'name' => 'Индивидуальные уроки',
                'name_en' => 'Individual lessons',
                'sort_order' => 30,
            ],
            'exam-preparation' => [
                'code' => 'exam_preparation',
                'name' => 'Подготовка к экзамену',
                'name_en' => 'Exam preparation',
                'sort_order' => 40,
            ],
            'skill-recovery' => [
                'code' => 'skill_recovery',
                'name' => 'Восстановление навыков',
                'name_en' => 'Skill recovery',
                'sort_order' => 50,
            ],
        ];

        foreach ($categories as $slug => $content) {
            $payload = CourseCategory::factory()
                ->make([
                    'code' => $content['code'],
                    'slug' => $slug,
                    'name_translations' => [
                        'ru' => $content['name'],
                        'en' => $content['name_en'],
                    ],
                    'short_description_translations' => [
                        'ru' => 'Курсы и услуги автошколы.',
                        'en' => 'Driving school courses and services.',
                    ],
                    'is_active' => true,
                    'is_visible_on_site' => true,
                    'sort_order' => $content['sort_order'],
                ])
                ->only((new CourseCategory)->getFillable());

            CourseCategory::query()->updateOrCreate(['slug' => $slug], $payload);
        }
    }

    private function seedBranches(): void
    {
        $branches = [
            'vilnius-main' => [
                'sort_order' => 10,
                'description' => 'Филиал в центре Вильнюса для теории, практических занятий, документов и консультаций по записи.',
                'description_en' => 'Central Vilnius branch for theory, practical lessons, documents, and enrollment consultations.',
                'working_hours' => 'Пн-Пт 09:00-18:00, Сб 10:00-14:00',
                'working_hours_en' => 'Mon-Fri 09:00-18:00, Sat 10:00-14:00',
                'seo_title' => 'Филиал автошколы в Вильнюсе',
                'seo_title_en' => 'Driving school branch in Vilnius',
                'seo_description' => 'Курсы категории B, ближайшие группы, инструкторы и автопарк в филиале DrivePro Academy в Вильнюсе.',
                'seo_description_en' => 'Category B courses, upcoming groups, instructors, and fleet at the DrivePro Academy Vilnius branch.',
            ],
            'kaunas-center' => [
                'sort_order' => 20,
                'description' => 'Филиал в Каунасе для мотоциклетных и автомобильных курсов, утренних групп и консультаций.',
                'description_en' => 'Kaunas branch for motorcycle and car courses, morning groups, and consultations.',
                'working_hours' => 'Пн-Пт 08:30-17:30',
                'working_hours_en' => 'Mon-Fri 08:30-17:30',
                'seo_title' => 'Филиал автошколы в Каунасе',
                'seo_title_en' => 'Driving school branch in Kaunas',
                'seo_description' => 'Группы, инструкторы и учебные автомобили филиала DrivePro Academy в Каунасе.',
                'seo_description_en' => 'Groups, instructors, and training vehicles at the DrivePro Academy Kaunas branch.',
            ],
        ];

        foreach ($branches as $slug => $content) {
            $branch = Branch::query()->where('slug', $slug)->first();

            if ($branch === null) {
                continue;
            }

            $payload = Branch::factory()
                ->publicWebsite([
                    ...$content,
                    'name' => $branch->name,
                    'name_en' => $branch->name,
                    'city' => $branch->city,
                    'city_en' => $branch->city,
                    'address' => $branch->address,
                    'address_en' => $branch->address,
                ])
                ->make([
                    'slug' => $branch->slug,
                    'phone' => $branch->phone,
                    'email' => $branch->email,
                    'is_active' => true,
                    'sort_order' => $content['sort_order'],
                ])
                ->only((new Branch)->getFillable());

            $branch->fill($payload)->save();
        }
    }

    private function seedPrograms(): void
    {
        $programs = [
            'category-b-manual' => [
                'category_slug' => 'category-b',
                'sort_order' => 10,
                'old_price_cents' => 139000,
                'short_description' => 'Полный курс категории B с теорией, практикой, документами и подготовкой к экзамену.',
                'short_description_en' => 'Complete Category B course with theory, practice, documents, and exam preparation.',
                'description' => 'Курс категории B объединяет теорию, практические занятия, выбор группы, документы и сопровождение менеджера.',
                'description_en' => 'The Category B course combines theory, practice lessons, group selection, documents, and manager support.',
                'included_items' => 'Теория, практические уроки по программе, консультация менеджера, доступ к группе и базовая подготовка к экзамену.',
                'included_items_en' => 'Theory, scheduled practice lessons, manager consultation, group access, and core exam preparation.',
                'extra_costs' => 'Государственные пошлины, медицинская справка и дополнительные занятия оплачиваются отдельно.',
                'extra_costs_en' => 'State fees, medical certificate, and extra lessons are paid separately.',
                'theory_program' => 'Правила дорожного движения, безопасность, дорожные ситуации и подготовка к теоретическому экзамену.',
                'theory_program_en' => 'Traffic rules, safety, road situations, and theory exam preparation.',
                'practice_program' => 'Площадка, городские маршруты, парковка, перекрестки, самостоятельность и подготовка к экзамену.',
                'practice_program_en' => 'Training yard, city routes, parking, junctions, independence, and exam preparation.',
            ],
            'category-a-motorcycle' => [
                'category_slug' => 'category-a',
                'sort_order' => 20,
                'old_price_cents' => null,
                'short_description' => 'Мотоциклетный курс с маневрами, дорожной безопасностью и экзаменационными маршрутами.',
                'short_description_en' => 'Motorcycle course with maneuvers, road safety, and exam routes.',
                'description' => 'Курс категории A помогает подготовиться к управлению мотоциклом на площадке и в городе.',
                'description_en' => 'Category A prepares students for motorcycle control in the yard and in city traffic.',
                'included_items' => 'Теория, маневры, дорожная практика и подготовка к экзамену.',
                'included_items_en' => 'Theory, maneuvers, road practice, and exam preparation.',
                'extra_costs' => 'Экипировка и внешние сборы не входят в стоимость.',
                'extra_costs_en' => 'Equipment and external fees are not included.',
                'theory_program' => 'Риск, экипировка, дорожная позиция и правила для мотоциклистов.',
                'theory_program_en' => 'Risk, equipment, road position, and motorcycle-specific rules.',
                'practice_program' => 'Маневры, баланс, торможение, городские маршруты и экзаменационная готовность.',
                'practice_program_en' => 'Maneuvers, balance, braking, city routes, and exam readiness.',
            ],
        ];

        foreach ($programs as $slug => $content) {
            $program = TrainingProgram::query()->where('slug', $slug)->first();

            if ($program === null) {
                continue;
            }

            $category = CourseCategory::query()
                ->where('slug', $content['category_slug'])
                ->first();

            $payload = TrainingProgram::factory()
                ->publicCatalog([
                    ...$content,
                    'title' => $program->title,
                    'title_en' => $program->title,
                ])
                ->make([
                    'course_category_id' => $category?->id,
                    'code' => strtoupper(str_replace('-', '_', $program->slug)),
                    'name_translations' => [
                        'ru' => $program->title,
                        'en' => $program->title,
                    ],
                    'slug' => $program->slug,
                    'license_category' => $program->license_category,
                    'transmission' => $program->transmission,
                    'theory_hours' => $program->theory_hours,
                    'practice_hours' => $program->practice_hours,
                    'duration_weeks' => $program->duration_weeks,
                    'format' => $program->format,
                    'available_languages' => $program->available_languages,
                    'required_documents' => $program->required_documents,
                    'admission_requirements' => $program->admission_requirements,
                    'price_cents' => $program->price_cents,
                    'old_price_cents' => $content['old_price_cents'],
                    'price' => $program->price_cents / 100,
                    'old_price' => $content['old_price_cents'] === null ? null : $content['old_price_cents'] / 100,
                    'currency' => 'EUR',
                    'duration_translations' => [
                        'ru' => $program->duration_weeks.' недель',
                        'en' => $program->duration_weeks.' weeks',
                    ],
                    'program_summary_translations' => [
                        'ru' => $content['short_description'],
                        'en' => $content['short_description_en'],
                    ],
                    'includes_translations' => [
                        'ru' => $content['included_items'],
                        'en' => $content['included_items_en'],
                    ],
                    'excludes_translations' => [
                        'ru' => $content['extra_costs'],
                        'en' => $content['extra_costs_en'],
                    ],
                    'requirements_translations' => [
                        'ru' => $program->admission_requirements,
                        'en' => $program->admission_requirements,
                    ],
                    'is_active' => true,
                    'is_visible_on_site' => true,
                    'is_featured' => $slug === 'category-b-manual',
                    'open_graph_image' => $program->open_graph_image,
                    'og_image' => $program->open_graph_image,
                    'structured_data' => $program->structured_data,
                    'sort_order' => $content['sort_order'],
                ])
                ->only((new TrainingProgram)->getFillable());

            $program->fill($payload)->save();
        }
    }

    private function seedPricingPackages(): void
    {
        $program = TrainingProgram::query()->where('slug', 'category-b-manual')->first();
        $category = CourseCategory::query()->where('slug', 'category-b')->first();

        if ($program === null || $category === null) {
            return;
        }

        $packages = [
            'category-b-standard' => ['code' => 'standard', 'name' => 'Standard', 'price' => 1290.00, 'sort_order' => 10],
            'category-b-premium' => ['code' => 'premium', 'name' => 'Premium', 'price' => 1490.00, 'sort_order' => 20],
            'category-b-intensive' => ['code' => 'intensive', 'name' => 'Intensive', 'price' => 1590.00, 'sort_order' => 30],
            'extra-lessons' => ['code' => 'extra_lessons', 'name' => 'Extra Lessons', 'price' => 45.00, 'sort_order' => 40],
        ];

        foreach ($packages as $slug => $content) {
            $payload = PricingPackage::factory()
                ->make([
                    'course_id' => $program->id,
                    'course_category_id' => $category->id,
                    'code' => $content['code'],
                    'slug' => $slug,
                    'name_translations' => [
                        'ru' => $content['name'],
                        'en' => $content['name'],
                    ],
                    'price' => $content['price'],
                    'currency' => 'EUR',
                    'is_active' => true,
                    'is_visible_on_site' => true,
                    'is_featured' => $content['code'] === 'premium',
                    'sort_order' => $content['sort_order'],
                ])
                ->only((new PricingPackage)->getFillable());

            PricingPackage::query()->updateOrCreate(['slug' => $slug], $payload);
        }
    }

    private function seedGroups(): void
    {
        $groups = [
            'B-VNO-001' => [
                'places_taken' => 5,
                'name_translations' => [
                    'ru' => 'Вечерняя группа категории B',
                    'en' => 'Evening Category B Group',
                ],
            ],
            'A-KAU-001' => [
                'places_taken' => 2,
                'name_translations' => [
                    'ru' => 'Утренняя мотоциклетная группа',
                    'en' => 'Morning Motorcycle Group',
                ],
            ],
        ];

        foreach ($groups as $code => $content) {
            $group = TrainingGroup::query()
                ->with('trainingProgram')
                ->where('code', $code)
                ->first();

            if ($group === null) {
                continue;
            }

            $payload = TrainingGroup::factory()
                ->publicVisible()
                ->make([
                    'group_number' => $group->code,
                    'branch_id' => $group->branch_id,
                    'training_program_id' => $group->training_program_id,
                    'course_category_id' => $group->trainingProgram?->course_category_id,
                    'instructor_id' => $group->instructor_id,
                    'name' => $content['name_translations']['ru'],
                    'code' => $group->code,
                    'status' => GroupStatus::Recruiting,
                    'capacity' => $group->capacity,
                    'places_taken' => $content['places_taken'],
                    'starts_on' => $group->starts_on,
                    'ends_on' => $group->ends_on,
                    'meeting_days' => $group->meeting_days,
                    'meeting_time' => $group->meeting_time,
                    'end_time' => '20:00',
                    'classroom' => $group->classroom,
                    'is_visible_on_site' => true,
                    'is_featured' => $code === 'B-VNO-001',
                    'sort_order' => $code === 'B-VNO-001' ? 10 : 20,
                    'name_translations' => $content['name_translations'],
                    'schedule_summary_translations' => [
                        'ru' => 'Занятия вечером два раза в неделю.',
                        'en' => 'Evening classes twice per week.',
                    ],
                ])
                ->only((new TrainingGroup)->getFillable());

            $group->fill($payload)->save();
        }
    }

    private function seedFaqs(): void
    {
        $program = TrainingProgram::query()->where('slug', 'category-b-manual')->first();

        $items = [
            [
                'faqable' => $program,
                'sort_order' => 10,
                'question' => 'Можно ли выбрать группу?',
                'question_en' => 'Can I choose a group?',
                'answer' => 'Да, менеджер поможет подобрать подходящую группу и филиал.',
                'answer_en' => 'Yes, a manager will help you choose a suitable group and branch.',
            ],
            [
                'faqable' => null,
                'sort_order' => 20,
                'question' => 'Как оставить заявку?',
                'question_en' => 'How do I send an application?',
                'answer' => 'Заполните форму на сайте, и заявка сразу появится в CRM.',
                'answer_en' => 'Submit the website form and the lead will appear in CRM immediately.',
            ],
        ];

        foreach ($items as $item) {
            $payload = Faq::factory()
                ->make([
                    'faqable_type' => $item['faqable']?->getMorphClass(),
                    'faqable_id' => $item['faqable']?->getKey(),
                    'question_translations' => [
                        'ru' => $item['question'],
                        'en' => $item['question_en'],
                    ],
                    'answer_translations' => [
                        'ru' => $item['answer'],
                        'en' => $item['answer_en'],
                    ],
                    'is_active' => true,
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

    private function seedSiteSettings(): void
    {
        $settings = [
            'default_phone' => ['group' => 'contacts', 'value' => '+370 600 00000', 'is_public' => true],
            'default_email' => ['group' => 'contacts', 'value' => 'info@drivepro.test', 'is_public' => true],
            'default_currency' => ['group' => 'pricing', 'value' => 'EUR', 'is_public' => true],
            'analytics_enabled' => ['group' => 'tracking', 'value' => false, 'is_public' => false],
            'cookie_notice_enabled' => ['group' => 'tracking', 'value' => true, 'is_public' => true],
        ];

        foreach ($settings as $key => $content) {
            $payload = SiteSetting::factory()
                ->make([
                    'key' => $key,
                    'group' => $content['group'],
                    'value' => $content['value'],
                    'description' => 'Public website setting.',
                    'is_public' => $content['is_public'],
                ])
                ->only((new SiteSetting)->getFillable());

            SiteSetting::query()->updateOrCreate(['key' => $key], $payload);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Enums\GroupStatus;
use App\Models\Branch;
use App\Models\TrainingGroup;
use App\Models\TrainingProgram;
use Illuminate\Database\Seeder;

class PublicWebsiteSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedBranches();
        $this->seedPrograms();
        $this->seedGroups();
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

            $payload = TrainingProgram::factory()
                ->publicCatalog([
                    ...$content,
                    'title' => $program->title,
                    'title_en' => $program->title,
                ])
                ->make([
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
                    'is_active' => true,
                    'open_graph_image' => $program->open_graph_image,
                    'structured_data' => $program->structured_data,
                    'sort_order' => $content['sort_order'],
                ])
                ->only((new TrainingProgram)->getFillable());

            $program->fill($payload)->save();
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
            $group = TrainingGroup::query()->where('code', $code)->first();

            if ($group === null) {
                continue;
            }

            $payload = TrainingGroup::factory()
                ->publicVisible()
                ->make([
                    'branch_id' => $group->branch_id,
                    'training_program_id' => $group->training_program_id,
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
                    'classroom' => $group->classroom,
                    'is_visible_on_site' => true,
                    'name_translations' => $content['name_translations'],
                ])
                ->only((new TrainingGroup)->getFillable());

            $group->fill($payload)->save();
        }
    }
}

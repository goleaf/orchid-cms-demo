<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadTag;
use App\Models\LeadTask;
use App\Models\TrainingGroup;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class CrmDemoLeadSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CrmStatusSeeder::class,
            CrmSourceSeeder::class,
            CrmLostReasonSeeder::class,
            CrmTagSeeder::class,
        ]);

        $context = $this->context();

        $original = $this->updateLead(
            'crm-demo-original@drivepro.test',
            Lead::factory()
                ->manual()
                ->contacted()
                ->withCourse($context['course'])
                ->withBranch($context['branch'])
                ->withConsent()
                ->make([
                    'first_name' => 'Demo',
                    'last_name' => 'Original',
                    'full_name' => 'Demo Original',
                    'email' => 'crm-demo-original@drivepro.test',
                    'phone' => '+370 600 70000',
                ]),
        );

        $newWebsite = $this->updateLead(
            'crm-demo-new-website@drivepro.test',
            Lead::factory()
                ->fromWebsite()
                ->newLead()
                ->withCourse($context['course'])
                ->withBranch($context['branch'])
                ->withTrainingGroup($context['group'])
                ->withConsent()
                ->make([
                    'first_name' => 'Demo',
                    'last_name' => 'Website',
                    'full_name' => 'Demo Website',
                    'email' => 'crm-demo-new-website@drivepro.test',
                    'phone' => '+370 600 70001',
                ]),
        );

        $this->updateLead('crm-demo-callback@drivepro.test', Lead::factory()->callback()->newLead()->withConsent()->make([
            'first_name' => 'Demo',
            'last_name' => 'Callback',
            'full_name' => 'Demo Callback',
            'email' => 'crm-demo-callback@drivepro.test',
            'phone' => '+370 600 70002',
        ]));

        $this->updateLead('crm-demo-contacted@drivepro.test', Lead::factory()->manual()->contacted()->assigned($context['manager'])->withConsent()->make([
            'first_name' => 'Demo',
            'last_name' => 'Contacted',
            'full_name' => 'Demo Contacted',
            'email' => 'crm-demo-contacted@drivepro.test',
            'phone' => '+370 600 70003',
        ]));

        $this->updateLead('crm-demo-waiting-documents@drivepro.test', Lead::factory()->waitingDocuments()->withConsent()->make([
            'first_name' => 'Demo',
            'last_name' => 'Documents',
            'full_name' => 'Demo Documents',
            'email' => 'crm-demo-waiting-documents@drivepro.test',
            'phone' => '+370 600 70004',
        ]));

        $this->updateLead('crm-demo-waiting-payment@drivepro.test', Lead::factory()->waitingPayment()->withConsent()->make([
            'first_name' => 'Demo',
            'last_name' => 'Payment',
            'full_name' => 'Demo Payment',
            'email' => 'crm-demo-waiting-payment@drivepro.test',
            'phone' => '+370 600 70005',
        ]));

        $this->updateLead('crm-demo-duplicate@drivepro.test', Lead::factory()->duplicate($original)->withConsent()->make([
            'first_name' => 'Demo',
            'last_name' => 'Duplicate',
            'full_name' => 'Demo Duplicate',
            'email' => 'crm-demo-duplicate@drivepro.test',
            'phone' => '+370 600 70000',
        ]));

        $this->updateLead('crm-demo-lost@drivepro.test', Lead::factory()->lost()->withConsent()->make([
            'first_name' => 'Demo',
            'last_name' => 'Lost',
            'full_name' => 'Demo Lost',
            'email' => 'crm-demo-lost@drivepro.test',
            'phone' => '+370 600 70006',
        ]));

        $this->updateLead('crm-demo-spam@drivepro.test', Lead::factory()->spam()->withoutConsent()->make([
            'first_name' => 'Demo',
            'last_name' => 'Spam',
            'full_name' => 'Demo Spam',
            'email' => 'crm-demo-spam@drivepro.test',
            'phone' => '+370 600 70007',
        ]));

        $overdue = $this->updateLead('crm-demo-overdue@drivepro.test', Lead::factory()->overdue()->assigned($context['manager'])->withConsent()->make([
            'first_name' => 'Demo',
            'last_name' => 'Overdue',
            'full_name' => 'Demo Overdue',
            'email' => 'crm-demo-overdue@drivepro.test',
            'phone' => '+370 600 70008',
        ]));

        $utm = $this->updateLead('crm-demo-utm@drivepro.test', Lead::factory()->fromWebsite()->withUtm()->withConsent()->hot()->make([
            'first_name' => 'Demo',
            'last_name' => 'UTM',
            'full_name' => 'Demo UTM',
            'email' => 'crm-demo-utm@drivepro.test',
            'phone' => '+370 600 70009',
        ]));

        $hotTag = LeadTag::query()->where('slug', 'hot')->first();
        if ($hotTag !== null) {
            $utm->tags()->syncWithoutDetaching([$hotTag->id]);
        }

        $this->seedTask($overdue, LeadTask::factory()->overdue()->high()->make([
            'marketing_lead_id' => $overdue->id,
            'title' => 'CRM demo overdue follow-up',
            'title_translations' => [
                'ru' => 'CRM demo overdue follow-up',
                'en' => 'CRM demo overdue follow-up',
                'lt' => 'CRM demo overdue follow-up',
                'pl' => 'CRM demo overdue follow-up',
            ],
        ]));

        $this->seedTask($newWebsite, LeadTask::factory()->open()->normal()->make([
            'marketing_lead_id' => $newWebsite->id,
            'title' => 'CRM demo contact website lead',
            'title_translations' => [
                'ru' => 'CRM demo contact website lead',
                'en' => 'CRM demo contact website lead',
                'lt' => 'CRM demo contact website lead',
                'pl' => 'CRM demo contact website lead',
            ],
        ]));

        foreach ([
            LeadActivity::factory()->createdFromWebsite()->make(['marketing_lead_id' => $utm->id]),
            LeadActivity::factory()->statusChanged()->make(['marketing_lead_id' => $utm->id]),
            LeadActivity::factory()->taskCreated()->make(['marketing_lead_id' => $utm->id]),
            LeadActivity::factory()->noteAdded()->make([
                'marketing_lead_id' => $utm->id,
                'body' => 'CRM demo activity note.',
            ]),
        ] as $activity) {
            $this->seedActivity($utm, $activity);
        }
    }

    /**
     * @return array{branch: Branch, category: CourseCategory, course: Course, group: TrainingGroup, manager: User}
     */
    private function context(): array
    {
        $manager = $this->updateModel(User::class, ['email' => 'crm-demo-manager@drivepro.test'], User::factory()->make([
            'name' => 'CRM Demo Manager',
            'email' => 'crm-demo-manager@drivepro.test',
        ]));

        $branch = $this->updateModel(Branch::class, ['slug' => 'crm-demo-vilnius'], Branch::factory()
            ->translated()
            ->withContacts()
            ->make([
                'code' => 'CRM-DEMO-VILNIUS',
                'slug' => 'crm-demo-vilnius',
            ]));

        $category = $this->updateModel(CourseCategory::class, ['slug' => 'crm-demo-category-b'], CourseCategory::factory()
            ->categoryB()
            ->translated()
            ->make([
                'code' => 'CRM_DEMO_CATEGORY_B',
                'slug' => 'crm-demo-category-b',
            ]));

        $course = $this->updateModel(Course::class, ['slug' => 'crm-demo-category-b-course'], Course::factory()
            ->categoryB()
            ->translated()
            ->withPrice(1290)
            ->make([
                'course_category_id' => $category->id,
                'code' => 'CRM_DEMO_CATEGORY_B_COURSE',
                'slug' => 'crm-demo-category-b-course',
            ]));

        $group = $this->updateModel(TrainingGroup::class, ['group_number' => 'CRM-DEMO-B-01'], TrainingGroup::factory()
            ->recruiting()
            ->visibleOnSite()
            ->startingSoon()
            ->evening()
            ->withCapacity(12, 4)
            ->translated()
            ->make([
                'group_number' => 'CRM-DEMO-B-01',
                'code' => 'CRM-DEMO-B-01',
                'branch_id' => $branch->id,
                'training_program_id' => $course->id,
                'course_category_id' => $category->id,
            ]));

        return [
            'branch' => $branch,
            'category' => $category,
            'course' => $course,
            'group' => $group,
            'manager' => $manager,
        ];
    }

    private function updateLead(string $email, Lead $lead): Lead
    {
        /** @var Lead $saved */
        $saved = Lead::query()->updateOrCreate(
            ['email' => $email],
            $this->payload($lead),
        );

        return $saved;
    }

    /**
     * @template TModel of Model
     *
     * @param  class-string<TModel>  $modelClass
     * @param  array<string, mixed>  $lookup
     * @param  TModel  $model
     * @return TModel
     */
    private function updateModel(string $modelClass, array $lookup, Model $model): Model
    {
        /** @var TModel $saved */
        $saved = $modelClass::query()->updateOrCreate(
            $lookup,
            $this->payload($model),
        );

        return $saved;
    }

    private function seedTask(Lead $lead, LeadTask $task): void
    {
        $task->marketing_lead_id = $lead->id;

        LeadTask::query()->firstOrCreate(
            [
                'marketing_lead_id' => $lead->id,
                'title' => $task->title,
            ],
            $this->payload($task),
        );
    }

    private function seedActivity(Lead $lead, LeadActivity $activity): void
    {
        $activity->marketing_lead_id = $lead->id;

        LeadActivity::query()->firstOrCreate(
            [
                'marketing_lead_id' => $lead->id,
                'type' => $activity->type,
            ],
            $this->payload($activity),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Model $model): array
    {
        return $model->only($model->getFillable());
    }
}

<?php

namespace Tests\Feature;

use App\Actions\GenerateLeadNumberAction;
use App\Actions\NormalizeLeadPhoneAction;
use App\Actions\PrepareLeadForStudentConversionAction;
use App\Enums\LeadStatus as LeadStatusEnum;
use App\Models\Lead;
use App\Models\LeadLostReason;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\LeadTag;
use App\Models\User;
use App\Rules\ActiveLeadLostReasonRule;
use App\Rules\ActiveLeadSourceRule;
use App\Rules\ActiveLeadStatusRule;
use App\Rules\ActiveLeadTagRule;
use App\Rules\DictionaryCodeRule;
use App\Rules\FutureFollowUpDateRule;
use App\Rules\LeadCanBeConvertedRule;
use App\Rules\LeadCanBeUpdatedRule;
use App\Rules\LeadIsNotDuplicateOfItselfRule;
use App\Rules\PhoneOrEmailRequiredRule;
use App\Rules\TranslatedDictionaryNameRequiredRule;
use App\Rules\ValidLeadCallResultRule;
use App\Rules\ValidLeadPriorityRule;
use App\Rules\ValidLeadStatusTransitionRule;
use App\Rules\ValidLeadTaskPriorityRule;
use App\Rules\ValidLeadTaskStatusRule;
use Database\Seeders\CrmDictionarySeeder;
use Database\Seeders\CrmTranslationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CrmFinalHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CrmTranslationSeeder::class);
        $this->seed(CrmDictionarySeeder::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_number_phone_and_conversion_guard_actions_work(): void
    {
        Carbon::setTestNow('2026-05-28 12:00:00');

        Lead::factory()->create(['lead_number' => 'LEAD-2026-0001']);

        $this->assertSame(
            'LEAD-2026-0002',
            app(GenerateLeadNumberAction::class)->handle(now()),
        );
        $this->assertSame(
            '+37060011122',
            app(NormalizeLeadPhoneAction::class)->handle('00 370 (600) 111-22'),
        );

        $lead = Lead::factory()->contacted()->create();

        $result = app(PrepareLeadForStudentConversionAction::class)->handle($lead, $this->userWithPermissions([
            'crm.leads.convert',
        ]));

        $this->assertTrue($result['ready']);
        $this->assertSame(LeadStatusEnum::ReadyToEnroll, $result['lead']->status);
        $this->assertSame(tkey('crm.leads.messages.student_module_next_block'), $result['message']);

        $this->expectException(ValidationException::class);

        app(PrepareLeadForStudentConversionAction::class)->handle(Lead::factory()->converted()->create());
    }

    public function test_required_crm_rules_return_translated_errors(): void
    {
        $lead = Lead::factory()->newLead()->create();
        $convertedLead = Lead::factory()->converted()->create();
        $inactiveStatus = LeadStatus::factory()->create(['code' => 'inactive_status', 'is_active' => false]);
        $inactiveSource = LeadSource::factory()->create(['code' => 'inactive_source', 'is_active' => false]);
        $inactiveLostReason = LeadLostReason::factory()->create(['code' => 'inactive_reason', 'is_active' => false]);
        $inactiveTag = LeadTag::factory()->create(['slug' => 'inactive_tag', 'is_active' => false]);

        $validator = Validator::make([
            'contact' => null,
            'phone' => null,
            'email' => null,
            'transition' => LeadStatusEnum::ReadyToEnroll->value,
            'status' => $inactiveStatus->code,
            'source' => $inactiveSource->code,
            'lost_reason' => $inactiveLostReason->code,
            'tag' => $inactiveTag->id,
            'lead_priority' => 'later',
            'task_status' => 'waiting',
            'task_priority' => 'critical',
            'call_result' => 'busy',
            'follow_up' => now()->subMinute()->toDateTimeString(),
            'name_translations' => ['en' => 'English only'],
            'code' => 'Invalid Code',
            'converted' => true,
            'updatable' => true,
            'duplicate_of' => $lead->id,
        ], [
            'contact' => [new PhoneOrEmailRequiredRule('phone', 'email', 'crm.leads.validation.phone_or_email_required')],
            'transition' => [new ValidLeadStatusTransitionRule($lead, $this->userWithPermissions())],
            'status' => [new ActiveLeadStatusRule],
            'source' => [new ActiveLeadSourceRule],
            'lost_reason' => [new ActiveLeadLostReasonRule],
            'tag' => [new ActiveLeadTagRule],
            'lead_priority' => [new ValidLeadPriorityRule],
            'task_status' => [new ValidLeadTaskStatusRule],
            'task_priority' => [new ValidLeadTaskPriorityRule],
            'call_result' => [new ValidLeadCallResultRule],
            'follow_up' => [new FutureFollowUpDateRule],
            'name_translations' => [new TranslatedDictionaryNameRequiredRule],
            'code' => [new DictionaryCodeRule],
            'converted' => [new LeadCanBeConvertedRule($convertedLead)],
            'updatable' => [new LeadCanBeUpdatedRule($convertedLead, $this->userWithPermissions())],
            'duplicate_of' => [new LeadIsNotDuplicateOfItselfRule($lead->id)],
        ]);

        $this->assertTrue($validator->fails());
        $this->assertSame(tkey('crm.leads.validation.phone_or_email_required'), $validator->errors()->first('contact'));
        $this->assertSame(tkey('crm.leads.validation.invalid_status_transition'), $validator->errors()->first('transition'));
        $this->assertSame(tkey('crm.leads.validation.status_not_active'), $validator->errors()->first('status'));
        $this->assertSame(tkey('crm.leads.validation.source_not_active'), $validator->errors()->first('source'));
        $this->assertSame(tkey('crm.leads.validation.lost_reason_not_active'), $validator->errors()->first('lost_reason'));
        $this->assertSame(tkey('crm.leads.validation.tag_not_active'), $validator->errors()->first('tag'));
        $this->assertSame(tkey('crm.leads.validation.invalid_priority'), $validator->errors()->first('lead_priority'));
        $this->assertSame(tkey('crm.leads.validation.invalid_task_status'), $validator->errors()->first('task_status'));
        $this->assertSame(tkey('crm.leads.validation.invalid_task_priority'), $validator->errors()->first('task_priority'));
        $this->assertSame(tkey('crm.leads.validation.invalid_call_result'), $validator->errors()->first('call_result'));
        $this->assertSame(tkey('crm.leads.validation.follow_up_must_be_future'), $validator->errors()->first('follow_up'));
        $this->assertSame(tkey('crm.leads.validation.default_translation_required'), $validator->errors()->first('name_translations'));
        $this->assertSame(tkey('crm.leads.validation.invalid_dictionary_code'), $validator->errors()->first('code'));
        $this->assertSame(tkey('crm.leads.validation.lead_cannot_be_converted'), $validator->errors()->first('converted'));
        $this->assertSame(tkey('crm.leads.validation.lead_already_converted'), $validator->errors()->first('updatable'));
        $this->assertSame(tkey('crm.leads.validation.cannot_duplicate_itself'), $validator->errors()->first('duplicate_of'));
    }

    public function test_lead_edit_screen_requires_crm_write_permission(): void
    {
        $lead = Lead::factory()->create(['lead_number' => 'LEAD-HARDEN-EDIT']);

        $this->actingAs($this->userWithPermissions(['crm.leads.view']))
            ->get(route('platform.crm.leads.edit', $lead))
            ->assertForbidden();

        $this->actingAs($this->userWithPermissions(['crm.leads.update']))
            ->get(route('platform.crm.leads.edit', $lead))
            ->assertOk()
            ->assertSee('LEAD-HARDEN-EDIT');
    }

    /**
     * @param  array<int, string>  $permissions
     */
    private function userWithPermissions(array $permissions = []): User
    {
        $user = User::factory()->create();

        $user->forceFill([
            'permissions' => collect(['platform.index', 'platform.main'])
                ->merge($permissions)
                ->mapWithKeys(fn (string $permission): array => [$permission => true])
                ->all(),
        ])->save();

        return $user;
    }
}

<?php

namespace Tests\Feature;

use App\Models\LeadLostReason;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\LeadTag;
use App\Models\TranslationString;
use Database\Seeders\CrmDictionarySeeder;
use Database\Seeders\CrmTranslationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmTranslationModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_required_crm_translation_keys_resolve_for_active_languages(): void
    {
        $this->seed(CrmTranslationSeeder::class);

        $keys = $this->requiredCrmKeys();
        $strings = TranslationString::query()
            ->with(['values:id,translation_string_id,language_code,value'])
            ->whereIn('key', $keys)
            ->get(['id', 'key'])
            ->keyBy('key');

        $this->assertSame([], array_values(array_diff($keys, $strings->keys()->all())));

        foreach ($keys as $key) {
            $values = $strings[$key]->values->pluck('value', 'language_code');

            foreach (['ru', 'en', 'lt', 'pl'] as $locale) {
                $this->assertTrue($values->has($locale), "{$key} is missing {$locale}");
                $this->assertNotSame('', trim((string) $values[$locale]), "{$key} has empty {$locale}");
                $this->assertNotSame($key, (string) $values[$locale], "{$key} fell back to its key for {$locale}");
            }
        }
    }

    public function test_crm_validation_messages_and_attributes_are_translated(): void
    {
        $this->seed(CrmTranslationSeeder::class);

        $this->assertSame('Enter a phone number or email.', tkey('crm.leads.validation.phone_or_email_required', [], 'en'));
        $this->assertSame('Укажите телефон или email.', tkey('crm.leads.validation.phone_or_email_required', [], 'ru'));
        $this->assertSame('Lead export is not allowed.', tkey('crm.leads.validation.export_not_allowed', [], 'en'));
        $this->assertSame('full name', tkey('validation.attributes.lead.full_name', [], 'en'));
        $this->assertSame('срок задачи', tkey('validation.attributes.lead_task.due_at', [], 'ru'));
    }

    public function test_dictionary_display_names_use_current_locale(): void
    {
        $this->seed(CrmTranslationSeeder::class);
        $this->seed(CrmDictionarySeeder::class);

        app()->setLocale('ru');
        $this->assertSame('Новая заявка', LeadStatus::query()->where('code', 'new')->firstOrFail()->display_name);
        $this->assertSame('Сайт', LeadSource::query()->where('code', 'website')->firstOrFail()->display_name);
        $this->assertSame('Цена', LeadLostReason::query()->where('code', 'price')->firstOrFail()->display_name);
        $this->assertSame('Горячий', LeadTag::query()->where('slug', 'hot')->firstOrFail()->display_name);

        app()->setLocale('en');
        $this->assertSame('New lead', LeadStatus::query()->where('code', 'new')->firstOrFail()->display_name);
        $this->assertSame('Website', LeadSource::query()->where('code', 'website')->firstOrFail()->display_name);
        $this->assertSame('Price', LeadLostReason::query()->where('code', 'price')->firstOrFail()->display_name);
        $this->assertSame('Hot', LeadTag::query()->where('slug', 'hot')->firstOrFail()->display_name);
    }

    public function test_missing_dictionary_translation_falls_back_safely(): void
    {
        $this->seed(CrmTranslationSeeder::class);

        $tag = LeadTag::factory()->create([
            'slug' => 'fallback-check',
            'name' => 'Fallback tag',
            'name_translations' => [
                'ru' => 'Резервная метка',
            ],
        ]);

        app()->setLocale('en');

        $this->assertSame('Резервная метка', $tag->display_name);
    }

    /**
     * @return array<int, string>
     */
    private function requiredCrmKeys(): array
    {
        return [
            'menu.crm',
            ...$this->prefixed('menu.crm.', [
                'leads',
                'new_leads',
                'my_leads',
                'unassigned',
                'overdue_tasks',
                'pipeline',
                'tasks',
                'statuses',
                'sources',
                'lost_reasons',
                'tags',
                'settings',
            ]),
            ...$this->prefixed('crm.leads.', [
                'title',
                'create_title',
                'edit_title',
                'view_title',
            ]),
            ...$this->prefixed('crm.leads.empty.', [
                'no_leads',
                'no_tasks',
                'no_activities',
                'no_duplicates',
            ]),
            ...$this->prefixed('crm.leads.sections.', [
                'main_information',
                'contact_information',
                'training_interest',
                'crm_information',
                'marketing_data',
                'consent_data',
                'tasks',
                'activities',
                'duplicates',
                'system_data',
                'conversion',
            ]),
            ...$this->prefixed('crm.leads.fields.', [
                'id',
                'uuid',
                'lead_number',
                'full_name',
                'first_name',
                'last_name',
                'middle_name',
                'phone',
                'normalized_phone',
                'email',
                'preferred_messenger',
                'city',
                'locale',
                'comment',
                'internal_comment',
                'status',
                'source',
                'manager',
                'lost_reason',
                'duplicate_of',
                'course',
                'course_category',
                'branch',
                'training_group',
                'desired_start_date',
                'preferred_time',
                'preferred_training_language',
                'preferred_gearbox',
                'budget',
                'priority',
                'lead_score',
                'last_contacted_at',
                'next_follow_up_at',
                'closed_at',
                'converted_at',
                'converted_student',
                'converted_enrollment',
                'consent_accepted',
                'consent_accepted_at',
                'consent_text_version',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
                'utm_source',
                'utm_medium',
                'utm_campaign',
                'utm_content',
                'utm_term',
                'referrer',
                'landing_page',
                'form_page',
                'form_name',
                'ip_address',
                'user_agent',
            ]),
            ...$this->prefixed('crm.leads.actions.', [
                'create',
                'save',
                'save_and_return',
                'open',
                'edit',
                'delete',
                'archive',
                'change_status',
                'assign_manager',
                'add_note',
                'log_call',
                'create_task',
                'complete_task',
                'cancel_task',
                'mark_lost',
                'mark_duplicate',
                'mark_spam',
                'reopen',
                'prepare_conversion',
                'convert_to_student',
                'export_csv',
                'clear_filters',
            ]),
            ...$this->prefixed('crm.leads.messages.', [
                'created',
                'updated',
                'deleted',
                'archived',
                'status_changed',
                'manager_assigned',
                'note_added',
                'call_logged',
                'task_created',
                'task_completed',
                'task_cancelled',
                'marked_lost',
                'marked_duplicate',
                'marked_spam',
                'reopened',
                'prepared_for_conversion',
                'export_started',
                'duplicate_detected',
            ]),
            ...$this->prefixed('crm.leads.statuses.', [
                'new',
                'no_answer',
                'contacted',
                'consultation',
                'waiting_documents',
                'waiting_payment',
                'ready_to_enroll',
                'enrolled',
                'lost',
                'duplicate',
                'spam',
                'archived',
            ]),
            ...$this->prefixed('crm.leads.sources.', [
                'website',
                'callback',
                'contact_form',
                'phone',
                'office',
                'google_ads',
                'facebook',
                'instagram',
                'tiktok',
                'telegram',
                'whatsapp',
                'referral',
                'partner',
                'other',
            ]),
            ...$this->prefixed('crm.leads.lost_reasons.', [
                'price',
                'schedule',
                'location',
                'competitor',
                'no_response',
                'changed_mind',
                'documents',
                'payment',
                'language',
                'car_type',
                'duplicate',
                'spam',
                'other',
            ]),
            ...$this->prefixed('crm.leads.tags.', [
                'hot',
                'vip',
                'needs_call',
                'ready_to_pay',
                'needs_documents',
                'repeat_request',
                'problematic',
                'thinking',
                'urgent',
                'individual_schedule',
                'wants_automatic',
                'wants_manual',
                'evening_training',
                'weekend_training',
                'corporate_client',
            ]),
            ...$this->prefixed('crm.leads.priorities.', [
                'low',
                'normal',
                'high',
                'urgent',
            ]),
            'crm.tasks.title',
            'crm.tasks.empty.no_tasks',
            ...$this->prefixed('crm.tasks.fields.', [
                'title',
                'description',
                'assigned_to',
                'created_by',
                'priority',
                'status',
                'due_at',
                'completed_at',
                'cancelled_at',
                'created_at',
            ]),
            ...$this->prefixed('crm.tasks.statuses.', [
                'open',
                'in_progress',
                'done',
                'cancelled',
            ]),
            ...$this->prefixed('crm.tasks.defaults.', [
                'contact_new_website_lead',
                'contact_new_manual_lead',
            ]),
            'crm.calls.title',
            ...$this->prefixed('crm.calls.fields.', [
                'result',
                'duration_seconds',
                'comment',
                'next_follow_up_at',
            ]),
            ...$this->prefixed('crm.calls.results.', [
                'reached',
                'no_answer',
                'wrong_number',
                'call_back_later',
                'thinking',
                'ready_to_pay',
                'refused',
            ]),
            ...$this->prefixed('crm.activities.fields.', [
                'type',
                'body',
                'change',
                'meta',
            ]),
            ...$this->prefixed('crm.activities.meta.', [
                'communication_id',
                'channel',
                'direction',
                'call_result',
                'task_id',
                'comment_id',
            ]),
            ...$this->prefixed('crm.activities.types.', [
                'created',
                'created_from_website',
                'created_manually',
                'status_changed',
                'manager_assigned',
                'note_added',
                'call_logged',
                'email_logged',
                'messenger_logged',
                'task_created',
                'task_completed',
                'marked_duplicate',
                'marked_lost',
                'marked_spam',
                'reopened',
                'converted',
                'archived',
                'updated',
            ]),
            ...$this->prefixed('crm.leads.filters.', [
                'search',
                'status',
                'source',
                'manager',
                'course',
                'course_category',
                'branch',
                'training_group',
                'tag',
                'lost_reason',
                'all_lost_reasons',
                'priority',
                'created_from',
                'created_to',
                'next_follow_up_from',
                'next_follow_up_to',
                'last_contacted_from',
                'last_contacted_to',
                'utm_source',
                'utm_medium',
                'utm_campaign',
                'form_name',
                'only_my',
                'only_unassigned',
                'only_due_today',
                'only_overdue',
                'only_duplicates',
                'only_open',
                'only_closed',
                'only_converted',
                'only_not_converted',
            ]),
            ...$this->prefixed('crm.leads.segments.', [
                'all',
                'new',
                'my_leads',
                'unassigned',
                'call_today',
                'overdue',
                'waiting_payment',
                'waiting_documents',
                'hot',
                'duplicates',
                'lost',
                'spam',
                'converted',
                'ready_to_enroll',
                'not_converted',
            ]),
            ...$this->prefixed('crm.pipeline.', [
                'title',
                'empty.no_leads',
                'actions.change_status',
                'actions.open_lead',
            ]),
            ...$this->prefixed('crm.dictionaries.', [
                'statuses.title',
                'sources.title',
                'lost_reasons.title',
                'tags.title',
            ]),
            ...$this->prefixed('crm.dictionaries.fields.', [
                'code',
                'name',
                'description',
                'color',
                'sort_order',
                'is_default',
                'is_active',
                'is_final',
                'is_success',
                'is_lost',
                'is_duplicate',
                'is_spam',
            ]),
            ...$this->prefixed('crm.dictionaries.actions.', [
                'create',
                'save',
                'delete',
            ]),
            ...$this->prefixed('crm.dictionaries.messages.', [
                'created',
                'updated',
                'deleted',
                'cannot_delete_used_item',
            ]),
            ...$this->prefixed('crm.validation.', [
                'dictionary_key_required',
                'dictionary_key_unique',
                'dictionary_record_unavailable',
                'dictionary_system_record_locked',
                'dictionary_system_code_locked',
                'dictionary_default_status_inactive',
                'dictionary_default_status_required',
                'dictionary_final_status_locked',
            ]),
            ...$this->prefixed('crm.leads.validation.', [
                'phone_or_email_required',
                'invalid_status_transition',
                'lead_cannot_be_updated',
                'lead_cannot_be_converted',
                'lead_already_converted',
                'lead_is_spam',
                'lead_is_duplicate',
                'lead_is_lost',
                'duplicate_original_required',
                'cannot_duplicate_itself',
                'status_not_active',
                'source_not_active',
                'lost_reason_not_active',
                'tag_not_active',
                'invalid_priority',
                'invalid_task_status',
                'invalid_task_priority',
                'invalid_call_result',
                'follow_up_must_be_future',
                'default_translation_required',
                'invalid_dictionary_code',
                'marketing_access_denied',
                'lost_reason_required',
                'manager_required',
                'task_due_date_invalid',
                'export_not_allowed',
            ]),
            ...$this->prefixed('permissions.crm.leads.', [
                'view',
                'create',
                'update',
                'delete',
                'archive',
                'assign',
                'change_status',
                'override_status_transition',
                'manage_dictionaries',
                'view_marketing',
                'convert',
                'export',
                'manage_tasks',
                'manage_tags',
            ]),
            'permissions.crm.pipeline.view',
            ...$this->prefixed('validation.attributes.lead.', [
                'full_name',
                'phone',
                'email',
                'status_id',
                'source_id',
                'manager_id',
                'lost_reason_id',
                'course_id',
                'branch_id',
                'training_group_id',
                'next_follow_up_at',
            ]),
            ...$this->prefixed('validation.attributes.lead_task.', [
                'title_translations',
                'due_at',
            ]),
            ...$this->prefixed('validation.attributes.lead_status.', [
                'code',
                'name_translations',
            ]),
        ];
    }

    /**
     * @param  array<int, string>  $suffixes
     * @return array<int, string>
     */
    private function prefixed(string $prefix, array $suffixes): array
    {
        return array_map(fn (string $suffix): string => $prefix.$suffix, $suffixes);
    }
}

<?php

namespace Tests\Feature;

use App\Actions\DeleteLeadDictionaryAction;
use App\Actions\DeleteMarketingMessageTemplateAction;
use App\Actions\SaveLeadDictionaryAction;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\MarketingMessageTemplate;
use App\Models\User;
use App\Rules\EditableLeadDictionaryRecordRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Mockery\MockInterface;
use Tests\TestCase;

class CrmActionsRequestsRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_save_dictionary_action_updates_status_and_keeps_single_default(): void
    {
        $previousDefault = LeadStatus::factory()->create([
            'code' => 'previous_default',
            'is_default' => true,
        ]);

        $status = app(SaveLeadDictionaryAction::class)->handle('statuses', null, [
            'code' => 'manager_review',
            'name' => 'Manager review',
            'name_translations' => [
                'ru' => 'Проверка менеджера',
                'en' => 'Manager review',
                'lt' => 'Vadybininko perziura',
                'pl' => 'Przeglad menedzera',
            ],
            'description_translations' => [
                'ru' => 'Лид проверяется менеджером.',
                'en' => 'Lead is being reviewed by a manager.',
            ],
            'color' => '#2563eb',
            'is_active' => true,
            'is_public' => true,
            'is_default' => true,
            'is_final' => false,
            'is_success' => false,
            'is_lost' => false,
            'is_duplicate' => false,
            'is_spam' => false,
            'sort_order' => 15,
        ]);

        $this->assertTrue($status->is_default);
        $this->assertFalse($previousDefault->refresh()->is_default);
        $this->assertSame('Manager review', $status->getTranslation('name', 'en'));
    }

    public function test_delete_dictionary_action_and_rule_reject_system_records_with_translated_message(): void
    {
        $this->seed();

        $systemSource = LeadSource::factory()->create([
            'code' => 'locked_source',
            'is_system' => true,
        ]);

        $validator = Validator::make(
            ['record' => $systemSource->id],
            ['record' => [new EditableLeadDictionaryRecordRule('sources')]],
        );

        $this->assertTrue($validator->fails());
        $this->assertSame(tkey('crm.validation.dictionary_system_record_locked'), $validator->errors()->first('record'));
        $this->assertNotSame('crm.validation.dictionary_system_record_locked', tkey('crm.validation.dictionary_system_record_locked', [], 'en'));

        $this->expectException(ValidationException::class);

        app(DeleteLeadDictionaryAction::class)->handle('sources', $systemSource->id);
    }

    public function test_dictionary_screen_saves_through_action(): void
    {
        $this->seed();

        $admin = $this->admin();

        $this->mock(SaveLeadDictionaryAction::class, function (MockInterface $mock): void {
            $mock->shouldReceive('handle')
                ->once()
                ->withArgs(fn (string $dictionary, mixed $record, array $payload): bool => $dictionary === 'sources'
                    && $record === null
                    && ($payload['code'] ?? null) === 'website_partner'
                    && ($payload['name_translations']['en'] ?? null) === 'Website partner')
                ->andReturn(LeadSource::factory()->make(['code' => 'website_partner']));
        });

        $this->actingAs($admin)
            ->post(route('platform.crm.dictionaries.create', ['dictionary' => 'sources', 'method' => 'save']), [
                'item' => [
                    'code' => 'website_partner',
                    'name' => 'Website partner',
                    'color' => '#2563eb',
                    'is_active' => '1',
                    'sort_order' => 80,
                ],
                'name_translations' => [
                    'ru' => 'Партнер сайта',
                    'en' => 'Website partner',
                    'lt' => 'Svetaines partneris',
                    'pl' => 'Partner strony',
                ],
            ])
            ->assertRedirect(route('platform.crm.dictionaries', 'sources'))
            ->assertSessionHasNoErrors();
    }

    public function test_message_template_delete_screen_uses_action(): void
    {
        $this->seed();

        $template = MarketingMessageTemplate::factory()->create([
            'name' => 'Delete through Action',
        ]);

        $this->mock(DeleteMarketingMessageTemplateAction::class, function (MockInterface $mock) use ($template): void {
            $mock->shouldReceive('handle')
                ->once()
                ->withArgs(fn (MarketingMessageTemplate $model): bool => $model->is($template));
        });

        $this->actingAs($this->admin())
            ->post(route('platform.marketing.templates.edit', [
                'messageTemplate' => $template,
                'method' => 'delete',
            ]))
            ->assertRedirect(route('platform.marketing.templates'))
            ->assertSessionHasNoErrors();
    }

    public function test_message_template_request_errors_are_translated(): void
    {
        $this->seed();

        $this->actingAs($this->admin())
            ->from(route('platform.marketing.templates.create'))
            ->post(route('platform.marketing.templates.create', ['method' => 'save']), [
                'template' => [
                    'name' => '',
                    'channel' => 'invalid-channel',
                    'body' => '',
                    'sort_order' => 'wrong',
                ],
            ])
            ->assertRedirect(route('platform.marketing.templates.create'))
            ->assertSessionHasErrors([
                'template.name' => tkey('crm.validation.message_template_name_required', [], 'ru'),
                'template.channel' => tkey('crm.validation.message_template_channel_invalid', [], 'ru'),
                'template.body' => tkey('crm.validation.message_template_body_required', [], 'ru'),
                'template.sort_order' => tkey('crm.validation.message_template_sort_order_invalid', [], 'ru'),
            ]);
    }

    private function admin(): User
    {
        return User::query()
            ->where('email', 'admin@example.com')
            ->firstOrFail();
    }
}

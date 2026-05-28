<?php

namespace Tests\Feature;

use App\Actions\CreateOrUpdateLeadLostReasonAction;
use App\Actions\CreateOrUpdateLeadSourceAction;
use App\Actions\DeleteLeadSourceAction;
use App\Actions\DeleteLeadStatusAction;
use App\Models\LeadLostReason;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\LeadTag;
use App\Models\MarketingLead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CrmDictionaryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_list_statuses(): void
    {
        $this->seed();

        $this->actingAs($this->seededAdmin())
            ->get(route('platform.crm.statuses'))
            ->assertOk()
            ->assertSee(tkey('crm.dictionaries.statuses.title'))
            ->assertSee(LeadStatus::translatedLabel('new'));
    }

    public function test_user_without_permission_cannot_list_statuses(): void
    {
        $this->seed();

        $this->actingAs($this->userWithPermissions())
            ->get(route('platform.crm.statuses'))
            ->assertForbidden();
    }

    public function test_status_can_be_created(): void
    {
        $this->seed();

        $this->actingAs($this->seededAdmin())
            ->post(route('platform.crm.dictionaries.create', ['dictionary' => 'statuses', 'method' => 'save']), [
                'item' => $this->statusPayload('manager_review'),
                'name_translations' => $this->translations('Manager review'),
                'description_translations' => $this->translations('Lead is reviewed by a manager.'),
            ])
            ->assertRedirect(route('platform.crm.dictionaries', 'statuses'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('lead_statuses', [
            'code' => 'manager_review',
            'is_active' => true,
        ]);
    }

    public function test_status_can_be_updated(): void
    {
        $this->seed();

        $status = LeadStatus::factory()->create([
            'code' => 'quality_check',
            'is_system' => false,
            'is_active' => true,
            'is_final' => false,
        ]);

        $this->actingAs($this->seededAdmin())
            ->post(route('platform.crm.dictionaries.edit', [
                'dictionary' => 'statuses',
                'record' => $status->id,
                'method' => 'save',
            ]), [
                'item' => $this->statusPayload('quality_check', [
                    'color' => '#0f766e',
                    'sort_order' => 44,
                ]),
                'name_translations' => $this->translations('Quality check'),
                'description_translations' => $this->translations('Manager checks lead quality.'),
            ])
            ->assertRedirect(route('platform.crm.dictionaries', 'statuses'))
            ->assertSessionHasNoErrors();

        $status->refresh();

        $this->assertSame('#0f766e', $status->color);
        $this->assertSame(44, $status->sort_order);
        $this->assertSame('Quality check', $status->getTranslation('name', 'en'));
    }

    public function test_only_one_default_status_exists(): void
    {
        $this->seed();

        $this->actingAs($this->seededAdmin())
            ->post(route('platform.crm.dictionaries.create', ['dictionary' => 'statuses', 'method' => 'save']), [
                'item' => $this->statusPayload('fresh_default', [
                    'is_default' => '1',
                ]),
                'name_translations' => $this->translations('Fresh default'),
            ])
            ->assertRedirect(route('platform.crm.dictionaries', 'statuses'))
            ->assertSessionHasNoErrors();

        $this->assertSame(1, LeadStatus::query()->where('is_default', true)->count());
        $this->assertTrue(LeadStatus::query()->where('code', 'fresh_default')->firstOrFail()->is_default);
    }

    public function test_used_status_cannot_be_deleted(): void
    {
        $status = LeadStatus::factory()->create([
            'code' => 'consultation_done',
            'is_system' => false,
        ]);
        MarketingLead::factory()->create(['status' => 'consultation_done']);

        try {
            app(DeleteLeadStatusAction::class)->handle($status);
            $this->fail('Used status was deleted.');
        } catch (ValidationException $exception) {
            $this->assertSame(tkey('crm.dictionaries.messages.cannot_delete_used_item'), $exception->errors()['record'][0]);
        }

        $this->assertDatabaseHas('lead_statuses', ['id' => $status->id]);
    }

    public function test_source_can_be_deactivated(): void
    {
        $source = LeadSource::factory()->create([
            'code' => 'walk_in_partner',
            'is_active' => true,
        ]);

        $updated = app(CreateOrUpdateLeadSourceAction::class)->handle($source, [
            'code' => 'walk_in_partner',
            'name' => 'Walk-in partner',
            'name_translations' => $this->translations('Walk-in partner'),
            'description_translations' => $this->translations('Partner source.'),
            'color' => '#64748b',
            'is_active' => false,
            'sort_order' => 50,
        ]);

        $this->assertFalse($updated->is_active);
    }

    public function test_used_source_cannot_be_deleted(): void
    {
        $source = LeadSource::factory()->create([
            'code' => 'used_source',
            'is_system' => false,
        ]);
        MarketingLead::factory()->create(['source' => 'used_source']);

        try {
            app(DeleteLeadSourceAction::class)->handle($source);
            $this->fail('Used source was deleted.');
        } catch (ValidationException $exception) {
            $this->assertSame(tkey('crm.dictionaries.messages.cannot_delete_used_item'), $exception->errors()['record'][0]);
        }

        $this->assertDatabaseHas('lead_sources', ['id' => $source->id]);
    }

    public function test_lost_reason_can_be_deactivated(): void
    {
        $reason = LeadLostReason::factory()->create([
            'code' => 'too_far',
            'is_active' => true,
        ]);

        $updated = app(CreateOrUpdateLeadLostReasonAction::class)->handle($reason, [
            'code' => 'too_far',
            'name' => 'Too far',
            'name_translations' => $this->translations('Too far'),
            'description_translations' => $this->translations('Location is not convenient.'),
            'color' => '#64748b',
            'is_active' => false,
            'sort_order' => 70,
        ]);

        $this->assertFalse($updated->is_active);
    }

    public function test_tag_can_be_created_with_translations(): void
    {
        $this->seed();

        $this->actingAs($this->seededAdmin())
            ->post(route('platform.crm.dictionaries.create', ['dictionary' => 'tags', 'method' => 'save']), [
                'item' => [
                    'slug' => 'fleet_client',
                    'name' => 'Fleet client',
                    'color' => '#334155',
                    'is_active' => '1',
                    'sort_order' => 90,
                ],
                'name_translations' => [
                    'ru' => 'Клиент автопарка',
                    'en' => 'Fleet client',
                    'lt' => 'Parko klientas',
                    'pl' => 'Klient flotowy',
                ],
                'description_translations' => [
                    'ru' => 'Заявка от клиента автопарка',
                    'en' => 'Lead from a fleet client',
                    'lt' => 'Uzklausa is parko kliento',
                    'pl' => 'Lead od klienta flotowego',
                ],
            ])
            ->assertRedirect(route('platform.crm.dictionaries', 'tags'))
            ->assertSessionHasNoErrors();

        $tag = LeadTag::query()->where('slug', 'fleet_client')->firstOrFail();

        $this->assertSame('Fleet client', $tag->getTranslation('name', 'en'));
        $this->assertSame('Клиент автопарка', $tag->getTranslation('name', 'ru'));
    }

    public function test_dictionary_display_names_use_current_locale(): void
    {
        $tag = LeadTag::factory()->create([
            'slug' => 'localized_tag',
            'name_translations' => [
                'ru' => 'Локальная метка',
                'en' => 'Localized tag',
                'lt' => 'Lokalizuota zyma',
                'pl' => 'Lokalny tag',
            ],
        ]);

        app()->setLocale('pl');

        $this->assertSame('Lokalny tag', $tag->display_name);
    }

    public function test_system_dictionary_code_is_protected(): void
    {
        $this->seed();

        $source = LeadSource::query()->where('code', 'website')->firstOrFail();

        $this->actingAs($this->seededAdmin())
            ->from(route('platform.crm.dictionaries.edit', ['dictionary' => 'sources', 'record' => $source->id]))
            ->post(route('platform.crm.dictionaries.edit', [
                'dictionary' => 'sources',
                'record' => $source->id,
                'method' => 'save',
            ]), [
                'item' => [
                    'code' => 'renamed_website',
                    'name' => 'Website',
                    'is_active' => '1',
                    'sort_order' => 1,
                ],
                'name_translations' => $this->translations('Website'),
            ])
            ->assertRedirect(route('platform.crm.dictionaries.edit', ['dictionary' => 'sources', 'record' => $source->id]))
            ->assertSessionHasErrors([
                'item.code' => tkey('crm.validation.dictionary_system_code_locked'),
            ]);
    }

    public function test_default_status_cannot_be_inactive(): void
    {
        $this->seed();

        $this->actingAs($this->seededAdmin())
            ->from(route('platform.crm.dictionaries.create', 'statuses'))
            ->post(route('platform.crm.dictionaries.create', ['dictionary' => 'statuses', 'method' => 'save']), [
                'item' => $this->statusPayload('inactive_default', [
                    'is_active' => '0',
                    'is_default' => '1',
                ]),
                'name_translations' => $this->translations('Inactive default'),
            ])
            ->assertRedirect(route('platform.crm.dictionaries.create', 'statuses'))
            ->assertSessionHasErrors([
                'item.is_default' => tkey('crm.validation.dictionary_default_status_inactive'),
            ]);
    }

    public function test_system_final_status_is_protected_from_accidental_deactivation(): void
    {
        $this->seed();

        $status = LeadStatus::query()->where('code', 'archived')->firstOrFail();

        $this->actingAs($this->seededAdmin())
            ->from(route('platform.crm.dictionaries.edit', ['dictionary' => 'statuses', 'record' => $status->id]))
            ->post(route('platform.crm.dictionaries.edit', [
                'dictionary' => 'statuses',
                'record' => $status->id,
                'method' => 'save',
            ]), [
                'item' => $this->statusPayload('archived', [
                    'is_system' => '1',
                    'is_active' => '0',
                    'is_final' => '1',
                    'sort_order' => $status->sort_order,
                ]),
                'name_translations' => $status->name_translations,
                'description_translations' => $status->description_translations,
            ])
            ->assertRedirect(route('platform.crm.dictionaries.edit', ['dictionary' => 'statuses', 'record' => $status->id]))
            ->assertSessionHasErrors([
                'item.is_active' => tkey('crm.validation.dictionary_final_status_locked'),
            ]);

        $this->assertTrue($status->refresh()->is_active);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function statusPayload(string $code, array $overrides = []): array
    {
        return [
            'code' => $code,
            'name' => str($code)->replace('_', ' ')->title()->toString(),
            'color' => '#2563eb',
            'is_active' => '1',
            'is_public' => '0',
            'is_default' => '0',
            'is_final' => '0',
            'is_success' => '0',
            'is_lost' => '0',
            'is_duplicate' => '0',
            'is_spam' => '0',
            'sort_order' => 50,
            ...$overrides,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function translations(string $value): array
    {
        return [
            'ru' => $value,
            'en' => $value,
            'lt' => $value,
            'pl' => $value,
        ];
    }

    private function seededAdmin(): User
    {
        return User::query()
            ->where('email', 'admin@example.com')
            ->firstOrFail();
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

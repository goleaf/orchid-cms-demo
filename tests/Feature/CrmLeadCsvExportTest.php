<?php

namespace Tests\Feature;

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\User;
use Database\Seeders\CrmDictionarySeeder;
use Database\Seeders\CrmTranslationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CrmLeadCsvExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('en');
        $this->seed(CrmTranslationSeeder::class);
        $this->seed(CrmDictionarySeeder::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_export_requires_permission(): void
    {
        $this->actingAs($this->userWithPermissions(['crm.leads.view']))
            ->post(route('platform.crm.leads', ['method' => 'export']))
            ->assertForbidden();
    }

    public function test_export_works_with_permission_and_includes_lead(): void
    {
        Carbon::setTestNow('2026-05-28 10:00:00');

        Lead::factory()->create([
            'lead_number' => 'LEAD-2026-CSV',
            'first_name' => 'Export',
            'last_name' => 'Driver',
            'email' => 'export.driver@example.test',
        ]);

        $response = $this->actingAs($this->userWithPermissions(['crm.leads.view', 'crm.leads.export']))
            ->post(route('platform.crm.leads', ['method' => 'export']))
            ->assertOk();

        $content = $response->streamedContent();

        $this->assertStringContainsString('crm-leads-2026-05-28.csv', (string) $response->headers->get('content-disposition'));
        $this->assertStringContainsString(tkey('crm.leads.fields.lead_number'), $content);
        $this->assertStringContainsString('LEAD-2026-CSV', $content);
        $this->assertStringContainsString('Export Driver', $content);
        $this->assertStringContainsString('export.driver@example.test', $content);
    }

    public function test_export_respects_status_filter(): void
    {
        Lead::factory()->newLead()->create([
            'lead_number' => 'LEAD-CSV-NEW',
            'email' => 'new-filter@example.test',
        ]);
        Lead::factory()->lost()->create([
            'lead_number' => 'LEAD-CSV-LOST',
            'email' => 'lost-filter@example.test',
        ]);

        $response = $this->actingAs($this->userWithPermissions(['crm.leads.view', 'crm.leads.export']))
            ->post(route('platform.crm.leads', ['method' => 'export']), [
                'status' => LeadStatus::Lost->value,
            ])
            ->assertOk();

        $content = $response->streamedContent();

        $this->assertStringContainsString('LEAD-CSV-LOST', $content);
        $this->assertStringNotContainsString('LEAD-CSV-NEW', $content);
    }

    public function test_export_excludes_marketing_fields_without_marketing_permission(): void
    {
        Lead::factory()->withUtm()->create([
            'lead_number' => 'LEAD-CSV-NO-MARKETING',
            'utm_source' => 'secret-google',
            'utm_campaign' => 'secret-campaign',
            'ip_address' => '127.0.0.5',
            'user_agent' => 'Secret Browser',
        ]);

        $response = $this->actingAs($this->userWithPermissions(['crm.leads.view', 'crm.leads.export']))
            ->post(route('platform.crm.leads', ['method' => 'export']))
            ->assertOk();

        $content = $response->streamedContent();

        $this->assertStringContainsString('LEAD-CSV-NO-MARKETING', $content);
        $this->assertStringNotContainsString(tkey('crm.leads.fields.utm_source'), $content);
        $this->assertStringNotContainsString('secret-google', $content);
        $this->assertStringNotContainsString('secret-campaign', $content);
        $this->assertStringNotContainsString('127.0.0.5', $content);
        $this->assertStringNotContainsString('Secret Browser', $content);
    }

    public function test_export_includes_marketing_fields_with_marketing_permission(): void
    {
        Lead::factory()->withUtm()->create([
            'lead_number' => 'LEAD-CSV-MARKETING',
            'utm_source' => 'google-export',
            'utm_campaign' => 'spring-export',
            'ip_address' => '127.0.0.6',
            'user_agent' => 'Marketing Browser',
        ]);

        $response = $this->actingAs($this->userWithPermissions([
            'crm.leads.view',
            'crm.leads.export',
            'crm.leads.view_marketing',
        ]))
            ->post(route('platform.crm.leads', ['method' => 'export']))
            ->assertOk();

        $content = $response->streamedContent();

        $this->assertStringContainsString(tkey('crm.leads.fields.utm_source'), $content);
        $this->assertStringContainsString('google-export', $content);
        $this->assertStringContainsString('spring-export', $content);
        $this->assertStringContainsString('127.0.0.6', $content);
        $this->assertStringContainsString('Marketing Browser', $content);
    }

    public function test_export_escapes_csv_values_safely(): void
    {
        Lead::factory()->create([
            'lead_number' => 'LEAD-CSV-ESCAPE',
            'first_name' => 'Comma, Quote',
            'last_name' => 'Driver "One"',
            'message' => 'Line one, "quoted"',
        ]);

        $response = $this->actingAs($this->userWithPermissions(['crm.leads.view', 'crm.leads.export']))
            ->post(route('platform.crm.leads', ['method' => 'export']))
            ->assertOk();

        $content = $response->streamedContent();

        $this->assertStringContainsString('"Comma, Quote Driver ""One"""', $content);
        $this->assertStringContainsString('"Line one, ""quoted"""', $content);
    }

    public function test_soft_deleted_leads_are_excluded(): void
    {
        Lead::factory()->create(['lead_number' => 'LEAD-CSV-ACTIVE']);
        $deleted = Lead::factory()->create(['lead_number' => 'LEAD-CSV-DELETED']);
        $deleted->delete();

        $response = $this->actingAs($this->userWithPermissions(['crm.leads.view', 'crm.leads.export']))
            ->post(route('platform.crm.leads', ['method' => 'export']))
            ->assertOk();

        $content = $response->streamedContent();

        $this->assertStringContainsString('LEAD-CSV-ACTIVE', $content);
        $this->assertStringNotContainsString('LEAD-CSV-DELETED', $content);
    }

    public function test_basic_reporting_helpers_return_counts(): void
    {
        Carbon::setTestNow('2026-05-28 10:00:00');

        $manager = User::factory()->create();
        Lead::factory()->newLead()->assigned($manager)->create([
            'source' => 'website',
            'created_at' => '2026-05-27 09:00:00',
        ]);
        Lead::factory()->readyToEnroll()->create([
            'source' => 'callback',
            'created_at' => '2026-05-28 09:00:00',
        ]);
        Lead::factory()->lost()->create([
            'source' => 'website',
            'lost_reason_code' => 'price',
            'created_at' => '2026-05-28 09:30:00',
        ]);
        Lead::factory()->overdue()->create([
            'source' => 'website',
            'created_at' => '2026-05-28 09:45:00',
        ]);

        $this->assertSame(2, Lead::reportCountByStatus()[LeadStatus::New->value]);
        $this->assertSame(3, Lead::reportCountBySource()['website']);
        $this->assertSame(1, Lead::reportCountByManager()[(string) $manager->id]);
        $this->assertSame(1, Lead::reportCountByLostReason()['price']);
        $this->assertSame(1, Lead::reportCountByDay()['2026-05-27']);
        $this->assertSame(3, Lead::reportCountByDay()['2026-05-28']);
        $this->assertSame(1, Lead::reportConversionReadyCount());
        $this->assertSame(1, Lead::reportOverdueFollowUpCount());
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

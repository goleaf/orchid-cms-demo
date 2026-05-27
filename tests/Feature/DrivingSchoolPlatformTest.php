<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DrivingSchoolPlatformTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_public_site_renders_auto_school_content(): void
    {
        $this->seed();

        $this->get('/')
            ->assertOk()
            ->assertSee('Driving lessons, exams, and school operations')
            ->assertSee('Student CRM and cabinet base');
    }

    public function test_admin_can_open_core_auto_school_sections(): void
    {
        $this->seed();

        $admin = User::query()
            ->where('email', 'admin@example.com')
            ->firstOrFail();

        $this->actingAs($admin)
            ->get(route('platform.main'))
            ->assertOk()
            ->assertSee('Auto-school operations');

        collect([
            'platform.operations.branches' => 'Branches',
            'platform.operations.instructors' => 'Instructors',
            'platform.operations.groups' => 'Training groups',
            'platform.crm.students' => 'Student CRM',
            'platform.lms.programs' => 'LMS Programs',
            'platform.schedule.lessons' => 'Schedule',
            'platform.fleet.vehicles' => 'Fleet',
            'platform.exams' => 'Exams',
            'platform.finance.payments' => 'Payments',
            'platform.documents' => 'Documents',
            'platform.marketing.campaigns' => 'Marketing campaigns',
            'platform.marketing.leads' => 'Marketing leads',
        ])->each(function (string $label, string $routeName) use ($admin): void {
            $this->actingAs($admin)
                ->get(route($routeName))
                ->assertOk()
                ->assertSee($label);
        });
    }

    public function test_seeded_operations_include_groups_and_marketing_pipeline(): void
    {
        $this->seed();

        $admin = User::query()
            ->where('email', 'admin@example.com')
            ->firstOrFail();

        $this->actingAs($admin)
            ->get(route('platform.operations.groups'))
            ->assertOk()
            ->assertSee('B-VNO-001')
            ->assertSee('Evening Category B Group');

        $this->actingAs($admin)
            ->get(route('platform.marketing.campaigns'))
            ->assertOk()
            ->assertSee('Spring Category B Intake')
            ->assertSee('Google Ads');

        $this->actingAs($admin)
            ->get(route('platform.marketing.leads'))
            ->assertOk()
            ->assertSee('Tomas Jankauskas')
            ->assertSee('Qualified');
    }
}

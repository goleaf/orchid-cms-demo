<?php

namespace Tests\Feature;

use App\Actions\Security\ChangeUserStatusAction;
use App\Actions\Security\CreateOrUpdateUserStatusAction;
use App\Actions\Security\CreateStaffProfileAction;
use App\Actions\Security\GenerateStaffNumberAction;
use App\Actions\Security\SetDefaultUserStatusAction;
use App\Actions\Security\UpdateStaffProfileAction;
use App\Models\StaffProfile;
use App\Models\User;
use App\Models\UserStatus;
use App\Rules\ActiveUserStatusRule;
use App\Rules\OnlyOneDefaultUserStatusRule;
use App\Rules\StaffNumberFormatRule;
use App\Rules\StaffProfileUserUniqueRule;
use App\Rules\UserStatusCanBeChangedRule;
use App\Rules\ValidUserLocaleRule;
use App\Rules\ValidUserTimezoneRule;
use App\Support\Access\SuperadminPermissions;
use Database\Factories\StaffProfileFactory;
use Database\Factories\UserStatusFactory;
use Database\Factories\RoleFactory;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\SecurityTranslationSeeder;
use Database\Seeders\StaffProfileDemoSeeder;
use Database\Seeders\UserStatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SecurityUserStatusesStaffProfilesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('en');
        $this->seed(LanguageSeeder::class);
        $this->seed(SecurityTranslationSeeder::class);
        $this->seed(UserStatusSeeder::class);
    }

    public function test_required_step_two_artifacts_exist(): void
    {
        foreach ([
            UserStatus::class,
            StaffProfile::class,
            UserStatusFactory::class,
            StaffProfileFactory::class,
            UserStatusSeeder::class,
            StaffProfileDemoSeeder::class,
            GenerateStaffNumberAction::class,
            CreateStaffProfileAction::class,
            UpdateStaffProfileAction::class,
            ChangeUserStatusAction::class,
            SetDefaultUserStatusAction::class,
            CreateOrUpdateUserStatusAction::class,
            ActiveUserStatusRule::class,
            OnlyOneDefaultUserStatusRule::class,
            UserStatusCanBeChangedRule::class,
            StaffProfileUserUniqueRule::class,
            StaffNumberFormatRule::class,
            ValidUserLocaleRule::class,
            ValidUserTimezoneRule::class,
            \App\Http\Requests\Security\UserStatusRequest::class,
            \App\Http\Requests\Security\StaffProfileRequest::class,
        ] as $class) {
            $this->assertTrue(class_exists($class), $class);
        }
    }

    public function test_user_status_factory_creates_valid_status(): void
    {
        $status = UserStatus::factory()->translated()->create([
            'code' => 'training_manager',
        ]);

        $this->assertDatabaseHas('user_statuses', [
            'code' => 'training_manager',
            'is_active' => true,
        ]);
        $this->assertSame('Translated status', $status->display_name);
    }

    public function test_staff_profile_factory_creates_valid_profile(): void
    {
        $profile = StaffProfile::factory()
            ->translated()
            ->withBranch()
            ->visibleOnSite()
            ->create();

        $this->assertNotNull($profile->uuid);
        $this->assertMatchesRegularExpression('/^STAFF-\d{4}-\d{4,}$/', (string) $profile->staff_number);
        $this->assertTrue($profile->user()->exists());
        $this->assertTrue($profile->branch()->exists());
        $this->assertTrue($profile->is_visible_on_site);
    }

    public function test_user_can_have_status_and_staff_profile(): void
    {
        $status = UserStatus::query()->where('code', 'active')->firstOrFail();
        $user = User::factory()->create(['status_id' => $status->id]);
        $profile = StaffProfile::factory()->translated()->create(['user_id' => $user->id]);

        $user->load(['status', 'staffProfile']);

        $this->assertTrue($user->status->is($status));
        $this->assertTrue($user->staffProfile->is($profile));
    }

    public function test_only_one_default_user_status_exists(): void
    {
        $freshDefault = UserStatus::factory()
            ->translated()
            ->default()
            ->create(['code' => 'fresh_default']);

        $this->assertSame(1, UserStatus::query()->where('is_default', true)->count());
        $this->assertTrue($freshDefault->refresh()->is_default);

        $active = UserStatus::query()->where('code', 'active')->firstOrFail();
        app(SetDefaultUserStatusAction::class)->handle($active);

        $this->assertSame(1, UserStatus::query()->where('is_default', true)->count());
        $this->assertTrue($active->refresh()->is_default);
    }

    public function test_default_active_status_is_seeded(): void
    {
        $active = UserStatus::query()->where('code', 'active')->firstOrFail();

        $this->assertTrue($active->is_default);
        $this->assertTrue($active->is_active);
        $this->assertSame(4, UserStatus::query()->count());
    }

    public function test_blocked_and_archived_status_helpers_work(): void
    {
        $blocked = UserStatus::query()->where('code', 'blocked')->firstOrFail();
        $archived = UserStatus::query()->where('code', 'archived')->firstOrFail();

        $blockedUser = User::factory()->create(['status_id' => $blocked->id])->load('status');
        $archivedUser = User::factory()->create(['status_id' => $archived->id])->load('status');

        $this->assertTrue($blockedUser->isBlocked());
        $this->assertFalse($blockedUser->isArchived());
        $this->assertTrue($archivedUser->isArchived());
        $this->assertTrue($archivedUser->isLockedOut());
    }

    public function test_staff_profile_display_name_uses_translations(): void
    {
        $profile = StaffProfile::factory()->translated()->create();

        app()->setLocale('pl');

        $this->assertSame('Instruktorka Anna', $profile->display_name);
        $this->assertSame('Starszy instruktor', $profile->display_job_title);
    }

    public function test_seeders_are_idempotent(): void
    {
        $this->seed(UserStatusSeeder::class);
        $this->seed(UserStatusSeeder::class);

        $this->assertSame(4, UserStatus::query()->count());
        $this->assertSame(1, UserStatus::query()->where('is_default', true)->count());

        $this->seed(StaffProfileDemoSeeder::class);
        $this->seed(StaffProfileDemoSeeder::class);

        $user = User::query()->where('email', 'staff.manager@drivepro.test')->firstOrFail();

        $this->assertSame(1, StaffProfile::query()->where('user_id', $user->id)->count());
    }

    public function test_validation_messages_are_translated(): void
    {
        $inactive = UserStatus::factory()->inactive()->create([
            'code' => 'disabled_choice',
            'is_active' => false,
        ]);

        $statusValidator = Validator::make(
            ['status_id' => $inactive->id],
            ['status_id' => [new ActiveUserStatusRule]]
        );

        $this->assertTrue($statusValidator->fails());
        $this->assertSame(tkey('security.validation.user_status_not_active'), $statusValidator->errors()->first('status_id'));

        $staffNumberValidator = Validator::make(
            ['staff_number' => 'BAD-1'],
            ['staff_number' => [new StaffNumberFormatRule]]
        );

        $this->assertTrue($staffNumberValidator->fails());
        $this->assertSame(tkey('security.validation.staff_number_format'), $staffNumberValidator->errors()->first('staff_number'));
    }

    public function test_last_superadmin_cannot_be_blocked_by_status_change(): void
    {
        $role = RoleFactory::new()->create([
            'slug' => 'superadmin',
            'name' => 'Superadmin',
            'permissions' => SuperadminPermissions::enabled(),
        ]);
        $active = UserStatus::query()->where('code', 'active')->firstOrFail();
        $blocked = UserStatus::query()->where('code', 'blocked')->firstOrFail();
        $superadmin = User::factory()->create([
            'status_id' => $active->id,
            'is_active' => true,
            'security_locked_at' => null,
        ]);
        $superadmin->roles()->attach($role);

        try {
            app(ChangeUserStatusAction::class)->handle($superadmin, $blocked, $superadmin);
            $this->fail('The last Superadmin should not be blocked.');
        } catch (ValidationException $exception) {
            $this->assertSame(tkey('security.validation.last_superadmin'), $exception->errors()['status_id'][0]);
        }
    }
}

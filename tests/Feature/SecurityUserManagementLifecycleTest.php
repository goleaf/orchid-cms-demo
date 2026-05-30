<?php

namespace Tests\Feature;

use App\Actions\Security\ArchiveUserAction;
use App\Actions\Security\BlockUserAction;
use App\Actions\Security\BuildUserSecuritySummaryAction;
use App\Actions\Security\ChangeUserStatusAction;
use App\Actions\Security\CheckUserCanLoginAction;
use App\Actions\Security\ClearForcePasswordChangeAction;
use App\Actions\Security\CreateUserAction;
use App\Actions\Security\EnsureUserHasStaffProfileAction;
use App\Actions\Security\ForceUserPasswordChangeAction;
use App\Actions\Security\MarkUserSeenAction;
use App\Actions\Security\ResolveUserDisplayNameAction;
use App\Actions\Security\ResolveUserStatusAction;
use App\Actions\Security\RevokeUserAccessOnBlockAction;
use App\Actions\Security\SyncUserBranchAccessAction;
use App\Actions\Security\SyncUserRolesAction;
use App\Actions\Security\UnblockUserAction;
use App\Actions\Security\UpdateStaffProfileAction;
use App\Actions\Security\UpdateUserAction;
use App\Actions\Security\UpdateUserLocaleAction;
use App\Actions\Security\UpdateUserProfileAction;
use App\Actions\Security\UpdateUserTimezoneAction;
use App\Http\Requests\Security\ArchiveUserRequest;
use App\Http\Requests\Security\BlockUserRequest;
use App\Http\Requests\Security\ChangeUserStatusRequest;
use App\Http\Requests\Security\ClearForcePasswordChangeRequest;
use App\Http\Requests\Security\EnsureUserHasStaffProfileRequest;
use App\Http\Requests\Security\ForceUserPasswordChangeRequest;
use App\Http\Requests\Security\StoreUserRequest;
use App\Http\Requests\Security\UnblockUserRequest;
use App\Http\Requests\Security\UpdateStaffProfileRequest;
use App\Http\Requests\Security\UpdateUserLocaleRequest;
use App\Http\Requests\Security\UpdateUserProfileRequest;
use App\Http\Requests\Security\UpdateUserRequest;
use App\Http\Requests\Security\UpdateUserTimezoneRequest;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\SecurityEvent;
use App\Models\StaffProfile;
use App\Models\User;
use App\Models\UserSecuritySession;
use App\Models\UserStatus;
use App\Rules\CurrentUserLockoutRule;
use App\Rules\LastSuperadminUserProtectedRule;
use App\Rules\StaffPhoneRule;
use App\Rules\StaffWorkEmailRule;
use App\Rules\UniqueUserEmailRule;
use App\Rules\UserCanBeArchivedRule;
use App\Rules\UserCanBeBlockedRule;
use App\Rules\UserCanBeUnblockedRule;
use App\Rules\UserCanBeUpdatedRule;
use App\Rules\UserCanForcePasswordChangeRule;
use App\Rules\UserCanLoginRule;
use App\Rules\ValidUserStatusTransitionRule;
use App\Support\Access\SuperadminPermissions;
use Database\Factories\UserFactory;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\SecurityTranslationSeeder;
use Database\Seeders\UserStatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Orchid\Platform\Models\Role;
use Tests\TestCase;

class SecurityUserManagementLifecycleTest extends TestCase
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

    public function test_required_step_nine_artifacts_exist(): void
    {
        foreach ([
            CreateUserAction::class,
            UpdateUserAction::class,
            ArchiveUserAction::class,
            BlockUserAction::class,
            UnblockUserAction::class,
            ChangeUserStatusAction::class,
            UpdateUserProfileAction::class,
            UpdateUserLocaleAction::class,
            UpdateUserTimezoneAction::class,
            ForceUserPasswordChangeAction::class,
            ClearForcePasswordChangeAction::class,
            MarkUserSeenAction::class,
            ResolveUserDisplayNameAction::class,
            ResolveUserStatusAction::class,
            CheckUserCanLoginAction::class,
            RevokeUserAccessOnBlockAction::class,
            EnsureUserHasStaffProfileAction::class,
            BuildUserSecuritySummaryAction::class,
            SyncUserRolesAction::class,
            SyncUserBranchAccessAction::class,
            StoreUserRequest::class,
            UpdateUserRequest::class,
            ArchiveUserRequest::class,
            BlockUserRequest::class,
            UnblockUserRequest::class,
            ChangeUserStatusRequest::class,
            UpdateUserProfileRequest::class,
            UpdateStaffProfileRequest::class,
            UpdateUserLocaleRequest::class,
            UpdateUserTimezoneRequest::class,
            ForceUserPasswordChangeRequest::class,
            ClearForcePasswordChangeRequest::class,
            EnsureUserHasStaffProfileRequest::class,
            UniqueUserEmailRule::class,
            UserCanBeUpdatedRule::class,
            UserCanBeBlockedRule::class,
            UserCanBeUnblockedRule::class,
            UserCanBeArchivedRule::class,
            ValidUserStatusTransitionRule::class,
            LastSuperadminUserProtectedRule::class,
            CurrentUserLockoutRule::class,
            StaffWorkEmailRule::class,
            StaffPhoneRule::class,
            UserCanForcePasswordChangeRule::class,
            UserCanLoginRule::class,
        ] as $class) {
            $this->assertTrue(class_exists($class), $class);
        }
    }

    public function test_user_factory_lifecycle_states_work(): void
    {
        $active = User::factory()->active()->create()->load('status');
        $blocked = User::factory()->blocked()->create()->load('status');

        $this->assertSame('active', $active->status->code);
        $this->assertSame('blocked', $blocked->status->code);
        $this->assertTrue($blocked->isBlocked());
        $this->assertInstanceOf(UserFactory::class, User::factory()->mustChangePassword());
    }

    public function test_create_user_action_creates_user_status_staff_profile_roles_and_branch_access(): void
    {
        $actor = User::factory()->superadmin()->create();
        $branch = Branch::factory()->create();
        $role = Role::query()->firstOrCreate(['slug' => 'manager'], ['name' => 'Manager', 'permissions' => []]);

        $user = app(CreateUserAction::class)->handle([
            'name' => 'Local Manager',
            'email' => 'local.manager@example.test',
            'password' => 'Password123!@#',
            'preferred_locale' => 'en',
            'timezone' => 'Europe/Vilnius',
            'must_change_password' => true,
            'roles' => [$role->id],
            'branch_ids' => [$branch->id],
            'staff_profile' => [
                'display_name_translations' => ['en' => 'Manager Display'],
                'work_email' => 'manager.work@example.test',
                'phone' => '+370 600 00000',
            ],
        ], $actor);

        $this->assertDatabaseHas('users', ['email' => 'local.manager@example.test']);
        $this->assertTrue(Hash::check('Password123!@#', $user->password));
        $this->assertNotSame('Password123!@#', $user->password);
        $this->assertSame('active', $user->status->code);
        $this->assertTrue($user->must_change_password);
        $this->assertTrue($user->roles()->where('slug', 'manager')->exists());
        $this->assertTrue($user->hasBranchAccess($branch));
        $this->assertTrue($user->staffProfile()->exists());
        $this->assertTrue(AuditLog::query()->where('action', 'user_created')->exists());
        $this->assertFalse(AuditLog::query()->get()->contains(fn (AuditLog $log): bool => str_contains(json_encode($log->new_values), 'Password123!@#')));
    }

    public function test_update_user_action_updates_safe_fields_and_protects_superadmin(): void
    {
        $actor = User::factory()->active()->create();
        $user = User::factory()->active()->create(['email' => 'before@example.test']);

        app(UpdateUserAction::class)->handle($user, [
            'name' => 'Updated User',
            'email' => 'after@example.test',
            'timezone' => 'UTC',
        ], $actor);

        $this->assertDatabaseHas('users', ['email' => 'after@example.test', 'name' => 'Updated User']);

        $lastSuperadmin = User::factory()->superadmin()->create();

        try {
            app(BlockUserAction::class)->handle($lastSuperadmin, $lastSuperadmin);
            $this->fail('Last Superadmin should be protected.');
        } catch (ValidationException $exception) {
            $this->assertSame(tkey('security.validation.user_cannot_be_blocked'), $exception->errors()['user'][0]);
        }
    }

    public function test_block_unblock_archive_status_and_session_revocation_work(): void
    {
        $actor = User::factory()->superadmin()->create();
        $user = User::factory()->active()->create();
        $session = UserSecuritySession::factory()->active()->withUser($user)->create();

        app(BlockUserAction::class)->handle($user, $actor);

        $this->assertTrue($user->refresh()->isBlocked());
        $this->assertNotNull($session->refresh()->revoked_at);
        $this->assertTrue(SecurityEvent::query()->where('event_type', 'user_blocked')->exists());

        app(UnblockUserAction::class)->handle($user, $actor);
        $this->assertSame('active', $user->refresh()->status->code);

        app(ArchiveUserAction::class)->handle($user, $actor);
        $this->assertTrue($user->refresh()->isArchived());
        $this->assertTrue(SecurityEvent::query()->where('event_type', 'user_archived')->exists());
    }

    public function test_current_user_cannot_block_self_without_override(): void
    {
        $user = User::factory()->superadmin()->create();
        User::factory()->superadmin()->create();

        $validator = Validator::make(
            ['user' => $user->id],
            ['user' => [new UserCanBeBlockedRule($user, $user)]],
        );

        $this->assertTrue($validator->fails());
        $this->assertSame(tkey('security.validation.current_user_lockout'), $validator->errors()->first('user'));
    }

    public function test_force_clear_locale_timezone_seen_display_login_and_summary_actions_work(): void
    {
        $actor = User::factory()->superadmin()->create();
        $user = User::factory()->active()->create(['preferred_locale' => 'en']);

        app(EnsureUserHasStaffProfileAction::class)->handle($user, [
            'display_name_translations' => ['en' => 'Translated Staff'],
        ], $actor);
        app(ForceUserPasswordChangeAction::class)->handle($user, $actor);
        $this->assertTrue($user->refresh()->must_change_password);
        $this->assertFalse(app(CheckUserCanLoginAction::class)->handle($user)['allowed']);

        app(ClearForcePasswordChangeAction::class)->handle($user, $actor, null, true);
        app(UpdateUserLocaleAction::class)->handle($user, 'lt', $actor);
        app(UpdateUserTimezoneAction::class)->handle($user, 'UTC', $actor);
        app(MarkUserSeenAction::class)->handle($user, 0);

        $this->assertFalse($user->refresh()->must_change_password);
        $this->assertSame('lt', $user->preferred_locale);
        $this->assertSame('UTC', $user->timezone);
        $this->assertNotNull($user->last_seen_at);
        $this->assertSame('Translated Staff', app(ResolveUserDisplayNameAction::class)->handle($user, 'en'));
        $this->assertSame('active', app(ResolveUserStatusAction::class)->handle($user)['code']);
        $this->assertTrue(app(CheckUserCanLoginAction::class)->handle($user)['allowed']);
        $this->assertArrayHasKey('branch_access', app(BuildUserSecuritySummaryAction::class)->handle($user));
    }

    public function test_status_transition_validation_and_login_reasons_work(): void
    {
        $actor = User::factory()->active()->create();
        $user = User::factory()->archived()->create();
        $active = UserStatus::query()->where('code', 'active')->firstOrFail();

        try {
            app(ChangeUserStatusAction::class)->handle($user, $active, $actor);
            $this->fail('Archived status should be final without override permission.');
        } catch (ValidationException $exception) {
            $this->assertSame(tkey('security.validation.invalid_user_status_transition'), $exception->errors()['status_id'][0]);
        }

        $this->assertSame('user_archived', app(CheckUserCanLoginAction::class)->handle($user)['reason']);
    }

    public function test_validation_messages_permissions_and_translation_keys_resolve(): void
    {
        User::factory()->create(['email' => 'duplicate@example.test']);

        $emailValidator = Validator::make(
            ['email' => 'duplicate@example.test'],
            ['email' => [new UniqueUserEmailRule]],
        );
        $phoneValidator = Validator::make(
            ['phone' => 'bad'],
            ['phone' => [new StaffPhoneRule]],
        );

        $this->assertTrue($emailValidator->fails());
        $this->assertSame(tkey('security.validation.user_email_not_unique'), $emailValidator->errors()->first('email'));
        $this->assertTrue($phoneValidator->fails());
        $this->assertSame(tkey('security.validation.invalid_staff_phone'), $phoneValidator->errors()->first('phone'));

        foreach ([
            'security.users.title',
            'security.users.messages.sessions_revoked',
            'security.users.login_reasons.must_change_password',
            'permissions.security.users.view_security_summary',
        ] as $key) {
            $this->assertNotSame($key, tkey($key));
        }

        foreach ([
            'security.users.view',
            'security.users.create',
            'security.users.update',
            'security.users.block',
            'security.users.unblock',
            'security.users.archive',
            'security.users.change_status',
            'security.users.override_status_transition',
            'security.users.force_password_change',
            'security.users.update_profile',
            'security.users.view_security_summary',
        ] as $permission) {
            $this->assertContains($permission, SuperadminPermissions::all());
        }
    }

    public function test_update_staff_profile_action_validates_and_updates_fields(): void
    {
        $actor = User::factory()->superadmin()->create();
        $profile = StaffProfile::factory()->create();

        app(UpdateStaffProfileAction::class)->handle($profile, [
            'phone' => '+370 611 11111',
            'work_email' => 'staff.updated@example.test',
            'display_name_translations' => ['en' => 'Updated Staff'],
        ], $actor, Request::create('/admin/users/profile', 'POST'));

        $this->assertDatabaseHas('staff_profiles', [
            'id' => $profile->id,
            'phone' => '+370 611 11111',
            'work_email' => 'staff.updated@example.test',
        ]);
        $this->assertTrue(AuditLog::query()->where('action', 'staff_profile_updated')->exists());
    }
}

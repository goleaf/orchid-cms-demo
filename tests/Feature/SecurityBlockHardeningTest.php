<?php

namespace Tests\Feature;

use App\Actions\Security\AuditPrivateFileDownloadAction;
use App\Actions\Security\ConfigureTwoFactorPlaceholderAction;
use App\Actions\Security\DeleteRoleAction;
use App\Actions\Security\DeleteUserAction;
use App\Actions\Security\RecordLoginAttemptAction;
use App\Actions\Security\RecordSecurityEventAction;
use App\Actions\Security\RecordUserSessionAction;
use App\Actions\Security\SanitizeExportRowAction;
use App\Actions\Security\SaveRoleAction;
use App\Actions\Security\SaveUserAction;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\LoginAttempt;
use App\Models\SecurityEvent;
use App\Models\User;
use App\Models\UserBranchAccess;
use App\Models\UserSession;
use App\Rules\BranchAccessRule;
use App\Rules\LastSuperadminRule;
use App\Rules\PasswordPolicyRule;
use App\Rules\SuperadminRoleProtectedRule;
use App\Support\Access\SuperadminPermissions;
use Database\Factories\AuditLogFactory;
use Database\Factories\LoginAttemptFactory;
use Database\Factories\SecurityEventFactory;
use Database\Factories\UserBranchAccessFactory;
use Database\Factories\UserSessionFactory;
use Database\Factories\RoleFactory;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\SecurityTranslationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Orchid\Platform\Models\Role;
use Tests\TestCase;

class SecurityBlockHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('en');
        $this->seed(LanguageSeeder::class);
        $this->seed(SecurityTranslationSeeder::class);
    }

    public function test_required_security_artifacts_exist(): void
    {
        foreach ([
            AuditLog::class,
            SecurityEvent::class,
            LoginAttempt::class,
            UserSession::class,
            UserBranchAccess::class,
            AuditLogFactory::class,
            SecurityEventFactory::class,
            LoginAttemptFactory::class,
            UserSessionFactory::class,
            UserBranchAccessFactory::class,
            SaveUserAction::class,
            DeleteUserAction::class,
            SaveRoleAction::class,
            DeleteRoleAction::class,
            RecordSecurityEventAction::class,
            RecordLoginAttemptAction::class,
            RecordUserSessionAction::class,
            AuditPrivateFileDownloadAction::class,
            PasswordPolicyRule::class,
            LastSuperadminRule::class,
            SuperadminRoleProtectedRule::class,
            BranchAccessRule::class,
            \App\Http\Requests\Security\UserRequest::class,
            \App\Http\Requests\Security\RoleRequest::class,
            \App\Http\Requests\Security\ProfileUpdateRequest::class,
            \App\Http\Requests\Security\ProfilePasswordRequest::class,
            SecurityTranslationSeeder::class,
        ] as $class) {
            $this->assertTrue(class_exists($class), $class);
        }
    }

    public function test_password_policy_and_two_factor_placeholder_are_safe(): void
    {
        $validator = Validator::make(
            ['password' => 'weak'],
            ['password' => [new PasswordPolicyRule]]
        );

        $this->assertTrue($validator->fails());
        $this->assertSame(tkey('security.validation.password_policy'), $validator->errors()->first('password'));

        $user = User::factory()->create();

        try {
            app(ConfigureTwoFactorPlaceholderAction::class)->handle($user, true, $user);
            $this->fail('Two-factor placeholder must not enable or store secrets.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                tkey('security.validation.two_factor_not_available'),
                $exception->errors()['user.two_factor_placeholder_enabled'][0],
            );
        }
    }

    public function test_last_superadmin_user_and_role_are_protected(): void
    {
        $role = RoleFactory::new()->create([
            'slug' => 'superadmin',
            'name' => 'Superadmin',
            'permissions' => SuperadminPermissions::enabled(),
        ]);
        $superadmin = User::factory()->create(['is_active' => true]);
        $superadmin->roles()->attach($role);

        try {
            app(DeleteUserAction::class)->handle($superadmin, $superadmin);
            $this->fail('The last active Superadmin should not be deleted.');
        } catch (ValidationException $exception) {
            $this->assertSame(tkey('security.validation.last_superadmin'), $exception->errors()['user'][0]);
        }

        try {
            app(DeleteRoleAction::class)->handle($role, $superadmin);
            $this->fail('The Superadmin role should not be deleted.');
        } catch (ValidationException $exception) {
            $this->assertSame(tkey('security.validation.superadmin_role_protected'), $exception->errors()['role'][0]);
        }
    }

    public function test_user_save_assigns_branch_access_and_writes_audit_records(): void
    {
        $actor = User::factory()->create([
            'permissions' => ['platform.systems.users' => true],
        ]);
        $user = User::factory()->create();
        $branch = Branch::factory()->create();
        $role = RoleFactory::new()->create(['slug' => 'manager']);
        $request = Request::create('/admin/users/'.$user->id, 'POST', [
            'user' => [
                'name' => 'Security Manager',
                'email' => 'security-manager@example.test',
                'roles' => [$role->id],
                'branch_ids' => [$branch->id],
                'is_active' => true,
            ],
            'permissions' => [
                base64_encode('platform.systems.users') => '1',
            ],
        ]);
        $request->setUserResolver(fn (): User => $actor);

        $saved = app(SaveUserAction::class)->handle($user, $request, $actor);

        $this->assertTrue($saved->roles()->where('slug', 'manager')->exists());
        $this->assertTrue($saved->hasBranchAccess($branch));
        $this->assertTrue(AuditLog::query()->where('action', 'user.updated')->exists());
        $this->assertTrue(SecurityEvent::query()->where('event_type', 'user.security_profile_saved')->exists());
    }

    public function test_login_attempts_and_sessions_are_hashed(): void
    {
        $user = User::factory()->create(['email' => 'secure@example.test']);
        $request = Request::create('/login', 'POST', [], [], [], [
            'HTTP_USER_AGENT' => 'Security Test Browser',
            'REMOTE_ADDR' => '127.0.0.1',
        ]);

        app(RecordLoginAttemptAction::class)->handle($user, 'secure@example.test', false, 'invalid_credentials', $request);
        app(RecordUserSessionAction::class)->handle($user, 'raw-session-id', $request);

        $attempt = LoginAttempt::query()->firstOrFail();
        $session = UserSession::query()->firstOrFail();

        $this->assertNotSame('secure@example.test', $attempt->identifier_hash);
        $this->assertSame(64, strlen($attempt->identifier_hash));
        $this->assertNotSame('raw-session-id', $session->session_hash);
        $this->assertSame(64, strlen($session->session_hash));
        $this->assertStringContainsString('Security Test Browser', $session->user_agent_preview);
    }

    public function test_exports_are_sanitized_and_private_downloads_are_audited_without_raw_paths(): void
    {
        $row = app(SanitizeExportRowAction::class)->handle([
            'name' => '=cmd',
            'password' => 'secret',
            'note' => '+SUM(1,1)',
        ]);

        $this->assertSame("'=cmd", $row['name']);
        $this->assertSame('[redacted]', $row['password']);
        $this->assertSame("'+SUM(1,1)", $row['note']);

        $actor = User::factory()->create();

        app(AuditPrivateFileDownloadAction::class)->handle($actor, 'private', 'students/private/contract.pdf');

        $log = AuditLog::query()->where('action', 'private_file.downloaded')->firstOrFail();

        $this->assertSame('private_file_download', $log->category);
        $this->assertSame(hash('sha256', 'students/private/contract.pdf'), $log->metadata['path_hash']);
        $this->assertArrayNotHasKey('path', $log->metadata);
    }
}

<?php

namespace Tests\Feature;

use App\Actions\Security\BuildSessionIdHashAction;
use App\Actions\Security\CheckFailedLoginThresholdAction;
use App\Actions\Security\DetectSuspiciousLoginAction;
use App\Actions\Security\PruneOldLoginAttemptsAction;
use App\Actions\Security\PruneOldUserSecuritySessionsAction;
use App\Actions\Security\RecordFailedLoginAction;
use App\Actions\Security\RecordLoginAttemptAction;
use App\Actions\Security\RecordSuccessfulLoginAction;
use App\Actions\Security\RecordUserLoginSessionAction;
use App\Actions\Security\RecordUserLogoutSessionAction;
use App\Actions\Security\RevokeAllUserSecuritySessionsAction;
use App\Actions\Security\RevokeOtherUserSecuritySessionsAction;
use App\Actions\Security\RevokeUserSecuritySessionAction;
use App\Actions\Security\SanitizeLoginMetadataAction;
use App\Actions\Security\TouchUserSecuritySessionAction;
use App\Http\Requests\Security\ExportLoginAttemptsRequest;
use App\Http\Requests\Security\ExportUserSecuritySessionsRequest;
use App\Http\Requests\Security\FilterLoginAttemptsRequest;
use App\Http\Requests\Security\FilterUserSecuritySessionsRequest;
use App\Http\Requests\Security\RevokeAllUserSecuritySessionsRequest;
use App\Http\Requests\Security\RevokeOtherUserSecuritySessionsRequest;
use App\Http\Requests\Security\RevokeUserSecuritySessionRequest;
use App\Listeners\Security\RecordFailedLoginListener;
use App\Listeners\Security\RecordLogoutListener;
use App\Listeners\Security\RecordSuccessfulLoginListener;
use App\Models\LoginAttempt;
use App\Models\SecurityEvent;
use App\Models\User;
use App\Models\UserSecuritySession;
use App\Rules\FailedLoginThresholdRule;
use App\Rules\LoginAttemptFailureReasonRule;
use App\Rules\LoginAttemptRetentionDaysRule;
use App\Rules\LoginMetadataSensitiveFieldRule;
use App\Rules\SessionIdHashRule;
use App\Rules\UserCanRevokeAllSessionsRule;
use App\Rules\UserCanRevokeSessionRule;
use App\Rules\UserSecuritySessionCanBeRevokedRule;
use App\Rules\UserSecuritySessionRetentionDaysRule;
use App\Support\Access\SuperadminPermissions;
use Database\Factories\LoginAttemptFactory;
use Database\Factories\UserSecuritySessionFactory;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\LoginAttemptDemoSeeder;
use Database\Seeders\SecurityTranslationSeeder;
use Database\Seeders\UserSecuritySessionDemoSeeder;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SecurityLoginAttemptsSessionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('en');
        $this->seed(LanguageSeeder::class);
        $this->seed(SecurityTranslationSeeder::class);
    }

    public function test_required_step_eight_artifacts_exist(): void
    {
        foreach ([
            LoginAttempt::class,
            UserSecuritySession::class,
            LoginAttemptFactory::class,
            UserSecuritySessionFactory::class,
            RecordLoginAttemptAction::class,
            RecordSuccessfulLoginAction::class,
            RecordFailedLoginAction::class,
            RecordUserLoginSessionAction::class,
            TouchUserSecuritySessionAction::class,
            RecordUserLogoutSessionAction::class,
            RevokeUserSecuritySessionAction::class,
            RevokeOtherUserSecuritySessionsAction::class,
            RevokeAllUserSecuritySessionsAction::class,
            PruneOldLoginAttemptsAction::class,
            PruneOldUserSecuritySessionsAction::class,
            DetectSuspiciousLoginAction::class,
            CheckFailedLoginThresholdAction::class,
            BuildSessionIdHashAction::class,
            SanitizeLoginMetadataAction::class,
            LoginAttemptFailureReasonRule::class,
            UserSecuritySessionCanBeRevokedRule::class,
            UserCanRevokeSessionRule::class,
            UserCanRevokeAllSessionsRule::class,
            SessionIdHashRule::class,
            LoginAttemptRetentionDaysRule::class,
            UserSecuritySessionRetentionDaysRule::class,
            FailedLoginThresholdRule::class,
            LoginMetadataSensitiveFieldRule::class,
            RevokeUserSecuritySessionRequest::class,
            RevokeOtherUserSecuritySessionsRequest::class,
            RevokeAllUserSecuritySessionsRequest::class,
            FilterLoginAttemptsRequest::class,
            FilterUserSecuritySessionsRequest::class,
            ExportLoginAttemptsRequest::class,
            ExportUserSecuritySessionsRequest::class,
            RecordSuccessfulLoginListener::class,
            RecordFailedLoginListener::class,
            RecordLogoutListener::class,
            LoginAttemptDemoSeeder::class,
            UserSecuritySessionDemoSeeder::class,
        ] as $class) {
            $this->assertTrue(class_exists($class), $class);
        }
    }

    public function test_factories_relationships_scopes_and_helpers_work(): void
    {
        $user = User::factory()->create();
        $successful = LoginAttempt::factory()->successful()->withUser($user)->create();
        $failed = LoginAttempt::factory()->invalidCredentials()->withUser($user)->create();
        $active = UserSecuritySession::factory()->active()->current()->withUser($user)->withDevice()->withLocation()->create();
        $revoked = UserSecuritySession::factory()->revoked($user)->withUser($user)->create();

        $this->assertTrue($successful->user->is($user));
        $this->assertTrue($active->user->is($user));
        $this->assertSame(1, LoginAttempt::query()->successful()->count());
        $this->assertSame(1, LoginAttempt::query()->failed()->count());
        $this->assertSame(1, UserSecuritySession::query()->active()->count());
        $this->assertSame(1, UserSecuritySession::query()->revoked()->count());
        $this->assertSame('Invalid credentials', $failed->display_failure_reason);
        $this->assertTrue($failed->is_failed);
        $this->assertTrue($active->is_active);
        $this->assertTrue($revoked->is_revoked);
        $this->assertSame('Workstation / Firefox / macOS', $active->display_device);
        $this->assertSame('Vilnius, LT', $active->display_location);
        $this->assertTrue($user->securitySessions()->whereKey($active->id)->exists());
        $this->assertTrue($user->activeSecuritySessions()->whereKey($active->id)->exists());
    }

    public function test_record_login_attempt_actions_create_attempts_events_and_sessions(): void
    {
        $user = User::factory()->create(['email' => 'secure@example.test']);
        $request = $this->requestWithSession('raw-session-id', [
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_USER_AGENT' => 'Security Test Browser',
        ]);

        app(RecordLoginAttemptAction::class)->handle(
            $user,
            'secure@example.test',
            false,
            LoginAttempt::FAILURE_INVALID_CREDENTIALS,
            $request,
            'web',
            metadata: ['password' => 'secret', 'token' => 'token-value', 'note' => 'safe'],
        );
        app(RecordSuccessfulLoginAction::class)->handle($user, $request, 'web');

        $this->assertDatabaseHas('login_attempts', [
            'email' => 'secure@example.test',
            'successful' => false,
            'failure_reason' => LoginAttempt::FAILURE_INVALID_CREDENTIALS,
        ]);
        $this->assertDatabaseHas('security_events', ['event_type' => 'login_failed']);
        $this->assertDatabaseHas('security_events', ['event_type' => 'login_success']);
        $this->assertNotNull($user->refresh()->last_login_at);
        $this->assertNotNull($user->last_seen_at);
        $this->assertSame('[REDACTED]', LoginAttempt::query()->failed()->firstOrFail()->metadata['password']);
        $this->assertSame(64, strlen(UserSecuritySession::query()->firstOrFail()->session_id_hash));
        $this->assertNotSame('raw-session-id', UserSecuritySession::query()->firstOrFail()->session_id_hash);
    }

    public function test_record_failed_login_detects_threshold_and_suspicious_patterns(): void
    {
        $user = User::factory()->create(['email' => 'target@example.test']);
        $request = Request::create('/admin/login', 'POST', [], [], [], ['REMOTE_ADDR' => '10.0.0.10']);

        LoginAttempt::factory()->count(4)->failed()->withEmail('target@example.test')->withIpAddress('10.0.0.10')->recent()->create();

        $result = app(RecordFailedLoginAction::class)->handle($user, 'target@example.test', null, $request, 'web');

        $this->assertTrue($result['threshold']['exceeded']);
        $this->assertTrue($result['suspicious']['suspicious']);
        $this->assertTrue(SecurityEvent::query()->where('event_type', 'login_threshold_exceeded')->exists());
    }

    public function test_session_touch_logout_and_revoke_actions_work(): void
    {
        $user = User::factory()->create();
        $actor = User::factory()->create(['permissions' => ['security.sessions.revoke' => true]]);
        $request = $this->requestWithSession('session-to-touch', ['REMOTE_ADDR' => '127.0.0.1']);
        $session = app(RecordUserLoginSessionAction::class)->handle($user, 'session-to-touch', $request, 'web');

        $session->forceFill(['last_activity_at' => now()->subMinutes(10)])->save();
        app(TouchUserSecuritySessionAction::class)->handle($user, 'session-to-touch', $request, 0);
        $this->assertTrue($session->refresh()->last_activity_at->gt(now()->subMinutes(2)));

        app(RecordUserLogoutSessionAction::class)->handle($user, 'session-to-touch', $request);
        $this->assertNotNull($session->refresh()->logged_out_at);

        $revokable = UserSecuritySession::factory()->active()->withUser($user)->create();
        app(RevokeUserSecuritySessionAction::class)->handle($revokable, $actor, Request::create('/admin/security/sessions', 'POST'), false);
        $this->assertNotNull($revokable->refresh()->revoked_at);
        $this->assertTrue(SecurityEvent::query()->where('event_type', 'session_revoked')->exists());
    }

    public function test_revoke_other_and_all_sessions_respect_current_session_and_permission(): void
    {
        $target = User::factory()->create();
        $actor = User::factory()->create(['permissions' => ['security.sessions.revoke_all' => true, 'security.sessions.revoke' => true]]);
        $request = $this->requestWithSession('current-session');
        $currentHash = app(BuildSessionIdHashAction::class)->handle($request->session()->getId());
        $current = UserSecuritySession::factory()->active()->current()->withUser($target)->create(['session_id_hash' => $currentHash]);
        $otherOne = UserSecuritySession::factory()->active()->withUser($target)->create();
        $otherTwo = UserSecuritySession::factory()->active()->withUser($target)->create();

        $otherCount = app(RevokeOtherUserSecuritySessionsAction::class)->handle($target, $request->session()->getId(), $actor, $request);

        $this->assertSame(2, $otherCount);
        $this->assertTrue($current->refresh()->is_active);
        $this->assertNotNull($otherOne->refresh()->revoked_at);
        $this->assertNotNull($otherTwo->refresh()->revoked_at);

        $remaining = UserSecuritySession::factory()->active()->withUser($target)->create();
        $allCount = app(RevokeAllUserSecuritySessionsAction::class)->handle($target, $actor, $request, false);

        $this->assertSame(1, $allCount);
        $this->assertNotNull($remaining->refresh()->revoked_at);

        $unauthorized = User::factory()->create();
        $this->expectException(ValidationException::class);
        app(RevokeAllUserSecuritySessionsAction::class)->handle($target, $unauthorized, $request, false);
    }

    public function test_hash_sanitizer_prune_commands_and_validation_rules_work(): void
    {
        $hash = app(BuildSessionIdHashAction::class)->handle('raw-session-id');
        $metadata = app(SanitizeLoginMetadataAction::class)->handle([
            'password' => 'secret',
            'access_token' => 'token',
            'raw_session_id' => 'raw-session-id',
            'safe' => 'kept',
        ]);

        $this->assertSame(64, strlen((string) $hash));
        $this->assertNotSame('raw-session-id', $hash);
        $this->assertSame('[REDACTED]', $metadata['password']);
        $this->assertSame('[REDACTED]', $metadata['access_token']);
        $this->assertSame('[REDACTED]', $metadata['raw_session_id']);
        $this->assertSame('kept', $metadata['safe']);

        LoginAttempt::factory()->old()->create();
        UserSecuritySession::factory()->old()->create();

        $this->assertSame(1, app(PruneOldLoginAttemptsAction::class)->handle(30));
        $this->assertSame(1, app(PruneOldUserSecuritySessionsAction::class)->handle(30));
        $this->artisan('security:prune-login-attempts', ['--days' => 30])->assertSuccessful();
        $this->artisan('security:prune-sessions', ['--days' => 30])->assertSuccessful();

        $failureValidator = Validator::make(['reason' => 'bad'], ['reason' => [new LoginAttemptFailureReasonRule]]);
        $hashValidator = Validator::make(['hash' => 'raw-session-id'], ['hash' => [new SessionIdHashRule]]);
        $metadataValidator = Validator::make(['metadata' => ['token' => 'plain']], ['metadata' => [new LoginMetadataSensitiveFieldRule]]);

        $this->assertTrue($failureValidator->fails());
        $this->assertSame(tkey('security.validation.invalid_login_failure_reason'), $failureValidator->errors()->first('reason'));
        $this->assertTrue($hashValidator->fails());
        $this->assertSame(tkey('security.validation.invalid_session_id_hash'), $hashValidator->errors()->first('hash'));
        $this->assertTrue($metadataValidator->fails());
        $this->assertSame(tkey('security.validation.login_metadata_sensitive_field_not_redacted'), $metadataValidator->errors()->first('metadata'));
    }

    public function test_auth_event_listeners_create_records(): void
    {
        $user = User::factory()->create(['email' => 'listener@example.test']);
        $request = $this->requestWithSession('listener-session');
        $this->app->instance('request', $request);

        Event::dispatch(new Login('web', $user, false));
        Event::dispatch(new Failed('web', null, ['email' => 'missing@example.test', 'password' => 'secret']));
        Event::dispatch(new Logout('web', $user));

        $this->assertTrue(LoginAttempt::query()->where('email', 'listener@example.test')->successful()->exists());
        $this->assertTrue(LoginAttempt::query()->where('email', 'missing@example.test')->failed()->exists());
        $this->assertNotNull(UserSecuritySession::query()->where('user_id', $user->id)->firstOrFail()->logged_out_at);
    }

    public function test_demo_seeders_are_idempotent_translations_and_permissions_exist(): void
    {
        $admin = User::factory()->create(['email' => 'admin@example.com']);

        $this->seed(LoginAttemptDemoSeeder::class);
        $this->seed(LoginAttemptDemoSeeder::class);
        $this->seed(UserSecuritySessionDemoSeeder::class);
        $this->seed(UserSecuritySessionDemoSeeder::class);

        $this->assertSame(2, LoginAttempt::query()->where('email', $admin->email)->count());
        $this->assertSame(1, UserSecuritySession::query()->where('user_id', $admin->id)->count());

        foreach (['ru', 'en', 'lt', 'pl'] as $locale) {
            app()->setLocale($locale);
            $this->assertNotSame('security.sessions.title', tkey('security.sessions.title'));
            $this->assertNotSame('permissions.security.sessions.revoke_all', tkey('permissions.security.sessions.revoke_all'));
            $this->assertNotSame('security.validation.invalid_session_id_hash', tkey('security.validation.invalid_session_id_hash'));
        }

        foreach ([
            'security.login_attempts.view',
            'security.login_attempts.export',
            'security.sessions.view',
            'security.sessions.revoke',
            'security.sessions.revoke_own',
            'security.sessions.revoke_all',
            'security.sessions.export',
        ] as $permission) {
            $this->assertContains($permission, SuperadminPermissions::all());
        }
    }

    private function requestWithSession(string $sessionId, array $server = []): Request
    {
        $request = Request::create('/admin', 'GET', [], [], [], $server + ['HTTP_USER_AGENT' => 'Security Test Browser']);
        $session = $this->app['session.store'];
        $session->setId($sessionId);
        $request->setLaravelSession($session);

        return $request;
    }
}

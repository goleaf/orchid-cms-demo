# Security Hardening

This repository is a local driving-school operating system. The security module protects local staff accounts, Orchid roles, permissions, branch access, audit trails, login attempts, and session records. It does not add tenant logic, SaaS features, remote telemetry, or background services.

## What It Covers

- User and role writes go through Form Requests and Actions.
- Orchid user and role screens keep persistence logic out of the screen methods.
- User statuses are local dictionary records with one active default status.
- Staff profiles extend user accounts with local branch, public profile, locale, timezone, and staff number data.
- Permission registry records document existing Orchid permissions with local groups, translations, modules, and risk levels without replacing Orchid access checks.
- The Superadmin role is protected from deletion or slug changes.
- The last active Superadmin user cannot be deleted, deactivated, locked, blocked, archived, or stripped of the Superadmin role.
- Branch access is stored locally in `user_branch_access`.
- Audit logs are stored in `audit_logs` with sensitive fields redacted.
- Security events are stored in `security_events`.
- Login attempts are stored in `login_attempts` with failure reasons, sanitized metadata, audit records, and security events.
- User security sessions are stored in `user_security_sessions` with hashed session identifiers only. The older `user_sessions` table remains for backward compatibility.
- User lifecycle actions create, update, block, unblock, archive, change status, force password changes, update profile preferences, keep staff profiles in sync, and build security summaries without adding another user table.
- Password changes use the local password policy rule.
- Two-factor setup is a safe placeholder and does not store secrets.
- CSV export rows are sanitized against spreadsheet formula injection and sensitive keyed values are redacted.
- Private file download auditing records a path hash and filename, not the raw private path.

## Main Files

- Actions: `app/Actions/Security`
- Requests: `app/Http/Requests/Security`
- Rules: `app/Rules`
- Models: `app/Models/AuditLog.php`, `SecurityEvent.php`, `LoginAttempt.php`, `UserSession.php`, `UserSecuritySession.php`, `UserBranchAccess.php`, `UserStatus.php`, `StaffProfile.php`, `PermissionGroup.php`, `PermissionRegistryItem.php`
- Migrations: `database/migrations/2026_05_28_210000_create_security_block_thirteen_tables.php`, `database/migrations/2026_05_28_220000_create_user_statuses_and_staff_profiles.php`, `database/migrations/2026_05_28_230000_create_permission_registry_tables.php`, `database/migrations/2026_05_28_231000_create_login_attempts_and_user_security_sessions.php`
- Translation and setup seeders: `database/seeders/SecurityTranslationSeeder.php`, `UserStatusSeeder.php`, `StaffProfileDemoSeeder.php`, `PermissionGroupSeeder.php`, `PermissionRegistrySeeder.php`, `LoginAttemptDemoSeeder.php`, `UserSecuritySessionDemoSeeder.php`
- Tests: `tests/Feature/SecurityBlockHardeningTest.php`, `tests/Feature/SecurityUserStatusesStaffProfilesTest.php`, `tests/Feature/SecurityPermissionRegistryTest.php`, `tests/Feature/SecurityLoginAttemptsSessionsTest.php`, `tests/Feature/SecurityUserManagementLifecycleTest.php`

## User Statuses And Staff Profiles

The security module now stores account lifecycle state in `user_statuses`.
The seeded statuses are active, inactive, blocked, and archived. Active is the default, and the model prevents more than one default status from remaining selected.

`users.status_id` links a local account to a status without replacing Orchid's user model. Blocking or archiving a status makes `User::isLockedOut()` return true, so existing login protection can reject the account safely. The status change rule also prevents the last Superadmin from being blocked or archived.

`staff_profiles` stores staff-only profile metadata for local school employees. It is one-to-one with `users`, optionally linked to a branch, and supports translated display names, job titles, public bios, staff numbers, locale, timezone, avatar, public visibility, and internal notes.

## User Account Lifecycle

Internal staff users still use the existing Laravel and Orchid user model. The lifecycle layer adds backend actions for safe account writes:

- create and update internal users
- block, unblock, archive, and change status
- update profile, locale, timezone, and staff profile data separately from role and permission changes
- force or clear password-change requirements
- mark a user as seen with throttled writes
- resolve display names, status details, login eligibility, and a security summary

Allowed status transitions are active to inactive, blocked, or archived; inactive to active, blocked, or archived; blocked to active or archived. Archived is final unless an operator has the explicit override permission.

The last active Superadmin remains protected from blocking, archiving, unsafe status changes, and role removal. A current user also cannot block or archive their own active access unless a future explicit recovery flow uses the override path.

Blocking and archiving revoke active tracked security sessions and clear remember tokens when available. The revocation is fail-safe so lifecycle writes are not broken by optional session tracking problems.

Login checks are read-only. They return whether the account may login, a reason such as inactive, blocked, archived, or must-change-password, and a status code for callers that need structured handling.

Audit and security event writes are fail-safe and redact sensitive data. Password values are never written to audit metadata.

Default status data can be refreshed with:

```bash
php artisan db:seed --class=UserStatusSeeder
```

Local demo staff profile data can be refreshed with:

```bash
php artisan db:seed --class=StaffProfileDemoSeeder
```

## Permission Registry

Orchid permissions remain the authorization source for roles, users, menus, and screens. The registry stores metadata around those same strings so permissions can be grouped, translated, audited, sorted, and reviewed by risk level.

Default permission groups cover website, CRM, students, education, schedule, lessons, driving, documents, finance, exams, notifications, analytics, security, and system modules. Registry sync reads existing local Orchid permission declarations and the Superadmin permission list, creates missing registry records, assigns groups and risk levels, and never removes custom permissions.

Refresh the registry with:

```bash
php artisan db:seed --class=PermissionGroupSeeder
php artisan db:seed --class=PermissionRegistrySeeder
```

System permission codes are protected from renaming. Critical permissions require Superadmin validation when changed through registry requests.

## Login Attempts And Sessions

Laravel and Orchid authentication remain the source of login behavior. Step 8 adds fail-safe listeners for login, failed login, and logout events. The listeners call Actions and catch failures so authentication flow is not broken by tracking errors.

Login attempt metadata is sanitized before storage. Passwords, tokens, cookies, private keys, two-factor secrets, recovery codes, authorization headers, and raw session IDs are replaced with `[REDACTED]`.

User security sessions store only a hashed session identifier. The raw Laravel session ID is never stored or exported. Session activity is touched by a throttled web middleware, session revocation writes audit and security events, and pruning is manual:

```bash
php artisan security:prune-login-attempts --days=90
php artisan security:prune-sessions --days=90
```

Suspicious login detection currently covers repeated failed attempts and new IP addresses. Impossible travel is a documented future enhancement and would require a local geolocation data source.

## Operational Notes

Run the translation seeder after migrating when security UI labels are missing:

```bash
php artisan db:seed --class=SecurityTranslationSeeder
```

Use the focused verification suite while changing this module:

```bash
php artisan test --filter=SecurityBlockHardeningTest
php artisan test --filter=SecurityUserStatusesStaffProfilesTest
php artisan test --filter=SecurityPermissionRegistryTest
php artisan test --filter=SecurityLoginAttemptsSessionsTest
php artisan test --filter=SecurityUserManagementLifecycleTest
php artisan test --filter=SuperadminRoleTest
```

## Limitations

Two-factor authentication is intentionally a placeholder. Enabling it raises a translated validation error and stores no secret until a real local implementation is added.

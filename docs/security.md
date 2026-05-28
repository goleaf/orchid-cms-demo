# Security Hardening

This repository is a local driving-school operating system. The security module protects local staff accounts, Orchid roles, permissions, branch access, audit trails, login attempts, and session records. It does not add tenant logic, SaaS features, remote telemetry, or background services.

## What It Covers

- User and role writes go through Form Requests and Actions.
- Orchid user and role screens keep persistence logic out of the screen methods.
- User statuses are local dictionary records with one active default status.
- Staff profiles extend user accounts with local branch, public profile, locale, timezone, and staff number data.
- The Superadmin role is protected from deletion or slug changes.
- The last active Superadmin user cannot be deleted, deactivated, locked, blocked, archived, or stripped of the Superadmin role.
- Branch access is stored locally in `user_branch_access`.
- Audit logs are stored in `audit_logs` with sensitive fields redacted.
- Security events are stored in `security_events`.
- Login attempts are stored in `login_attempts` with hashed login identifiers.
- User sessions are stored in `user_sessions` with hashed session identifiers only.
- Password changes use the local password policy rule.
- Two-factor setup is a safe placeholder and does not store secrets.
- CSV export rows are sanitized against spreadsheet formula injection and sensitive keyed values are redacted.
- Private file download auditing records a path hash and filename, not the raw private path.

## Main Files

- Actions: `app/Actions/Security`
- Requests: `app/Http/Requests/Security`
- Rules: `app/Rules`
- Models: `app/Models/AuditLog.php`, `SecurityEvent.php`, `LoginAttempt.php`, `UserSession.php`, `UserBranchAccess.php`, `UserStatus.php`, `StaffProfile.php`
- Migrations: `database/migrations/2026_05_28_210000_create_security_block_thirteen_tables.php`, `database/migrations/2026_05_28_220000_create_user_statuses_and_staff_profiles.php`
- Translation and setup seeders: `database/seeders/SecurityTranslationSeeder.php`, `UserStatusSeeder.php`, `StaffProfileDemoSeeder.php`
- Tests: `tests/Feature/SecurityBlockHardeningTest.php`, `tests/Feature/SecurityUserStatusesStaffProfilesTest.php`

## User Statuses And Staff Profiles

The security module now stores account lifecycle state in `user_statuses`.
The seeded statuses are active, inactive, blocked, and archived. Active is the default, and the model prevents more than one default status from remaining selected.

`users.status_id` links a local account to a status without replacing Orchid's user model. Blocking or archiving a status makes `User::isLockedOut()` return true, so existing login protection can reject the account safely. The status change rule also prevents the last Superadmin from being blocked or archived.

`staff_profiles` stores staff-only profile metadata for local school employees. It is one-to-one with `users`, optionally linked to a branch, and supports translated display names, job titles, public bios, staff numbers, locale, timezone, avatar, public visibility, and internal notes.

Default status data can be refreshed with:

```bash
php artisan db:seed --class=UserStatusSeeder
```

Local demo staff profile data can be refreshed with:

```bash
php artisan db:seed --class=StaffProfileDemoSeeder
```

## Operational Notes

Run the translation seeder after migrating when security UI labels are missing:

```bash
php artisan db:seed --class=SecurityTranslationSeeder
```

Use the focused verification suite while changing this module:

```bash
php artisan test --filter=SecurityBlockHardeningTest
php artisan test --filter=SecurityUserStatusesStaffProfilesTest
php artisan test --filter=SuperadminRoleTest
```

## Limitations

Two-factor authentication is intentionally a placeholder. Enabling it raises a translated validation error and stores no secret until a real local implementation is added.

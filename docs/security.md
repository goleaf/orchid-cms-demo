# Security Hardening

This repository is a local driving-school operating system. The security module protects local staff accounts, Orchid roles, permissions, branch access, audit trails, login attempts, and session records. It does not add tenant logic, SaaS features, remote telemetry, or background services.

## What It Covers

- User and role writes go through Form Requests and Actions.
- Orchid user and role screens keep persistence logic out of the screen methods.
- The Superadmin role is protected from deletion or slug changes.
- The last active Superadmin user cannot be deleted, deactivated, locked, or stripped of the Superadmin role.
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
- Models: `app/Models/AuditLog.php`, `SecurityEvent.php`, `LoginAttempt.php`, `UserSession.php`, `UserBranchAccess.php`
- Migration: `database/migrations/2026_05_28_210000_create_security_block_thirteen_tables.php`
- Translation seeder: `database/seeders/SecurityTranslationSeeder.php`
- Tests: `tests/Feature/SecurityBlockHardeningTest.php`

## Operational Notes

Run the translation seeder after migrating when security UI labels are missing:

```bash
php artisan db:seed --class=SecurityTranslationSeeder
```

Use the focused verification suite while changing this module:

```bash
php artisan test --filter=SecurityBlockHardeningTest
php artisan test --filter=SuperadminRoleTest
```

## Limitations

Two-factor authentication is intentionally a placeholder. Enabling it raises a translated validation error and stores no secret until a real local implementation is added.

# Security Memory

- Block 13 security hardening is local to this driving-school repository. Do not add tenant, SaaS, subscription, reseller, remote telemetry, or background service behavior.
- User and role persistence should stay in `app/Actions/Security` and `app/Http/Requests/Security`; Orchid screens should remain thin.
- Audit logs, security events, login attempts, and user session records must redact sensitive fields and must not store raw session identifiers or raw login identifiers.
- The `superadmin` role and the last active Superadmin user are protected safety boundaries.
- User lifecycle state is local and uses `user_statuses`; active is the default, and blocked or archived statuses must not lock out the last Superadmin.
- Staff identity extensions belong in one-to-one `staff_profiles` records linked to `users`, with optional branch, locale, timezone, translated public profile fields, and no tenant or SaaS behavior.
- Permission registry records are metadata around existing Orchid permission strings. They group, translate, risk-label, sync, and audit permissions without replacing Orchid role/user access checks.
- Step 8 login/session tracking uses `login_attempts` plus `user_security_sessions`; session IDs must be HMAC-hashed, metadata must redact passwords/tokens/raw session IDs, and Laravel/Orchid authentication remains unchanged.
- Step 9 user lifecycle uses backend Actions, Form Requests, and Rules for create/update/block/unblock/archive/status/profile/preference/password-change/seen/summary flows; blocking or archiving revokes tracked sessions and must never expose raw passwords.

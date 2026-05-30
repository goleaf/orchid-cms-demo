# Security Data Map

This map documents local security data only. It does not describe SaaS tenancy, remote telemetry, subscriptions, reseller accounts, or platform-owner data.

## Account Tables

- `users`: Orchid-compatible local user accounts. Step 2 adds optional status, timezone, last login, last seen, and password-change requirement fields only when missing.
- `user_statuses`: local account status dictionary. Active is seeded as the default. Blocked and archived statuses lock out the account.
- `staff_profiles`: one local staff profile per user, with optional branch, public profile translations, locale, timezone, and internal notes.
- `permission_groups`: local registry groups for documenting existing Orchid permissions.
- `permission_registry_items`: local metadata records for permission strings, including group, module, risk level, translations, and system/custom state.
- `user_branch_access`: local user-to-branch access records.
- `role_users`: Orchid role assignment pivot.

## Security Event Tables

- `audit_logs`: compact audit records with sensitive values redacted.
- `security_events`: security lifecycle events.
- `login_attempts`: login attempt records with normalized email, guard, IP address, user agent, failure reason, sanitized metadata, and attempt timestamps.
- `user_sessions`: legacy session records with hashed session identifiers only.
- `user_security_sessions`: current security-session records with HMAC-hashed session identifiers, activity timestamps, revocation fields, and sanitized metadata.

## Sensitive Data Boundaries

- Raw session identifiers are never stored.
- Session hashes are not exported by default.
- Passwords, tokens, private keys, cookies, and API credentials must not be written to memory, docs, audit metadata, or exports.
- Login and session metadata redacts sensitive values as `[REDACTED]`.
- Private file downloads store audit evidence without the raw private path.

## Local Access Boundaries

- The Superadmin role is the highest local driving-school role.
- The last active Superadmin cannot be deleted, deactivated, locked, blocked, archived, or stripped of the Superadmin role.
- Branch access is local branch access, not tenant isolation.
- Permission registry groups are documentation and management metadata around Orchid permissions, not tenant or company isolation.

## Lifecycle Data Flow

- User creation and update actions write only the existing `users` table and optional linked `staff_profiles`, `role_users`, and `user_branch_access` records.
- Status changes write `users.status_id`; when the target status is blocked or archived, active `user_security_sessions` records are marked revoked.
- Profile updates do not change roles, permissions, statuses, or branch access.
- Force-password-change actions write `users.must_change_password` when the column exists and do not store or expose passwords.
- Seen tracking writes `users.last_seen_at` only after a throttle window.
- Login eligibility checks only read `users`, `user_statuses`, and optional flags; they do not login the user or mutate state.
- Audit logs and security events store lifecycle evidence with sensitive values redacted.

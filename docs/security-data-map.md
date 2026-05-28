# Security Data Map

This map documents local security data only. It does not describe SaaS tenancy, remote telemetry, subscriptions, reseller accounts, or platform-owner data.

## Account Tables

- `users`: Orchid-compatible local user accounts. Step 2 adds optional status, timezone, last login, last seen, and password-change requirement fields only when missing.
- `user_statuses`: local account status dictionary. Active is seeded as the default. Blocked and archived statuses lock out the account.
- `staff_profiles`: one local staff profile per user, with optional branch, public profile translations, locale, timezone, and internal notes.
- `user_branch_access`: local user-to-branch access records.
- `role_users`: Orchid role assignment pivot.

## Security Event Tables

- `audit_logs`: compact audit records with sensitive values redacted.
- `security_events`: security lifecycle events.
- `login_attempts`: login attempt records with hashed identifiers.
- `user_sessions`: session records with hashed session identifiers only.

## Sensitive Data Boundaries

- Raw session identifiers are never stored.
- Raw login identifiers are hashed in login attempt records.
- Passwords, tokens, private keys, cookies, and API credentials must not be written to memory, docs, audit metadata, or exports.
- Private file downloads store audit evidence without the raw private path.

## Local Access Boundaries

- The Superadmin role is the highest local driving-school role.
- The last active Superadmin cannot be deleted, deactivated, locked, blocked, archived, or stripped of the Superadmin role.
- Branch access is local branch access, not tenant isolation.

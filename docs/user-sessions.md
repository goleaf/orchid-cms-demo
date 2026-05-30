# User Security Sessions

User security sessions track local admin login sessions without storing raw session identifiers. They complement Laravel and Orchid sessions; they do not replace authentication.

## Hash Policy

`user_security_sessions.session_id_hash` stores an HMAC-SHA256 hash of the raw session ID using the application key. Raw session IDs are never written to the database, audit logs, security events, exports, or documentation.

The older `user_sessions` table remains for backward compatibility with the first security hardening step. New Step 8 tracking writes to `user_security_sessions`.

## What Is Stored

Session records include:

- user and guard
- hashed session ID
- IP address and user agent
- lightweight device, browser, and platform labels
- optional country and city fields
- login, last activity, logout, and revoke timestamps
- revoker when a session is revoked
- sanitized metadata

`session_id_hash` is hidden from model serialization and must not be exported by default.

## Session Activity

A fail-safe web middleware touches the current session after authenticated requests. It skips writes when the last activity timestamp is recent, keeping the hook lightweight.

## Revocation

Security actions support:

- revoking one active session
- revoking other sessions for a user while preserving the current session
- revoking all sessions with `security.sessions.revoke_all`

The current Superadmin session is protected from accidental full revocation unless a future explicit recovery flow is added.

User lifecycle blocking and archiving also revoke active tracked sessions and clear remember tokens when available. This happens through a fail-safe action, so an optional session-tracking problem does not leave the account update half-finished.

## Pruning

Old logged-out or revoked sessions are pruned manually with:

```bash
php artisan security:prune-sessions --days=90
```

Active sessions are not pruned.

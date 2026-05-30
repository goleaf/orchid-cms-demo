# Login Attempts

Login attempt tracking records local authentication evidence for the driving school admin area. It does not replace Laravel or Orchid authentication and does not add an external identity provider.

## What Is Stored

`login_attempts` stores successful and failed attempts with:

- user link when known
- normalized email for security review
- guard, IP address, and user agent
- success flag and failure reason
- sanitized metadata
- attempt timestamp

Sensitive metadata fields such as passwords, tokens, cookies, authorization headers, raw session IDs, private keys, recovery codes, and two-factor secrets are replaced with `[REDACTED]`.

## Failure Reasons

Supported failure reasons are:

- `invalid_credentials`
- `user_blocked`
- `user_inactive`
- `user_archived`
- `too_many_attempts`
- `password_expired`
- `must_change_password`
- `unknown`

User lifecycle login checks are read-only and can return blocked, archived, inactive, and must-change-password reasons before a caller decides how to handle authentication. They do not write login attempt records by themselves.

## Events And Audit

Successful attempts create `login_success` security events. Failed attempts create `login_failed` warning events. Threshold breaches create `login_threshold_exceeded` events. Audit rows use compact metadata and the existing security redaction pipeline.

## Suspicious Login Detection

The local detector flags repeated failed attempts for the same email or IP address and a known user logging in from a new IP address. Impossible travel is intentionally left as a TODO because this project has no external geolocation provider.

## Pruning

Old records are pruned manually with:

```bash
php artisan security:prune-login-attempts --days=90
```

No scheduler entry is added in this step. A future local scheduler can call the command if the school wants automatic retention.

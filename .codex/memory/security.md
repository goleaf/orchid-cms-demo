# Security Memory

- Block 13 security hardening is local to this driving-school repository. Do not add tenant, SaaS, subscription, reseller, remote telemetry, or background service behavior.
- User and role persistence should stay in `app/Actions/Security` and `app/Http/Requests/Security`; Orchid screens should remain thin.
- Audit logs, security events, login attempts, and user session records must redact sensitive fields and must not store raw session identifiers or raw login identifiers.
- The `superadmin` role and the last active Superadmin user are protected safety boundaries.

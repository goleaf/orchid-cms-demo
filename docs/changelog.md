# Changelog

## 2026-05-30

- Added backend user lifecycle actions, validation, permissions, translations, factories, audit integration, session revocation, and documentation for internal staff account management.
- Added a shared design brief for the public website and Orchid admin UI, including navigation, localization, layout, data-flow, and verification rules.
- Added staff profile documentation and linked the security documentation set from the main project documentation indexes.
- Improved the public homepage navigation and hero responsiveness, keeping the public links translated and the first screen usable on desktop and mobile.

## 2026-05-28

- Added local login attempt and security-session tracking with safe session hashing, translated failure reasons, revocation actions, pruning commands, audit records, and security events.

- Added a local permission registry around existing Orchid permissions, including permission groups, risk levels, protected system codes, sync support, translated validation, and repeatable setup.

- Added local user statuses and staff profiles, including translated labels, safe validation, repeatable setup data, and Superadmin lockout protection for blocked or archived accounts.

- Completed local security hardening for users, roles, permissions, branch access, audit records, login tracking, hashed session records, password policy, safe two-factor placeholder handling, sanitized exports, and private download auditing.

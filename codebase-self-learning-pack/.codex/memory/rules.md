# Rules

- Do not implement SaaS tenant logic unless the user explicitly changes direction.
- Do not create subscription billing, tenant isolation, platform-owner dashboards, or reseller logic.
- All visible UI labels should be translatable through translation keys, `tkey()`, or the project's localization helper.
- Avoid hardcoded visible Russian labels in Orchid screens, menus, table columns, buttons, modals, alerts, validations, notifications, public pages, or documents.
- Superadmin must be able to manage languages and translations from Orchid.
- Store only safe project memory. Never store secrets, credentials, tokens, cookies, private student/customer data, or full private user prompts.
- Learn only from code evidence, tests, docs, or explicit user corrections.


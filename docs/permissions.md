# Permission Registry

This project uses Orchid permissions for real access checks. The permission registry adds local documentation, grouping, translations, risk labels, and sync support around those Orchid permission strings.

It does not replace Orchid roles, does not add SaaS tenant permissions, and does not create platform-owner or reseller access.

## Why It Exists

Orchid stores permission choices on users and roles as permission strings. That is still the source used by `hasAccess()` and screen `permission()` checks.

The registry stores metadata for those strings:

- group for navigation and future management
- local module
- translated name and description
- risk level
- system/custom flag
- display order

## Tables

- `permission_groups` stores groups such as website, CRM, students, education, security, and system.
- `permission_registry_items` stores permission metadata keyed by the exact Orchid permission code.

## Groups

Default groups are seeded for website, customer relationship management, students, education, schedule, lessons, driving, documents, finance, exams, notifications, analytics, security, and system.

Groups use translated JSON labels first and fall back to `security.permission_groups.*` translation keys.

## Risk Levels

Risk levels are:

- `low`
- `normal`
- `high`
- `critical`

Critical permissions cover destructive, lockout, override, role, user, and security-management access. Critical changes are guarded by validation and require a Superadmin context.

Step 8 adds local security permissions for login-attempt review, session review, session revocation, own-session revocation, all-session revocation, and exports. These permissions still use Orchid access checks; the registry only documents and groups them.

Step 9 adds granular local user lifecycle permissions for viewing users, creating users, updating users, blocking, unblocking, archiving, changing statuses, overriding status transitions, forcing password changes, updating profiles, and viewing user security summaries. The older broad system user permission remains for compatibility with existing Orchid screens.

## System And Custom Permissions

System permissions come from existing Orchid permission declarations, the local platform provider, and `SuperadminPermissions`. Sync marks discovered permissions as system records and protects their codes from being renamed.

Custom registry records are allowed for future local extensions. Sync never deletes custom or unknown records.

## Sync Process

Run the seeders after migration:

```bash
php artisan db:seed --class=PermissionGroupSeeder
php artisan db:seed --class=PermissionRegistrySeeder
```

The registry sync:

- imports existing local Orchid permission strings
- creates missing registry records
- assigns groups by module or permission prefix
- assigns risk levels by action words and security scope
- updates labels only when the current labels are empty or generated fallbacks
- does not delete custom records
- writes an audit log when audit tables are available

If Orchid permissions cannot be discovered, sync exits safely without changing data.

## Adding A Module Permission

1. Add the permission to the relevant Orchid screen/menu/provider and to `SuperadminPermissions` when the Superadmin role should receive it.
2. Add translated permission labels where visible in Orchid.
3. Run `php artisan db:seed --class=PermissionRegistrySeeder`.
4. If the module needs a new group or risk mapping, update the sync action and translations.
5. Add a focused test for the permission, registry metadata, and authorization behavior.

## Translations

Registry labels are stored as JSON translations on groups and items. Stable UI labels and validation errors are also seeded through `SecurityTranslationSeeder` for Russian, English, Lithuanian, and Polish.

## Limitations

This step adds the data model, actions, requests, rules, factories, seeders, translations, tests, and documentation. It does not add a dedicated Orchid registry management screen yet.

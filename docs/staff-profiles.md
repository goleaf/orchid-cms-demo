# Staff Profiles

Staff profiles extend existing Orchid users with local driving-school employee information. They do not create student portal accounts, tenants, company switching, or external identity records.

## Relationship

Each staff profile belongs to exactly one user. A user can have only one staff profile. If a missing profile is needed, the lifecycle action creates one and restores a soft-deleted profile instead of creating a duplicate.

## Stored Data

Staff profiles can store:

- staff number
- branch
- translated display name
- translated job title
- translated public bio
- phone
- work email
- preferred locale
- timezone
- avatar path
- public website visibility
- internal notes

Staff profile data is separate from roles, permissions, status, and branch access.

## Lifecycle Use

User creation can create a staff profile when staff profile data is provided. Profile update actions can update display name, phone, work email, avatar, locale, and timezone. A dedicated staff profile update action validates phone, work email, branch, locale, timezone, and one-profile-per-user behavior.

## Audit

Staff profile creation and updates write audit records when audit logging is available. Audit writes are fail-safe and do not store passwords or secrets.

## Limitations

Dedicated staff profile management screens are not part of this step. The backend layer is ready for those screens.

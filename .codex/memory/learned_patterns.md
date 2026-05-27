# Learned Patterns

Add stable codebase conventions here after they are proven by code or confirmed by the user.

- Superadmin platform access is centralized in App\Support\Access\SuperadminPermissions and applied by SuperadminRoleSeeder; seeders should reuse SuperadminPermissions::enabled() instead of duplicating permission arrays.
  Evidence: Observed App\Support\Access\SuperadminPermissions, database/seeders/SuperadminRoleSeeder.php, DatabaseSeeder::run(), and passing SuperadminRoleTest.
  Added: 2026-05-27T16:29:14+00:00

- Locale switching is centralized in App\Services\LocaleManager; it validates active languages, stores the selected locale in session key locale, saves authenticated users to users.preferred_locale, and SetLocale applies it before rendering.
  Evidence: Implemented app/Services/LocaleManager.php, app/Http/Middleware/SetLocale.php, routes/web.php locale.switch, database migration for users.preferred_locale, and passing SystemLocalizationTest/full php artisan test on 2026-05-27.
  Added: 2026-05-27T17:00:56+00:00

- CRM dictionary labels use name_translations through HasTranslatedDictionaryName; display names fall back to name and then code or slug.
  Evidence: App\Models\LeadStatus, LeadSource, LeadLostReason, LeadTag use HasTranslatedDictionaryName; CrmLocalizationTest verifies translated source/status labels render in CRM screens.
  Added: 2026-05-27T17:23:12+00:00

- CRM Block 2 admin write operations use dedicated Actions plus Form Requests with tkey validation messages; verify with DrivingSchoolPlatformTest, CrmLocalizationTest, and SuperadminRoleTest before the full suite.
  Evidence: Implemented CRM lead create/edit, task, communication, duplicate/lost/status, export requests/actions; php artisan test passed with 55 tests and 479 assertions.
  Added: 2026-05-27T18:38:55+00:00

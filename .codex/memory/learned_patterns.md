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

- CRM Lead compatibility model maps to marketing_leads; lead-owned hasMany and tag pivot relations must pin marketing_lead_id explicitly so Lead does not make Eloquent guess lead_id.
  Evidence: CrmLeadDatabaseFoundationTest and DrivingSchoolPlatformTest failed with guessed lead_id until MarketingLead relations specified marketing_lead_id; full php artisan test then passed.
  Added: 2026-05-27T18:59:21+00:00

- Public website lead forms create CRM records in marketing_leads through CreateWebsiteLeadAction or CreateCallbackLeadAction; contact forms prefer the lead_sources code contact_form when available, and website lead marketing fields can be shown with either website.view_marketing or crm.leads.view_marketing.
  Evidence: Implemented ResolveLeadSourceAction, ResolveLeadNotificationRecipientsAction, CRM task title crm.tasks.defaults.contact_new_website_lead, WebsiteLeadListScreen permission handling; full php artisan test passed with 95 tests and 2523 assertions.
  Added: 2026-05-27T21:22:58+00:00

- Public SEO uses GenerateSitemapAction and GenerateRobotsTxtAction; sitemap entries use website.* public routes and exclude inactive, hidden, or is_indexable=false site_pages, training_programs, and branches.
  Evidence: Implemented SEO/sitemap/robots/public visibility rules and full php artisan test passed with 100 tests and 2562 assertions.
  Added: 2026-05-27T21:40:30+00:00

- CRM lead filtering is centralized in App\Actions\FilterLeadsAction; LeadListScreen and GetLeadPipelineAction should reuse it for consistent search, filters, and quick segments.
  Evidence: Implemented advanced CRM filters and pipeline grouping; php artisan test passed with 146 tests and 7298 assertions.
  Added: 2026-05-28T02:02:20+00:00

- Codex prompt context includes compact repository skill inventory on every prompt; use the local orchid-platform skill and mirrored docs before Orchid admin implementation.
  Evidence: Updated .codex/hooks/user_prompt_context.py, verified dummy UserPromptSubmit output includes orchid-platform, and repository skill discovery passes with two valid skills.
  Added: 2026-05-28T08:20:34+00:00

- Analytics validation Rules should stay schema-flexible across the Block 12 legacy foundation and additive analytics tables, use Eloquent/Schema guards for optional modules, and return only seeded analytics.validation translation messages.
  Evidence: Added ActiveReportDefinitionRule, ActiveKpiMetricRule, report/KPI/dashboard/cache/module Rules, AnalyticsTranslationSeeder keys, docs, and AnalyticsValidationRulesTest.
  Added: 2026-05-28T20:00:00+03:00

- Analytics Form Requests should centralize shared field labels, filter rules, and permission checks in a request concern, then keep dashboard, report, KPI, cache, and preference requests thin around module-specific data helpers.
  Evidence: Added UsesAnalyticsRequestValidation, concrete analytics request classes, translation attributes, docs, and AnalyticsFormRequestsTest.
  Added: 2026-05-28T20:30:00+03:00

- Block 13 account lifecycle uses user_statuses plus User::status(), User::isBlocked(), and User::isArchived(); active is the default seeded status and blocked or archived statuses count as local lockout states.
  Evidence: Added UserStatus model, UserStatusSeeder, ChangeUserStatusAction, UserStatusCanBeChangedRule, and SecurityUserStatusesStaffProfilesTest.
  Added: 2026-05-28T22:00:00+03:00

- Local staff identity extends Orchid users through one-to-one staff_profiles records instead of changing the core Orchid user contract or adding tenant/company account logic.
  Evidence: Added StaffProfile model, StaffProfileFactory, StaffProfileDemoSeeder, CreateStaffProfileAction, UpdateStaffProfileAction, and docs/users.md.
  Added: 2026-05-28T22:00:00+03:00

- Permission metadata should wrap existing Orchid permission strings instead of replacing Orchid access checks; sync reads PlatformProvider and SuperadminPermissions, creates missing registry records, and leaves custom records intact.
  Evidence: Added PermissionGroup, PermissionRegistryItem, ImportExistingOrchidPermissionsAction, SyncPermissionRegistryAction, PermissionRegistrySeeder, and SecurityPermissionRegistryTest.
  Added: 2026-05-28T23:00:00+03:00

- Authentication tracking should use fail-safe listeners and Actions: login attempts are sanitized, security sessions store only HMAC session hashes, and activity touches are throttled so auth flow is never blocked by tracking failures.
  Evidence: Added RecordSuccessfulLoginAction, RecordFailedLoginAction, RecordUserLoginSessionAction, TouchUserSecuritySessionAction, auth event listeners, and SecurityLoginAttemptsSessionsTest.
  Added: 2026-05-28T23:30:00+03:00

- User lifecycle write flows should stay backend-only until screens are requested: create/update/block/unblock/archive/status/profile/preference/password-change actions use translated rules, protect the last Superadmin, prevent accidental self-lockout, revoke tracked sessions on block/archive, and keep profile changes separate from roles and permissions.
  Evidence: Added Step 9 lifecycle Actions, Requests, Rules, factory states, docs, and SecurityUserManagementLifecycleTest.
  Added: 2026-05-30T00:00:00+03:00

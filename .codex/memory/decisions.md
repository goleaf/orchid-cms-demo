# Decisions

- The product is for a local driving school, not a SaaS system.
- Multilingual support should be treated as a global foundation, not as an afterthought.

- Public website foundation reuses training_programs as courses, marketing_leads as CRM leads, and student_reviews as testimonials; do not create duplicate courses, leads, website_leads, or testimonials tables for this local driving-school product.
  Evidence: Implemented database foundation migrations, compatibility models, factories, seeders, and PublicWebsiteDatabaseFoundationTest; php artisan test passed.
  Added: 2026-05-27T18:35:06+00:00

- Block 3 student foundation reuses student_profiles as students and enrollments as student_enrollments through Student and StudentEnrollment compatibility models; do not add duplicate students or student_enrollments tables.
  Evidence: Implemented additive Block 3 migration, compatibility models, factories, seeders, and StudentDatabaseFoundationTest; php artisan test passed with 184 tests and 7767 assertions.
  Added: 2026-05-28T03:25:47+00:00

- Block 10 exam foundation uses exam_admissions, exam_admission_checklist_items, exam_sessions, exam_attempts, and exam_activities linked to existing students, enrollments, groups, lessons, documents, and payments; official exams stay as manual placeholders without government API sync.
  Evidence: Implemented additive exam foundation models, actions, requests, rules, factories, seeders, Orchid session list, documentation, and ExamBlockFoundationTest.
  Added: 2026-05-28T09:16:37+00:00

- Block 10 normalized exam database models add exam type, session status, attempt status, result status, admission rule, participant, result, retake, checklist item, and activity links without adding SaaS or government sync behavior.
  Evidence: Added additive exam database migration, model relationships, factories, dictionary seeder, docs, and ExamDatabaseModelsTest.
  Added: 2026-05-28T13:00:00+00:00

- Block 10 exam workflow writes should go through dedicated Actions and translated FormRequest/Rule validation; readiness checks combine documents, payments, hours, internal exam requirements, session capacity, attempt lifecycle, result scoring, retakes, and activity history without official registry sync.
  Evidence: Added ExamWorkflowService-backed Actions, exam FormRequests, custom validation Rules, translations, docs, and ExamActionsValidationTest.
  Added: 2026-05-28T13:30:00+00:00

- Block 10 exam seed data uses idempotent factory-backed dictionary seeders for default internal exams, official placeholder exams, statuses, result statuses, admission rules, and multilingual translations; demo exam data stays local/demo/testing only.
  Evidence: Added ExamTypeSeeder, ExamStatusSeeder, ExamAttemptStatusSeeder, ExamResultStatusSeeder, ExamAdmissionRuleSeeder, DemoExamSeeder, docs, and ExamFactoriesSeedersTest.
  Added: 2026-05-28T16:30:00+03:00

- Block 10 exam UI and access labels use seeded Russian, English, Lithuanian, and Polish translation keys with granular local permissions for sessions, admissions, attempts, results, retakes, dictionaries, and exports while preserving broad compatibility permissions.
  Evidence: Added exam translation keys, granular superadmin permissions, Orchid permission labels, docs, and ExamLocalizationPermissionsTest.
  Added: 2026-05-28T17:10:00+03:00

- Block 10 exam Orchid UI uses thin local-driving-school screens for sessions, admissions, attempts, results, retakes, and dictionaries; screen writes call exam Actions, labels use translation keys, and access is checked with granular exam permissions instead of broad platform access.
  Evidence: Added exam screens, menu entries, routes, docs, and ExamOrchidScreensTest.
  Added: 2026-05-28T20:30:00+03:00

- Block 10 exam admissions use a structured checklist result for documents, payments, theory hours, practice hours, internal prerequisites, enrollment status, student status, and manual review; session participants are admitted or blocked from that same result without adding global enforcement outside exams.
  Evidence: Added admission checklist storage fields, structured admission checks, manual approval/block behavior, session rechecks, docs, and ExamAdmissionChecklistTest.
  Added: 2026-05-28T21:30:00+03:00

- Block 11 communications foundation uses notification channels, communication templates, reminders, delivery logs, user notification preferences, and student communication history while CRM lead history continues to reuse existing lead communication records.
  Evidence: Implemented foundation schema, Actions, Form Requests, validation Rules, factories, seeders, Orchid communication pages, notification placeholders, documentation, and CommunicationModuleFoundationTest.
  Added: 2026-05-28T12:31:35+03:00

- Block 11 normalized notification database models add notification templates, template versions, variables, messages, recipients, deliveries, preferences, reminder rules, reminder schedules, communication threads, communication messages, attachments, and activity records while reusing users, student_profiles, marketing_leads, and notification_channels.
  Evidence: Added additive notification database schema, model relationships, factories, docs, and NotificationDatabaseModelsTest.
  Added: 2026-05-28T14:20:00+03:00

- Block 11 notification workflows use dedicated Actions, notification FormRequests, and translated custom Rules for template rendering, message scheduling, internal/email sending, placeholder external queues, retries, reminders, communication history, and preferences; SMS, WhatsApp, and Telegram remain placeholders until a provider is selected.
  Evidence: Added notification Actions, request validation, rules, translations, docs, and NotificationActionsRulesTest.
  Added: 2026-05-28T18:30:00+03:00

- Block 11 notification setup data uses idempotent factory-backed seeders for default channels, message templates, template variables, reminder rules, translations, and local demo records; external SMS, WhatsApp, Telegram, and push channels remain placeholders only.
  Evidence: Added notification factory states, default seeders, translation seeding, docs, and NotificationFactoriesSeedersTest.
  Added: 2026-05-28T21:00:00+03:00

- Block 11 notification UI and access labels use seeded Russian, English, Lithuanian, and Polish translation keys with granular local permissions for messages, templates, reminders, deliveries, threads, preferences, channels, and exports.
  Evidence: Added notification UI, field, action, validation, enum, and permission translation keys; registered notification permissions; updated docs; and added NotificationLocalizationPermissionsTest.
  Added: 2026-05-28T21:45:00+03:00

- Block 12 analytics foundation is local-driving-school reporting only: dashboard widgets, report definitions, report runs, report exports, KPI metrics, targets, snapshots, analytics cache, and user dashboard preferences are kept without tenant, subscription, reseller, platform-owner, or multi-company dimensions.
  Evidence: Implemented analytics foundation schema, models, Actions, Form Requests, validation Rules, factories, seeders, Orchid owner dashboard, documentation, and AnalyticsBlockFoundationTest.
  Added: 2026-05-28T13:00:00+03:00

- Block 12 dashboard definitions use analytics_dashboards linked to dashboard_widgets and user_dashboard_preferences for local role audiences only; dashboard data must not gain SaaS tenant, subscription, reseller, platform-owner, or multi-company dimensions.
  Evidence: Added additive dashboard schema, AnalyticsDashboard model, widget/preference relationships, factories, demo seed data, documentation, and AnalyticsDashboardDataModelTest.
  Added: 2026-05-28T14:00:00+03:00

- Block 12 report definitions, runs, and exports use local analytics tables with report groups, data sources, schema metadata, permission metadata, run users, export metadata, and soft-deletable definitions; they must not gain SaaS tenant, subscription, reseller, platform-owner, or multi-company dimensions.
  Evidence: Added additive report schema, ReportGroup enum, model relationships, factories, Actions compatibility updates, documentation, and AnalyticsReportDataModelTest.
  Added: 2026-05-28T16:00:00+03:00

- Block 12 KPI metrics, targets, and snapshots use local analytics tables with metric groups, units, period ranges, thresholds, optional branch/user scope, translated metric labels, and snapshot metadata; they must not gain SaaS tenant, subscription, reseller, platform-owner, or multi-company dimensions.
  Evidence: Added additive KPI schema, KPI enums, model relationships, factories, docs, and AnalyticsKpiDataModelTest.
  Added: 2026-05-28T17:00:00+03:00

- Block 12 analytics snapshots and cache entries store local owner dashboard and operational summary payloads with period ranges, optional branch/user scope, cache tags, and expiration handling; they must not store external telemetry or SaaS tenant, subscription, reseller, platform-owner, or multi-company dimensions.
  Evidence: Added analytics snapshot/cache-entry schema, models, factories, Actions, cache key validation, docs, and AnalyticsSnapshotsCacheTest.
  Added: 2026-05-28T19:00:00+03:00

- Block 12 core analytics Actions calculate dashboard summaries, widget payloads, report runs, export records, KPI values, KPI snapshots, cache refreshes, date ranges, and filters through local Eloquent models while guarding optional module tables.
  Evidence: Added table-safe analytics Actions, export payload records, KPI comparison/snapshot calculation, docs, and AnalyticsCoreActionsTest.
  Added: 2026-05-28T19:30:00+03:00

- Block 12 analytics validation Rules enforce active reports, active KPI metrics, report filters and columns, export formats, KPI periods and targets, widget configuration, date ranges, cache keys, permissions, and optional module availability through translated analytics validation messages only.
  Evidence: Added analytics validation Rules, translation keys, docs, and AnalyticsValidationRulesTest.
  Added: 2026-05-28T20:00:00+03:00

- Block 12 analytics Form Requests validate dashboard setup, widget setup, report runs, report exports, KPI targets, cache refreshes, and dashboard preferences with local Orchid permissions, analytics Rules, translated attributes, and no SaaS scope fields.
  Evidence: Added analytics request classes, shared request validation helpers, translation attributes, docs, and AnalyticsFormRequestsTest.
  Added: 2026-05-28T20:30:00+03:00

- Block 13 user statuses and staff profiles are local security extensions: user_statuses controls lifecycle state and staff_profiles stores one-to-one school employee profile data without tenants, subscriptions, platform owners, or multi-company isolation.
  Evidence: Added user status and staff profile migration, models, Actions, Form Requests, Rules, factories, seeders, translations, docs, and SecurityUserStatusesStaffProfilesTest.
  Added: 2026-05-28T22:00:00+03:00

- Block 13 permission registry documents existing Orchid permissions with local groups, translations, modules, risk levels, sync, and audit metadata; Orchid remains the actual authorization mechanism.
  Evidence: Added permission registry migration, models, Actions, Form Requests, Rules, factories, seeders, translations, docs, and SecurityPermissionRegistryTest.
  Added: 2026-05-28T23:00:00+03:00

- Block 13 login attempt and user security session tracking is local security evidence around Laravel/Orchid auth; it records attempts, failed-login thresholds, hashed security sessions, logout/revocation/pruning, audit logs, and security events without storing raw session IDs or adding external identity services.
  Evidence: Added login/session migration, UserSecuritySession model, login/session Actions, auth listeners, pruning commands, translations, docs, and SecurityLoginAttemptsSessionsTest.
  Added: 2026-05-28T23:30:00+03:00

- Block 13 user management lifecycle stays on the existing Orchid users table and adds backend-only Actions, Requests, Rules, translations, permissions, factories, audit records, security events, and documentation for internal staff account create/update/block/unblock/archive/status/profile/preference/password-change flows.
  Evidence: Added user lifecycle actions, validation rules, form requests, factory states, docs, and SecurityUserManagementLifecycleTest.
  Added: 2026-05-30T00:00:00+03:00

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

- Block 10 exam seed data uses idempotent factory-backed dictionary seeders for default internal exams, official placeholder exams, statuses, result statuses, admission rules, and multilingual translations; demo exam data stays local/demo/testing only.
  Evidence: Added ExamTypeSeeder, ExamStatusSeeder, ExamAttemptStatusSeeder, ExamResultStatusSeeder, ExamAdmissionRuleSeeder, DemoExamSeeder, docs, and ExamFactoriesSeedersTest.
  Added: 2026-05-28T16:30:00+03:00

- Block 11 communications foundation uses notification channels, communication templates, reminders, delivery logs, user notification preferences, and student communication history while CRM lead history continues to reuse existing lead communication records.
  Evidence: Implemented foundation schema, Actions, Form Requests, validation Rules, factories, seeders, Orchid communication pages, notification placeholders, documentation, and CommunicationModuleFoundationTest.
  Added: 2026-05-28T12:31:35+03:00

- Block 12 analytics foundation is local-driving-school reporting only: dashboard widgets, report definitions, report runs, report exports, KPI metrics, targets, snapshots, analytics cache, and user dashboard preferences are kept without tenant, subscription, reseller, platform-owner, or multi-company dimensions.
  Evidence: Implemented analytics foundation schema, models, Actions, Form Requests, validation Rules, factories, seeders, Orchid owner dashboard, documentation, and AnalyticsBlockFoundationTest.
  Added: 2026-05-28T13:00:00+03:00

- Block 12 dashboard definitions use analytics_dashboards linked to dashboard_widgets and user_dashboard_preferences for local role audiences only; dashboard data must not gain SaaS tenant, subscription, reseller, platform-owner, or multi-company dimensions.
  Evidence: Added additive dashboard schema, AnalyticsDashboard model, widget/preference relationships, factories, demo seed data, documentation, and AnalyticsDashboardDataModelTest.
  Added: 2026-05-28T14:00:00+03:00

- Block 12 KPI metrics, targets, and snapshots use local analytics tables with metric groups, units, period ranges, thresholds, optional branch/user scope, translated metric labels, and snapshot metadata; they must not gain SaaS tenant, subscription, reseller, platform-owner, or multi-company dimensions.
  Evidence: Added additive KPI schema, KPI enums, model relationships, factories, docs, and AnalyticsKpiDataModelTest.
  Added: 2026-05-28T17:00:00+03:00

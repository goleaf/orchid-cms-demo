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

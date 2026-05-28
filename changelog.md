# Changelog

## 2026-05-28

- Analytics now has local snapshot storage and action-based cache entries for owner dashboards, summaries, expiration handling, and tag-based clearing

- Added multilingual exam labels and more precise local exam access controls for sessions, admissions, attempts, results, retakes, dictionaries, and exports

- KPI setup now has a clearer local data model for metric definitions, targets, snapshots, translated labels, period ranges, thresholds, and branch or staff ownership

- Added reusable exam sample builders and repeatable exam setup data for default types, statuses, admission rules, translations, and local demo records

- Added a dashboard and widget data model for local analytics, including translated labels, default owner dashboard setup, user layout preferences, and automated checks
- Added a stronger exam management foundation with exam types, statuses, admission rules, participants, results, retakes, and checklist tracking
- Added an owner analytics dashboard with key school metrics, report summaries, recent report runs, and performance snapshots
- Improved seeded website pages with clearer multilingual content, search descriptions, sharing details, and legal page coverage
- Expanded project documentation for analytics, exams, communications, and notifications
- Added broader automated coverage for analytics, exam records, education groups, public website setup, and communication flows

- Added normalized exam dictionaries, admission rules, participants, results, retakes, checklist items, and activity links for the local exam workflow
- Expanded exam verification so translated labels, relationships, scopes, and seed data are checked automatically

- Added the local owner dashboard foundation with report setup, report run tracking, export records, KPI metrics, targets, snapshots, analytics cache records, and personal dashboard preferences
- Added analytics permissions, translated admin labels, validation rules, sample setup data, and automated checks for the new reporting foundation

- Added the communication module foundation for internal notifications, reusable messages, reminders, delivery history, user preferences, student contact history, CRM lead contact history, and future messaging placeholders
- Added the first analytics tools for tracking school performance, reports, targets, and dashboard preferences
- Added communication management pages for channels, message templates, reminders, and delivery history
- Improved validation around training groups, schedules, learning programs, and student group membership
- Expanded demo data and translations so analytics and communication areas are easier to try in a fresh setup
- Added tests covering the communication foundation and updated education group behavior

- Group capacity recalculation now uses the latest saved group data before updating totals
- The sample Category B training program now uses clearer Vilnius-focused wording
- Internal workflow notes were updated for future project learning review

- Added stronger validation for group publishing, archiving, status changes, student transfers, waitlists, and schedule pattern changes
- Added permission checks around group and membership operations so only authorized staff can perform them
- Added multilingual labels and messages for communication channels, templates, reminders, delivery logs, and related permissions
- Updated internal project learning notes for future maintenance context

- Added clearer changelog coverage for the newest module guides and recent project history
- Clarified the operations foundation for branches, instructors, vehicles, lesson schedules, public instructor pages, and public vehicle pages
- Clarified the document foundation for student documents, lead attachments, onboarding placeholders, exam readiness checks, and future review workflows
- Clarified the payment foundation for student payments, enrollment links, payment placeholders, exam readiness checks, and future finance workflows
- Clarified the communication foundation for reminder scheduling, user notification preferences, student contact history, reusable messages, and delivery history
- Clarified the analytics foundation for local dashboard metrics, saved reports, report runs, exports, targets, and future reporting snapshots
- Clarified that public website content now also covers instructor pages, vehicle pages, review pages, and knowledge articles

- Added a communications foundation for reminders, message templates, student contact history, notification channels, and delivery tracking
- Added a dashboard and analytics foundation for key metrics, targets, saved reports, report runs, and exports
- Improved training group management with waitlists, transfers, capacity updates, schedule patterns, status changes, publishing, and archiving
- Expanded project documentation for operations, documents, payments, communications, analytics, and public website planning
- Added test coverage for the new foundation so the main workflows can be checked automatically

- Backfilled the changelog from the project history so earlier website, CRM, student, education, operations, and automation foundations are represented
- Added missing module guides for operations, communications, documents, payments, and dashboard reporting
- Updated the documentation index and website notes so current guides match the committed project surface

- Added the student and enrollment management foundation using the existing student profile and enrollment records
- Added student and enrollment statuses, onboarding tasks, student activity history, manager assignment, filtering, and export support
- Added a lead-to-student conversion flow that can create or link students, create enrollments, assign groups, and preserve CRM history
- Added student administration pages for student lists, student cards, enrollments, tasks, and status dictionaries
- Added stronger student validation, dictionary protection, translated labels, sample records, and focused verification coverage

- Expanded CRM management with a clearer pipeline, lead filters, task handling, call logging, CSV exports, and translated admin labels
- Added CRM dictionary management for statuses, sources, lost reasons, and tags with safer deletion rules
- Added message template management and stronger lead validation for contact details, duplicate handling, status changes, and marketing data access
- Added CRM documentation and verification coverage for factories, seeders, translations, admin pages, pipeline behavior, and exports

- Added public website management for pages, course offers, pricing, branches, public groups, FAQ, testimonials, SEO settings, and lead intake
- Added public pages for instructors, vehicles, reviews, and knowledge articles using prepared page data
- Connected public website forms to the CRM intake flow with tracking, follow-up records, and translated validation
- Added multilingual public content foundations for courses, branches, reviews, FAQ, pricing packages, and site settings

- Added the first local driving-school operating system foundation with branches, instructors, vehicles, lessons, enrollments, payments, documents, exams, campaigns, reviews, and knowledge articles
- Added the first admin pages for operations, CRM, students, schedule, fleet, documents, payments, exams, and the dashboard
- Added the multilingual foundation for languages, translations, locale switching, user locale preference, and translation management
- Added the local superadmin permission foundation for the driving-school admin role
- Added local project automation for changelog updates, learning notes, commit preparation, and repository skill discovery

- Added a clear exam planning guide covering admissions, sessions, results, retakes, and activity history
- Clarified that exam tracking is for one local driving school and does not include external registry integration or online testing
- Updated the project notes used for future learning review

- Added the foundation for managing exam admissions, exam sessions, attempts, results, and retakes
- Added clearer exam permissions and translated admin labels for exam workflows
- Updated the exam admin page to show scheduled sessions with program, group, instructor, seat, status, and location details
- Prepared communication and reminder records for future student notifications and follow-ups
- Improved seeded homepage, branch, and training group data so public pages show more complete multilingual demo content

- Added the foundation for managing exam readiness, exam sessions, attempts, results, and retakes
- Connected exam readiness to student enrollments, training groups, driving lessons, documents, payments, and activity history
- Added multilingual exam labels, validation messages, permissions, sample records, and documentation for the new foundation
- Improved student group membership tracking, including transfers, waiting lists, completed memberships, and capacity counts
- Improved website lead listings so source and form names display as friendly translated labels
- Expanded sample data and tests for training programs, groups, translations, leads, and exam-related workflows
- Improved the automated project workflow with stronger validation and cleaner memory handling

- Improved project automation hooks so memory context, tool evidence, learning candidates, changelog updates, commits, and pushes are wired through the same local workflow
- Added safer hook validation before automated commits and made generated hook-memory files less likely to pollute future task history
- Added local controls for disabling self-learning hooks or skipping only the automatic push step

- Improved sample course and pricing content so multilingual test data is more consistent
- Updated training package examples to use clearer localized names and features

- Added the education structure needed to connect learning programs, modules, topics, and training groups
- Improved group assignment foundations so capacity, memberships, enrollment records, and activity history can stay aligned
- Expanded multilingual website and admin seed content for Lithuanian and Polish operators
- Updated project documentation to reflect the completed student conversion and training group foundations

- FAQ items can now be moved up or down from the website administration list
- New FAQ items are placed at the end of the list automatically
- The manual position number was removed from FAQ editing to reduce ordering mistakes
- FAQ ordering now handles first and last items safely
- Project documentation now reflects the current student conversion, group membership, and website management flow

- Improved project ignore rules for local environment files, generated database files, and runtime cache folders

- Website group managers can now start creating a new group directly from the group list
- Added checks to confirm a new public website group can be created successfully from the admin area

- Added the foundation for managing training groups, memberships, schedule patterns, and education activity history
- Improved group enrollment rules so students are only added when the selected group can accept them
- Expanded multilingual labels for student, education, CRM, and public website workflows
- Added project documentation for the driving school operating system, training groups, local Orchid guidance, and automation workflow
- Improved automated changelog and commit preparation for internal project workflow

## 2026-05-27

- Created the initial application and admin-panel foundation for a local driving-school operating system
- Added early public website pages for home, contacts, course selection, instructors, vehicles, reviews, knowledge articles, and lead forms
- Added the first CRM lead intake flow for public enrollment requests, contact requests, lead comments, communications, tasks, and status history
- Added the first sales pipeline workflow with safer status handling and clearer lead titles
- Added early lead administration improvements for extra lead fields, task creation, filters, and display columns
- Added the first project self-learning and local automation setup for repeatable repository work
- Added the first public website, CRM, and project notes that later module guides build on

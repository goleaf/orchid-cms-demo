# Public Website Foundation

Project baseline: follow [`docs/project-specs.md`](project-specs.md) and [`AGENTS.md`](../AGENTS.md). This block is Laravel + Orchid + Blade, uses Eloquent only, and keeps all visible admin/public text translatable.

This block adds the public website layer for one local driving school. It is not a SaaS module and does not introduce tenants, subscriptions, resellers, platform-owner dashboards, or multi-company isolation.

## Public Routes

- `website.home`: homepage with editable public content, featured courses, open groups, pricing preview, testimonials, contacts, and lead form.
- `website.courses.index` / `website.courses.show`: public course index and course detail pages by course slug.
- `website.pricing`: public pricing page with course prices, pricing packages, and application form.
- `website.branches.index` / `website.branches.show`: public branch index and branch detail pages by branch slug.
- `website.contacts`: contact page with public settings, branch cards, callback form, and contact form.
- `website.thank_you`: shared thank-you page after public lead submission.
- `website.leads.store`: public course application form endpoint.
- `website.callback.store`: public callback form endpoint.
- `website.contact.store`: public contact form endpoint.
- `website.language.switch`: public locale switch endpoint.
- `site.home`: homepage with editable landing content, courses, upcoming groups, branches, and lead form.
- `site.courses.show` / `site.categories.show`: public course detail pages by course slug.
- `site.prices`: public pricing table for active courses.
- `site.branches.show`: public branch detail pages by branch slug.
- `site.contacts`: branch contact cards and callback form.
- `site.apply` / `site.apply.store`: website lead form connected to CRM lead intake.
- `site.callback.store`: callback form connected to CRM lead intake.
- `site.thanks`: shared thank-you page after public lead submission.
- `site.sitemap` / `site.robots`: discovery endpoints for public pages.

The `site.*` names are kept as compatibility aliases where the new `website.*` route names own the same public URL.

## Admin Management

Orchid exposes website management under `platform.website.*` routes:

- `platform.website.settings`: homepage content.
- `platform.website.courses`: public course catalog and SEO.
- `platform.website.pricing`: public pricing packages and tariff display.
- `platform.website.branches`: branch public content and SEO.
- `platform.website.groups`: publicly visible training groups.
- `platform.website.leads`: CRM lead list for website intake.

Access is controlled by `website.*` permissions and remains compatible with the existing local superadmin role.

## Actions And Requests

Public enrollment and callback submissions go through Form Requests and Actions. Controllers and Orchid screens should stay thin and call Actions for business operations.

Public lead intake actions:

- `CreateWebsiteLeadAction`
- `CreateCallbackLeadAction`
- `CaptureUtmDataAction`
- `StoreUtmInSessionAction`
- `NormalizePhoneAction`
- `ResolveWebsiteCourseContextAction`
- `GetCourseIndexPageAction`
- `GetBranchIndexPageAction`

Public content management actions:

- `CreateOrUpdateSitePageAction`
- `CreateOrUpdateCourseAction`
- `CreateOrUpdateCourseCategoryAction`
- `CreateOrUpdatePricingPackageAction`
- `SavePricingPackageAction`
- `CreateOrUpdateBranchAction`
- `CreateOrUpdateFaqAction`
- `CreateOrUpdateTestimonialAction`
- `UpdateSiteSettingsAction`
- `GenerateSeoMetadataAction`
- publish/hide Actions for pages, courses, and branches.

Validation uses Form Requests plus custom Rules for public catalog visibility, consent, locale availability, required default translations, slug uniqueness, SEO metadata length, prices, and publish readiness.

## Lead Intake

Public enrollment and callback submissions create `MarketingLead` records. The actions also create CRM activity records:

- communication record with `web_form` channel,
- comment record,
- status history entry,
- follow-up task,
- manager notification.

The intake stores UTM fields, landing page, form page, form name, locale, IP address, and user agent. Tracking is captured from public GET requests by `StoreUtmInSessionAction` and merged into forms by `CaptureUtmDataAction`.

## Multilingual Content

Public UI copy uses translation keys through `tkey()`. Public course, branch, group, and homepage content use JSON translation fields managed through Orchid translatable fields.

Seeded translations are created by `SystemTranslationSeeder`; seeded website content is created by `PublicWebsiteSeeder` through model factories.

Public Blade pages share translated form partials in `resources/views/site/partials`. Forms submit only to Action-backed controllers and preserve UTM/session tracking on validation errors.

## Database Foundation

The public website foundation reuses the local driving-school operating tables instead of creating SaaS-style duplicates:

- Public courses use `training_programs`, exposed through `App\Models\Course`.
- CRM website intake uses `marketing_leads`, exposed through `App\Models\Lead`.
- Testimonials use `student_reviews`, exposed through `App\Models\Testimonial`.
- Existing branches and training groups remain `branches` and `training_groups`.

New website-only tables:

- `site_pages` for managed public pages and SEO metadata.
- `course_categories` for grouping public course offers.
- `pricing_packages` for pricing-page tariffs.
- `faqs` for global, page, branch, or course FAQ content.
- `site_settings` for public website settings.

Existing school tables are extended with UUIDs, multilingual website fields, site visibility flags, SEO/media references where practical, audit fields, and soft deletes where the public website needs lifecycle management.

## Pricing Packages

The public pricing page renders both course-level prices from `training_programs` and package-level tariffs from `pricing_packages`. Pricing packages can be attached to a course and/or course category, include multilingual name, description, and feature lists, and are managed in Orchid through `platform.website.pricing`.

## Tests

The public website foundation is covered by `tests/Feature/PublicWebsiteFoundationTest.php`, `tests/Feature/PublicWebsiteFrontendTest.php`, `tests/Feature/PublicWebsiteDatabaseFoundationTest.php`, `tests/Feature/PublicWebsiteActionsRequestsRulesTest.php`, and updated platform coverage in `tests/Feature/DrivingSchoolPlatformTest.php`.

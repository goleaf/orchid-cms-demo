# Public Website Foundation

This block adds the public website layer for one local driving school. It is not a SaaS module and does not introduce tenants, subscriptions, resellers, platform-owner dashboards, or multi-company isolation.

## Public Routes

- `site.home`: homepage with editable landing content, courses, upcoming groups, branches, and lead form.
- `site.courses.show` / `site.categories.show`: public course detail pages by course slug.
- `site.prices`: public pricing table for active courses.
- `site.branches.show`: public branch detail pages by branch slug.
- `site.contacts`: branch contact cards and callback form.
- `site.apply` / `site.apply.store`: website lead form connected to CRM lead intake.
- `site.callback.store`: callback form connected to CRM lead intake.
- `site.thanks`: shared thank-you page after public lead submission.
- `site.sitemap` / `site.robots`: discovery endpoints for public pages.

## Admin Management

Orchid exposes website management under `platform.website.*` routes:

- `platform.website.settings`: homepage content.
- `platform.website.courses`: public course catalog and SEO.
- `platform.website.branches`: branch public content and SEO.
- `platform.website.groups`: publicly visible training groups.
- `platform.website.leads`: CRM lead list for website intake.

Access is controlled by `website.*` permissions and remains compatible with the existing local superadmin role.

## Lead Intake

Public enrollment and callback submissions create `MarketingLead` records. The actions also create CRM activity records:

- communication record with `web_form` channel,
- comment record,
- status history entry,
- follow-up task,
- manager notification.

The intake stores UTM fields, landing page, form page, form name, locale, IP address, and user agent. Tracking is captured from public GET requests and merged into POST submissions through `App\Support\Site\SiteTracking`.

## Multilingual Content

Public UI copy uses translation keys through `tkey()`. Public course, branch, group, and homepage content use JSON translation fields managed through Orchid translatable fields.

Seeded translations are created by `SystemTranslationSeeder`; seeded website content is created by `PublicWebsiteSeeder` through model factories.

## Tests

The public website foundation is covered by `tests/Feature/PublicWebsiteFoundationTest.php` and updated platform coverage in `tests/Feature/DrivingSchoolPlatformTest.php`.

# Public Website

Project baseline: follow [`docs/project-specs.md`](project-specs.md) and [`AGENTS.md`](../AGENTS.md). Public website work is Laravel + Orchid + Blade, uses Eloquent only, and keeps all visible admin/public text translatable.

Block 1 provides the public website foundation for one local driving school. It is not a SaaS module and does not add tenant, reseller, subscription, platform-owner, or multi-company behavior.

## Purpose

The public website lets administrators manage public pages, course offers, prices, branches, public group visibility, FAQ, testimonials, SEO metadata, and site settings from Orchid. Public forms create structured CRM lead records instead of only sending email.

## Models

- `SitePage`: managed public pages, page type, slug, translated content, SEO metadata, publication state, indexability.
- `CourseCategory`: translated course grouping with public visibility and SEO metadata.
- `Course`: public course facade over `training_programs`; reuses the education table without creating a duplicate course table.
- `PricingPackage`: tariff cards for the pricing page, attached to a course or category.
- `Branch`: local office or training location with translated public fields and SEO metadata.
- `TrainingGroup`: existing group model with public visibility, capacity, schedule summary, course, branch, and lead relations.
- `Faq`: global or polymorphic FAQ content for pages, courses, and branches.
- `Testimonial`: public testimonial facade over `student_reviews`.
- `SiteSetting`: key/value public website settings.
- `Lead`: CRM-compatible lead facade over `marketing_leads`.

All public content fields that are visible to users use JSON translation fields and the repository `HasTranslations` behavior where the model supports it.

## Actions

Public form intake:

- `CreateWebsiteLeadAction`
- `CreateCallbackLeadAction`
- `CaptureUtmDataAction`
- `StoreUtmInSessionAction`
- `NormalizePhoneAction`
- `ResolveWebsiteCourseContextAction`
- `ResolveLeadSourceAction`
- `ResolveLeadNotificationRecipientsAction`

Public page data:

- `GetHomePageAction`
- `GetCourseIndexPageAction`
- `GetProgramCategoryPageAction`
- `GetPricingPageAction`
- `GetBranchIndexPageAction`
- `GetBranchPageAction`
- `GetContactPageAction`
- `GetSitePageAction`

Orchid/content management:

- `CreateOrUpdateSitePageAction`
- `CreateOrUpdateCourseAction`
- `CreateOrUpdateCourseCategoryAction`
- `CreateOrUpdatePricingPackageAction`
- `CreateOrUpdateBranchAction`
- `CreateOrUpdateFaqAction`
- `CreateOrUpdateTestimonialAction`
- `UpdateSiteSettingsAction`
- `PublishSitePageAction`
- `UnpublishSitePageAction`
- `PublishCourseOnSiteAction`
- `HideCourseFromSiteAction`
- `PublishBranchOnSiteAction`
- `HideBranchFromSiteAction`
- `ShowTrainingGroupOnSiteAction`
- `HideTrainingGroupFromSiteAction`

SEO:

- `GenerateSeoMetadataAction`
- `UpdateSeoMetadataAction`
- `GenerateSitemapAction`
- `GenerateRobotsTxtAction`

Controllers and Orchid screens are expected to delegate business behavior to these Actions.

## Form Requests

Public forms:

- `StoreWebsiteLeadRequest`
- `StoreCallbackLeadRequest`
- `StoreContactLeadRequest`

Admin content:

- `StoreSitePageRequest` / `UpdateSitePageRequest`
- `StoreCourseCategoryRequest` / `UpdateCourseCategoryRequest`
- `StoreCourseRequest` / `UpdateCourseRequest`
- `StorePricingPackageRequest` / `UpdatePricingPackageRequest`
- `StoreBranchRequest` / `UpdateBranchRequest`
- `StoreFaqRequest` / `UpdateFaqRequest`
- `StoreTestimonialRequest` / `UpdateTestimonialRequest`
- `UpdateSiteSettingsRequest`

## Rules

Custom validation rules use translation keys for messages:

- `PhoneOrEmailRequiredRule`
- `ValidPublicCourseRule`
- `ValidPublicBranchRule`
- `ValidPublicTrainingGroupRule`
- `ConsentAcceptedRule`
- `ValidLocaleRule`
- `TranslatedFieldRequiredRule`
- `ValidSlugRule`
- `SeoMetadataRule`
- `SeoTitleLengthRule`
- `SeoDescriptionLengthRule`
- `ValidCanonicalUrlRule`
- `ValidPriceRule`
- `PublishedPageRequirementRule`
- `PublicCourseCanBePublishedRule`
- `PublicBranchCanBePublishedRule`
- `PublicPageIndexableRule`

## Factories

Factories exist for the public website records:

- `SitePageFactory`
- `CourseCategoryFactory`
- `CourseFactory`
- `PricingPackageFactory`
- `BranchFactory`
- `TrainingGroupFactory`
- `FaqFactory`
- `TestimonialFactory`
- `SiteSettingFactory`
- `LeadFactory`

Seeder and test data should use factories where practical. Stable dictionary-like records can use idempotent `updateOrCreate` with factory-built payloads.

## Seeders

- `WebsiteTranslationSeeder`: delegates website translation key setup.
- `WebsitePageSeeder`: default managed pages.
- `WebsiteCourseSeeder`: demo public categories and courses.
- `WebsitePricingSeeder`: demo pricing packages.
- `WebsiteBranchSeeder`: demo branches.
- `WebsiteTrainingGroupSeeder`: demo public groups.
- `WebsiteFaqSeeder`: common FAQ.
- `WebsiteTestimonialSeeder`: demo testimonials.
- `WebsiteSettingsSeeder`: default site settings.
- `WebsiteDemoSeeder` / `PublicWebsiteSeeder`: wrapper seeders.

Seeders are designed to be idempotent for system pages, settings, and stable demo records.

## Permissions

Website permissions are centralized with the local superadmin permission set:

- `website.view`
- `website.manage_pages`
- `website.manage_courses`
- `website.manage_course_categories`
- `website.manage_pricing`
- `website.manage_branches`
- `website.manage_groups`
- `website.manage_faq`
- `website.manage_testimonials`
- `website.manage_settings`
- `website.view_leads`
- `website.update_leads`
- `website.view_marketing`
- `website.preview`

Marketing attribution fields in the website lead screen require `website.view_marketing` or `crm.leads.view_marketing`.

## Translation Keys

Visible UI copy uses translation keys through `tkey()` or Laravel localization. Website keys are seeded for `ru`, `en`, `lt`, and `pl`.

Primary groups:

- `menu.website.*`
- `website.nav.*`
- `website.actions.*`
- `website.home.*`
- `website.courses.*`
- `website.pricing.*`
- `website.branches.*`
- `website.groups.*`
- `website.forms.*`
- `website.faq.*`
- `website.testimonials.*`
- `website.seo.*`
- `website.admin.*`
- `website.validation.*`
- `permissions.website.*`
- `validation.attributes.website_lead.*`

Approximate seeded content translations should be reviewed by a human before production launch.

## Validation Errors

Custom validation errors use keys such as:

- `website.validation.phone_or_email_required`
- `website.validation.consent_required`
- `website.validation.invalid_public_course`
- `website.validation.invalid_public_branch`
- `website.validation.invalid_public_group`
- `website.validation.group_is_full`
- `website.validation.default_translation_required`
- `website.validation.invalid_slug`
- `website.validation.slug_already_exists`
- `website.validation.invalid_price`
- `website.validation.invalid_locale`
- `website.validation.page_cannot_be_published`
- `website.validation.course_cannot_be_published`
- `website.validation.branch_cannot_be_published`
- `website.validation.seo_title_too_long`
- `website.validation.seo_description_too_long`
- `website.validation.invalid_canonical_url`
- `website.validation.public_page_not_indexable`

Public form validation also defines translated validation attributes for lead, course, branch, group, and page fields.

## UTM Tracking

`CaptureSiteTracking` runs on public routes and calls `StoreUtmInSessionAction` to preserve first-touch values:

- `utm_source`
- `utm_medium`
- `utm_campaign`
- `utm_content`
- `utm_term`
- `referrer`
- `landing_page`
- current page/form page

`CaptureUtmDataAction` merges request query parameters, hidden form fields, and session values when a form is submitted. Leads store landing page, form page, locale, IP address, and user agent.

## CRM Lead Integration

Website application, callback, and contact forms create `MarketingLead` records through Action classes. Intake behavior:

- validates Form Request data,
- normalizes phone,
- resolves course, branch, and training group context,
- captures UTM and request metadata,
- saves consent and consent timestamp,
- maps source and form name,
- sets status to `new`,
- marks duplicates when CRM duplicate logic finds a match,
- records CRM activity/comment/communication where supported,
- creates the first follow-up task where CRM tasks exist,
- notifies internal recipients where the notification system is available,
- redirects users to the translated thank-you page.

## SEO

Public records support translated SEO and Open Graph metadata:

- SEO title and description,
- Open Graph title and description,
- Open Graph image,
- canonical URL,
- index/noindex flag,
- clean slug.

`GenerateSeoMetadataAction` creates simple fallback metadata from translated title/name/description content. It does not use AI.

## Sitemap And Robots

Routes:

- `site.sitemap`: `GET /sitemap.xml`
- `site.robots`: `GET /robots.txt`

The sitemap includes active, visible, indexable public pages, courses, branches, pricing, contacts, and homepage URLs. It excludes inactive, hidden, and noindex records.

Robots output allows public pages, disallows admin/platform paths, and includes the sitemap URL. `robots_txt` in site settings can override the default text.

## Public Visibility

Public controllers query only active and visible-on-site content, with open group capacity/status rules where group enrollment is shown. Hidden courses, branches, groups, and noindex records are not included in public discovery output.

## Tests

Primary coverage:

- `PublicWebsiteFoundationTest`
- `PublicWebsiteDatabaseFoundationTest`
- `PublicWebsiteFactoriesSeedersTest`
- `PublicWebsiteActionsRequestsRulesTest`
- `PublicWebsiteTranslationsTest`
- `PublicWebsiteOrchidAdminTest`
- `PublicWebsiteFrontendTest`
- `PublicWebsiteSeoTest`

Run focused website checks with:

```bash
php artisan test --filter=PublicWebsite
```

Run the full suite with:

```bash
php artisan test
```

## Known TODOs

- Human review is needed for approximate seeded `ru`, `en`, `lt`, and `pl` website copy.
- Full CRM pipeline, payments, invoices, lesson scheduling, documents, exams, payroll, and external messaging integrations are intentionally outside Block 1.

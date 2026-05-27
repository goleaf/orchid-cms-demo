# Decisions

- The product is for a local driving school, not a SaaS system.
- Multilingual support should be treated as a global foundation, not as an afterthought.

- Public website foundation reuses training_programs as courses, marketing_leads as CRM leads, and student_reviews as testimonials; do not create duplicate courses, leads, website_leads, or testimonials tables for this local driving-school product.
  Evidence: Implemented database foundation migrations, compatibility models, factories, seeders, and PublicWebsiteDatabaseFoundationTest; php artisan test passed.
  Added: 2026-05-27T18:35:06+00:00

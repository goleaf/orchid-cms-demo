# Tool Notes

Add useful local commands after inspecting the repository, for example:

- Laravel tests: `php artisan test`
- Targeted Laravel test: `php artisan test --filter=Name`

Do not assume these commands until the repository confirms them.

- Run the full Laravel test suite with php artisan test; targeted feature checks can use php artisan test --filter=DrivingSchoolPlatformTest or --filter=SystemLocalizationTest.
  Evidence: Commands passed after CRM message-template changes: php artisan test, php artisan test --filter=DrivingSchoolPlatformTest, php artisan test --filter=SystemLocalizationTest.
  Added: 2026-05-27T16:29:14+00:00

## Repository skill discovery

- Last scan: 2026-05-27T16:45:51Z
- Skills found: 1; valid: 1.
- Discovered skills: `codebase-self-learning` (.agents/skills/codebase-self-learning).
- Recommended fixes: none.
- Manual command: `python3 .agents/skills/codebase-self-learning/scripts/discover_skills.py --json`.

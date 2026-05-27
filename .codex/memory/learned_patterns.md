# Learned Patterns

Add stable codebase conventions here after they are proven by code or confirmed by the user.

- Superadmin platform access is centralized in App\Support\Access\SuperadminPermissions and applied by SuperadminRoleSeeder; seeders should reuse SuperadminPermissions::enabled() instead of duplicating permission arrays.
  Evidence: Observed App\Support\Access\SuperadminPermissions, database/seeders/SuperadminRoleSeeder.php, DatabaseSeeder::run(), and passing SuperadminRoleTest.
  Added: 2026-05-27T16:29:14+00:00

# TODO: Remove Student Role — Registrar-Only System

## Steps
- [x] 1. Update `config/config.php` — remove `isStudent()`, simplify `isRegistrar()` and `requireRegistrar()`.
- [x] 2. Update `pages/login.php` — remove role-switcher tabs, hidden role input, and `switchRole()` JS; always log in as registrar.
- [x] 3. Update `includes/navbar.php` — always show "Registrar Staff".
- [x] 4. Update `includes/sidebar.php` — remove `if (!isStudent())` conditionals so all menu items always show.
- [x] 5. Update `pages/settings.php` — remove `isStudent()` references, always show Portal Configuration.
- [x] 6. Verify all modified files pass PHP lint (no syntax errors).

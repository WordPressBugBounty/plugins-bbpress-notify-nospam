# Changelog

## 3.0.0 - Refactor & Autoload (unreleased)

- Moved main plugin class into `includes/controller/class-bbpress-notify-nospam.php`.
- Added `includes/autoload.php` SPL autoloader to support new `class-*.php` files and legacy `*.class.php` files.
- Replaced root plugin file with a minimal loader exposing `bbpnns()` and bootstrapping the v3 class.
- Migrated controllers to `includes/controller/class-*.php` (incremental migration to v3 architecture).
- Archived original legacy controller files to `includes/legacy/controller/` for reference.
- Centralized settings access via `bbpnns()->get_settings()`.
- Added PHPUnit tests and PHPCS ruleset; test suite runs in the plugin-local environment.

# Changelog

## 3.0.0 - Refactor & Autoload (unreleased)

- Moved main plugin class into `includes/controller/class-bbpress-notify-nospam.php`.
- Added `includes/autoload.php` SPL autoloader to support new `class-*.php` files and legacy `*.class.php` files.
- Replaced root plugin file with a minimal loader exposing `bbpnns()` and bootstrapping the v3 class.
- Migrated controllers to `includes/controller/class-*.php` (incremental migration to v3 architecture).
- Archived original legacy controller files to `includes/legacy/controller/` for reference.
- Centralized settings access via `bbpnns()->get_settings()`.
- Added PHPUnit tests and PHPCS ruleset; test suite runs in the plugin-local environment.

Notes:
- Legacy `*.class.php` originals are preserved in `includes/legacy/controller/` and are not loaded by the new autoloader unless explicitly referenced.
- For developer testing: run `composer install` (if needed) then `vendor/bin/phpunit --configuration phpunit.xml` from the plugin directory.

## 3.0.1 - Bugfixes and test improvements (released)

- Fix: `isset()` behavior on the settings model when properties are accessed via magic `__get()` by implementing `__isset()` on `bbPress_Notify_noSpam_Model_Settings`.
- Add: `__unset()`, `__debugInfo()`, `__serialize()` and `__unserialize()` and `__clone()` to the settings model to ensure consistent behavior for debugging, serialization and cloning.
- Tests: Merged unit tests verifying `isset()` works for `newtopic_recipients` and `newreply_recipients`, and updated test file naming so PHPUnit discovers tests reliably.

Notes:
- These changes address an issue where `isset( $settings->prop )` evaluated to false despite `$settings->prop` returning a valid value via `__get()`. PHP calls `__isset()` for `isset()` checks, so adding a proper `__isset()` implementation fixes the mismatch.

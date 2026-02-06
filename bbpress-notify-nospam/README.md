# bbPress Notify (No-Spam)

This repository contains the bbPress Notify (No-Spam) WordPress plugin.

Quick start

- Run PHP lint across plugin files:
```bash
find . -name '*.php' -not -path '*/includes/legacy/*' -not -path '*/vendor/*' -print0 | xargs -0 -n1 php -l
```

- Run unit tests (from project root after installing dev deps):
```bash
# install dev deps (if composer.json exists)
composer install

# run the plugin test suite
vendor/bin/phpunit --testsuite bbpnns
```

What changed in working tree (summary)
- Refactor to a v3 loader/autoloader and updated `load_lib()` to instantiate via autoload.
- Hardened autoloader to support legacy filename patterns and kebab-case mapping.
- Moved/updated several controller classes; removed `includes/legacy/` and `t/` (archived in SVN).
- Fixed `trace()` and `log_msg()` to preserve actual newlines in dry-run logs.
- Added PHPUnit tests including a php-lint smoke test and reflection-based checks.

Next steps
- Initialize a Git repo and commit changes. Suggested commands are in `COMMIT_MESSAGE.txt`.

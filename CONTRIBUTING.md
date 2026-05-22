# Contributing to DEX

Thanks for taking the time to contribute. DEX is a CodeIgniter 4-native monitoring library, so we try to keep changes small, predictable, and easy to review.

## Quick Start

- PHP: 8.2+
- CodeIgniter: 4.6.2+
- Install dependencies: `composer install`
- Run tests: `composer test`
- Run static analysis: `composer phpstan`
- Run full QA (when needed): `composer qa:full`

## Running tests

Run the default test suite:

```bash
composer test
```

Run SQLite explicitly:

```bash
composer test:sqlite
```

Run MySQL/MariaDB explicitly:

```bash
DB=MySQLi \
DB_HOST=127.0.0.1 \
DB_PORT=3306 \
DB_DATABASE=dex_test \
DB_USERNAME=root \
DB_PASSWORD=your_password \
composer test
```

Before opening a compatibility-related pull request, test both dependency edges:

```bash
composer update --prefer-lowest --prefer-dist --no-interaction
composer qa:full

composer update --prefer-dist --no-interaction
composer qa:full
```

## Before You Open a PR

- Check existing issues and discussions to avoid duplicate work.
- If you're proposing a bigger change, start a discussion first so we can agree on direction.
- Keep diffs focused. Avoid drive-by refactors.

## Development Guidelines

- Follow PSR-12.
- Prefer early returns and small, single-purpose functions.
- Preserve backward compatibility unless the change explicitly requires a breaking behavior.
- Keep telemetry overhead low and avoid unnecessary payload growth.
- Keep database work in repositories and schema changes in migrations.

### UI Work

The UI is intentionally lightweight:

- Vanilla JS only. No external JS dependencies.
- Reuse and extend styles in `src/Views/dex/_styles.php` (avoid inline styles).
- Reuse and extend JS in `src/Views/dex/_js.php` (avoid duplicated logic).
- Views should display data only. Prepare view data in orchestrators/services, not inside view files.

## Reporting Bugs

Use the bug report form and include:

- DEX version, PHP version, CodeIgniter version
- Steps to reproduce
- Expected vs actual behavior
- Logs, stack traces, and screenshots (redact secrets/PII)

## Requesting Features

Use the feature request form and describe:

- The problem you're trying to solve
- The simplest acceptable solution
- Any constraints (performance, storage, CI4 version support)

## Security Issues

Please do not open public issues for security reports. Review `SECURITY.md` for the preferred reporting path.


# Laravel: Migrate to SQL

[![Tests](https://github.com/othyn/laravel-migrate-to-sql/actions/workflows/tests.yml/badge.svg)](https://github.com/othyn/laravel-migrate-to-sql/actions/workflows/tests.yml)
[![Style](https://github.com/othyn/laravel-migrate-to-sql/actions/workflows/style.yml/badge.svg)](https://github.com/othyn/laravel-migrate-to-sql/actions/workflows/style.yml)
[![Code Coverage](https://img.shields.io/badge/code%20coverage-100%25-success)](https://img.shields.io/badge/code%20coverage-100%25-success)
[![Licence](https://img.shields.io/github/license/othyn/laravel-migrate-to-sql)](https://img.shields.io/github/license/othyn/laravel-migrate-to-sql)

Quickly convert and export all Laravel migrations into an SQL file, or to TTY, with options to prettify the output via a new handy `artisan` command that extends the default `migrate` command list.

```sh
$ php artisan migrate:to-sql
```

---

## Index

-   [Installation](#installation)
-   [Usage](#usage)
-   [Testing](#testing)
-   [Todo](#todo)
-   [Changelog](#changelog)

---

## Installation

Via Composer, you can run a `composer require` which will grab the latest version:

```sh
composer require othyn/laravel-migrate-to-sql
```

---

## Usage

Outlined below are all of the explicit command options, detailing their usage:

```sh
$ php artisan migrate:to-sql --help

Description:
  Generates SQL from your applications migrations

Usage:
  migrate:to-sql [options]

Options:
      --type[=TYPE]           Which type of migration to generate the SQL for; up or down [default: "up"]
      --exportPath[=PATH]     The output path for the generated SQL file, defaults to base_path() of the application
      --ugly                  Queries should not be prettified as part of the output process
      --tty                   Output should be sent to TTY instead of written to disk, use `--no-ansi` to disable output formatting

    ... laravel default options ...
```

---

### Usage - Default behaviour

By default, the command will:

-   Generate SQL for the `up` migrations
-   Generate the SQL queries to disk, in the root of your project directory, `base_path()` in the name format `migrations.{type}.{Y_m_d__His}.sql`.
-   Prettify the SQL output into structured queries

---

### Usage - Output a specific type of migration

By type, I'm referring to both the `up` and `down` methods within a migration - in which those are your only two options here.

By default, the command will generate `up` migrations, but if you wish to generate `down` migrations, then you can my doing the following:

```sh
$ php artisan migrate:to-sql --type=down
```

which will generate the `down` migrations to a file on disk, in the default directory specified above, in the following structure:

```sql
-- 2014_10_12_000000_create_users_table:
DROP
    TABLE IF EXISTS `users`;

-- 2014_10_12_100000_create_password_resets_table:
DROP
    TABLE IF EXISTS `password_resets`;

-- etc...
```

---

### Usage - Output to a specific custom export path

If you wish for the command to export to a custom defined location, then pass it with the `--exportPath` option, for example:

```sh
$ php artisan migrate:to-sql --exportPath=~/migrations.up.sql
```

which will generate the `up` migrations to `~/migrations.up.sql` on disk, in the following structure:

```sql
-- 2014_10_12_000000_create_users_table:
ALTER TABLE
    `users`
ADD
    UNIQUE `users_email_unique`(`email`);

-- etc...
```

---

### Usage - Output without formatting or prettifying the query

If you wish for the command to export the queries without doing any sort of formatting or pretty-ing of them, then pass the `--ugly` option, for example:

```sh
$ php artisan migrate:to-sql --ugly
```

which will generate the `up` migrations to a file on disk, in the default directory specified above, in the following structure:

```sql
-- 2014_10_12_000000_create_users_table:
alter table `users` add unique `users_email_unique`(`email`);

-- etc...
```

---

### Usage - Output to TTY instead of to disk

If you wish for the command to export the queries without storing them to disk and stead sending them to TTY, then pass the `--tty` option, for example (which will generate the `up` migrations):

```sh
$ php artisan migrate:to-sql --tty

-- 2014_10_12_000000_create_users_table:
ALTER TABLE
    `users`
ADD
    UNIQUE `users_email_unique`(`email`);

-- etc...
```

---

### Usage - Stacking options

Options can be stacked ontop of each other to get more customised output, for example you could do this:

```sh
$ php artisan migrate:to-sql --type=down --ugly --tty

-- 2014_10_12_000000_create_users_table:
drop table if exists `users`;

-- etc...
```

To generate only `down` migrations, to TTY only without SQL formatting taking place.

---

## Testing

There is a Docker container that is pre-built that contains an Alpine CLI release of PHP + PHPUnit + xdebug. This is setup to test the project and can be setup via the following:

```sh
composer build
```

This should trigger Docker Compose to build the image.

There are tests for all code written, in which can be run via:

```sh
# Using PHPUnit, with code coverage reporting, within the Docker container
composer test

# Using PHPUnit, with code coverage reporting, within the Docker container, specifying a direct test
composer test-filtered ItGeneratesSqlFromMigrations

# Using PHPUnit, with code coverage reporting, using local phpunit and xdebug
composer test-local

# Using PHPUnit, with code coverage reporting, using local phpunit and xdebug, specifying a direct test
composer test-local-filtered ItGeneratesSqlFromMigrations
```

In those tests, there are Feature tests for a production ready implementation of the package. There are also Unit tests for each class written for full coverage.

You can also easily open a shell in the testing container by using the command:

```sh
composer shell
```

---

## Todo

-   Currently I'm using my own forked version of `doctrine/sql-formatter` with a [PR'd change](https://github.com/doctrine/sql-formatter/pull/73). If or once this is merged, then the `repositories` key in `composer.json` can be removed, and the package updated to version `1.1.x`.
-   Wait for GitHub actions to formally introduce official support for dynamic code coverage badges, or implement [something like this](https://github.com/marketplace/actions/dynamic-badges) that can parse out from a phpunit coverage report. For now, its manual.

---

## Changelog

Any and all project changes for releases should be documented below. Versioning follows the [SEMVER](https://semver.org/) standard.

---

### Version 1.0.0

Initial release.

#### Added

-   Everything

#### Changed

-   Everything

#### Fixed

-   Everything

#### Removed

-   Everything

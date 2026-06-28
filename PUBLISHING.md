# Publishing the PHP SDK

This package is prepared for Packagist as `screenshotapi/sdk`, but it has not been submitted or published.

## Pre-Publish Checklist

- Confirm the package source repository Packagist will index. Packagist expects the submitted repository to expose this SDK's `composer.json` at the repository root. If publishing from the monorepo, split or mirror `sdks/php` to a PHP SDK repository first.
- Confirm the source URL in `composer.json` is the final public repository. Current placeholder: `https://github.com/miketromba/screenshotapi-php`.
- Confirm the package name `screenshotapi/sdk` is available or owned by the ScreenshotAPI Packagist account.
- Confirm `LICENSE`, `README.md`, `composer.json`, `src/`, `examples/`, `tests/`, and `phpunit.xml.dist` are committed.
- Run `composer validate --strict` from `sdks/php`.
- Run `composer install` from `sdks/php`.
- Run `composer test` from `sdks/php`.
- Confirm examples still match the public API and do not require real network access during tests.
- Create an annotated release tag, for example `v1.0.0`, in the package repository.
- Log in to Packagist, choose Submit, and enter the public repository URL.
- After Packagist crawls the repository, verify `composer require screenshotapi/sdk` installs the tagged release in a clean project.
- Configure the Packagist GitHub hook or auto-update integration for future tags.

## Release Update Checklist

- Update README examples and docs links for any API changes.
- Run `composer validate --strict`, `composer install`, and `composer test`.
- Tag the release in the source repository.
- Confirm Packagist shows the new tag and dependency metadata.
- Install the package in a clean temporary project and run a mocked smoke test.

## Official References

- Packagist publishing overview: https://packagist.org/
- Composer package schema: https://getcomposer.org/doc/04-schema.md
- Composer CLI commands: https://getcomposer.org/doc/03-cli.md

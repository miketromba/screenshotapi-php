# Publishing the PHP SDK

This package is published on Packagist as [`screenshotapi/sdk`](https://packagist.org/packages/screenshotapi/sdk) from the public repository [`miketromba/screenshotapi-php`](https://github.com/miketromba/screenshotapi-php). Packagist auto-updates on every push via the configured GitHub webhook, so new releases only require a new Git tag.

## One-Time Setup (already done)

- The public repository exposes this SDK's `composer.json` at its root (the contents of `sdks/php` are mirrored to the repo root).
- The package `screenshotapi/sdk` is owned by the ScreenshotAPI Packagist account.
- The Packagist GitHub auto-update webhook (`https://packagist.org/api/github`) is active on the repository, so pushed tags publish automatically.

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

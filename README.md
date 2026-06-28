# screenshotapi/sdk

Official PHP SDK for [ScreenshotAPI](https://screenshotapi.to?utm_source=packagist&utm_medium=php_sdk&utm_campaign=sdk_readme&utm_content=header&ref=php-sdk) — capture website screenshots with a simple API call.

## Installation

```bash
composer require screenshotapi/sdk
```

Requires PHP 8.1+ and Composer.

## Authentication

Create an API key in the [ScreenshotAPI dashboard](https://screenshotapi.to/dashboard/api-keys?utm_source=packagist&utm_medium=php_sdk&utm_campaign=sdk_readme&utm_content=auth_dashboard&ref=php-sdk), then expose it to your application as an environment variable:

```bash
export SCREENSHOTAPI_KEY=sk_live_your_key_here
```

The SDK sends the key with the `x-api-key` header for each request.

## First Screenshot

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use ScreenshotAPI\Client;
use ScreenshotAPI\ScreenshotOptions;

$apiKey = getenv('SCREENSHOTAPI_KEY');

if (!is_string($apiKey) || $apiKey === '') {
    throw new RuntimeException('Set SCREENSHOTAPI_KEY before running this example.');
}

$client = new Client($apiKey);

$metadata = $client->save(
    new ScreenshotOptions(url: 'https://example.com'),
    __DIR__ . '/screenshot.png',
);

echo "Saved screenshot.png\n";
echo "Credits remaining: {$metadata->creditsRemaining}\n";
```

## Advanced Options

Use `screenshot()` when you want raw image bytes, response metadata, or to stream the image from your framework.

```php
use ScreenshotAPI\ScreenshotOptions;

$result = $client->screenshot(new ScreenshotOptions(
    url: 'https://example.com/pricing',
    width: 1920,
    height: 1080,
    fullPage: true,
    type: 'webp',
    quality: 85,
    colorScheme: 'dark',
    waitUntil: 'networkidle0',
    waitForSelector: '#main',
    delay: 500,
    blockAds: true,
    removeCookieBanners: true,
    devicePixelRatio: 2,
    timezone: 'America/New_York',
    locale: 'en-US',
    cacheTtl: 300,
    removeElements: ['.modal', '#promo'],
    mockupDevice: 'browser',
    geoLocation: [
        'latitude' => 40.7128,
        'longitude' => -74.0060,
        'accuracy' => 25,
    ],
));

file_put_contents(__DIR__ . '/pricing.webp', $result->image);

echo "Content type: {$result->contentType}\n";
echo "Screenshot ID: {$result->metadata->screenshotId}\n";
echo "Duration: {$result->metadata->durationMs}ms\n";
```

Pass `html` to render HTML directly. The SDK automatically switches to `POST /api/v1/screenshot`.

```php
$pdf = $client->screenshot(new ScreenshotOptions(
    html: '<main><h1>Invoice</h1></main>',
    type: 'pdf',
    width: 1200,
));
```

You can also customize the API base URL, timeout, or Guzzle client:

```php
use GuzzleHttp\Client as HttpClient;
use ScreenshotAPI\Client;

$client = new Client(
    apiKey: getenv('SCREENSHOTAPI_KEY'),
    baseUrl: 'https://screenshotapi.to',
    timeout: 30.0,
    httpClient: new HttpClient(['timeout' => 30.0]),
);
```

## Error Handling

```php
use ScreenshotAPI\Exceptions\APIException;
use ScreenshotAPI\Exceptions\AuthenticationException;
use ScreenshotAPI\Exceptions\InsufficientCreditsException;
use ScreenshotAPI\Exceptions\InvalidAPIKeyException;
use ScreenshotAPI\Exceptions\ScreenshotFailedException;
use ScreenshotAPI\ScreenshotOptions;

try {
    $result = $client->screenshot(new ScreenshotOptions(
        url: 'https://example.com',
    ));
} catch (AuthenticationException $e) {
    // 401: API key is missing or malformed.
} catch (InvalidAPIKeyException $e) {
    // 403: API key is invalid or revoked.
} catch (InsufficientCreditsException $e) {
    echo "No credits remaining. Balance: {$e->balance}\n";
} catch (ScreenshotFailedException $e) {
    echo "Screenshot failed: {$e->getMessage()}\n";
} catch (APIException $e) {
    echo "ScreenshotAPI request failed ({$e->statusCode}): {$e->getMessage()}\n";
}
```

Network-level failures are wrapped in `APIException` with `errorCode` set to `request_failed`.

## Framework Examples

Example integrations are included in [`examples/`](examples/):

- [`plain-php.php`](examples/plain-php.php) — command-line PHP script.
- [`laravel-controller.php`](examples/laravel-controller.php) — Laravel controller that streams the image response.
- [`symfony-controller.php`](examples/symfony-controller.php) — Symfony controller with query validation and JSON errors.

## API Reference

### `new Client(apiKey, baseUrl, timeout, httpClient)`

| Parameter | Type | Required | Default | Description |
| --- | --- | --- | --- | --- |
| `apiKey` | `string` | Yes | - | Your ScreenshotAPI key |
| `baseUrl` | `string` | No | `https://screenshotapi.to` | API base URL |
| `timeout` | `float` | No | `60` | Request timeout in seconds |
| `httpClient` | `GuzzleHttp\ClientInterface` | No | internal Guzzle client | Custom Guzzle-compatible client |

### `$client->screenshot(ScreenshotOptions $options): Result`

| Option | Type | Required | Default | Description |
| --- | --- | --- | --- | --- |
| `url` | `string` | Required unless `html` is set | - | URL to capture |
| `html` | `string` | No | - | HTML document to render via POST |
| `width` | `int` | No | `1440` | Viewport width in pixels |
| `height` | `int` | No | `900` | Viewport height in pixels |
| `fullPage` | `bool` | No | `false` | Capture the full scrollable page |
| `type` | `string` | No | `png` | `png`, `jpeg`, `webp`, or `pdf` |
| `quality` | `int` | No | `100` | Image quality from 1 to 100 |
| `colorScheme` | `string` | No | browser default | `light` or `dark` |
| `waitUntil` | `string` | No | `networkidle2` | Page load event to wait for |
| `waitForSelector` | `string` | No | none | CSS selector to wait for before capture |
| `delay` | `int` | No | none | Extra delay after load, in milliseconds |
| `blockAds` | `bool` | No | `false` | Block common ad network requests |
| `removeCookieBanners` | `bool` | No | `false` | Attempt to remove cookie banners |
| `cssInject` | `string` | No | none | CSS injected before capture |
| `jsInject` | `string` | No | none | JavaScript evaluated before capture |
| `stealthMode` | `bool` | No | `false` | Enable bot-evasion browser settings |
| `devicePixelRatio` | `int` | No | `1` | Device pixel ratio, 1-3 |
| `timezone` | `string` | No | server default | IANA timezone |
| `locale` | `string` | No | server default | BCP 47 locale |
| `cacheTtl` | `int` | No | none | Cache TTL in seconds |
| `preloadFonts` | `bool` | No | `false` | Preload fonts before capture |
| `removeElements` | `list<string>` | No | none | CSS selectors to remove |
| `removePopups` | `bool` | No | `false` | Attempt to close popups |
| `mockupDevice` | `string` | No | none | `browser`, `iphone`, or `macbook` |
| `geoLocation` | `array` | No | none | Browser geolocation with `latitude`, `longitude`, and optional `accuracy` |

`Result` contains:

- `image`: binary image string.
- `contentType`: response content type such as `image/png` or `image/webp`.
- `metadata`: `creditsRemaining`, `screenshotId`, and `durationMs`.

### `$client->save(ScreenshotOptions $options, string $path): Metadata`

Captures a screenshot, writes it to `$path`, and returns the response metadata.

## Pricing and Free Tier

ScreenshotAPI includes **200 free screenshots per month** on the Free plan. Start with the [free tier](https://screenshotapi.to/sign-up?utm_source=packagist&utm_medium=php_sdk&utm_campaign=sdk_readme&utm_content=free_tier_cta&ref=php-sdk), or compare paid plans and credit packs on the [pricing page](https://screenshotapi.to/pricing?utm_source=packagist&utm_medium=php_sdk&utm_campaign=sdk_readme&utm_content=pricing&ref=php-sdk).

## Documentation and Support

- [PHP SDK documentation](https://screenshotapi.to/docs/sdks/php?utm_source=packagist&utm_medium=php_sdk&utm_campaign=sdk_readme&utm_content=docs&ref=php-sdk)
- [Screenshot API reference](https://screenshotapi.to/docs/api/screenshot?utm_source=packagist&utm_medium=php_sdk&utm_campaign=sdk_readme&utm_content=api_reference&ref=php-sdk)
- [Contact support](mailto:support@screenshotapi.to)

## Development

```bash
composer install
composer validate --strict
composer test
```

The test suite uses Guzzle mocks and does not make real network calls.

## License

MIT

<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ScreenshotAPI\Client;
use ScreenshotAPI\Exceptions\APIException;
use ScreenshotAPI\Exceptions\InsufficientCreditsException;
use ScreenshotAPI\ScreenshotOptions;

$apiKey = getenv('SCREENSHOTAPI_KEY');

if (!is_string($apiKey) || $apiKey === '') {
    fwrite(STDERR, "Set SCREENSHOTAPI_KEY before running this example.\n");
    exit(1);
}

$client = new Client($apiKey);

try {
    $metadata = $client->save(
        new ScreenshotOptions(
            url: 'https://example.com',
            width: 1440,
            height: 900,
            fullPage: true,
            type: 'webp',
            quality: 85,
        ),
        __DIR__ . '/example.webp',
    );

    echo "Saved examples/example.webp\n";
    echo "Credits remaining: {$metadata->creditsRemaining}\n";
} catch (InsufficientCreditsException $exception) {
    fwrite(STDERR, "No credits remaining. Balance: {$exception->balance}\n");
    exit(1);
} catch (APIException $exception) {
    fwrite(STDERR, "Screenshot failed ({$exception->statusCode}): {$exception->getMessage()}\n");
    exit(1);
}

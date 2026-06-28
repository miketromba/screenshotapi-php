<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use ScreenshotAPI\Client;
use ScreenshotAPI\Exceptions\APIException;
use ScreenshotAPI\ScreenshotOptions;

final class ScreenshotController extends Controller
{
    public function __invoke(Request $request): Response|JsonResponse
    {
        $validated = $request->validate([
            'url' => ['required', 'url'],
        ]);

        $apiKey = config('services.screenshotapi.key');

        if (!is_string($apiKey) || $apiKey === '') {
            return response()->json(['error' => 'ScreenshotAPI key is not configured.'], 500);
        }

        $client = new Client($apiKey);

        try {
            $result = $client->screenshot(new ScreenshotOptions(
                url: $validated['url'],
                width: 1440,
                height: 900,
                fullPage: true,
                type: 'webp',
                quality: 85,
            ));
        } catch (APIException $exception) {
            return response()->json([
                'error' => $exception->getMessage(),
                'code' => $exception->errorCode,
            ], $exception->statusCode >= 500 ? 502 : max($exception->statusCode, 400));
        }

        return response($result->image, 200, [
            'Content-Type' => $result->contentType,
            'Cache-Control' => 'public, max-age=3600',
            'X-Screenshot-Id' => $result->metadata->screenshotId,
            'X-Credits-Remaining' => (string) $result->metadata->creditsRemaining,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Controller;

use ScreenshotAPI\Client;
use ScreenshotAPI\Exceptions\APIException;
use ScreenshotAPI\ScreenshotOptions;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ScreenshotController extends AbstractController
{
    #[Route('/screenshot', name: 'screenshot_capture', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        $url = $request->query->get('url');

        if (!is_string($url) || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return new JsonResponse(['error' => 'A valid url query parameter is required.'], 422);
        }

        $apiKey = $_ENV['SCREENSHOTAPI_KEY'] ?? '';

        if (!is_string($apiKey) || $apiKey === '') {
            return new JsonResponse(['error' => 'ScreenshotAPI key is not configured.'], 500);
        }

        $client = new Client($apiKey);

        try {
            $result = $client->screenshot(new ScreenshotOptions(
                url: $url,
                width: 1440,
                height: 900,
                fullPage: true,
                type: 'webp',
                quality: 85,
            ));
        } catch (APIException $exception) {
            return new JsonResponse([
                'error' => $exception->getMessage(),
                'code' => $exception->errorCode,
            ], $exception->statusCode >= 500 ? 502 : max($exception->statusCode, 400));
        }

        return new Response($result->image, 200, [
            'Content-Type' => $result->contentType,
            'Cache-Control' => 'public, max-age=3600',
            'X-Screenshot-Id' => $result->metadata->screenshotId,
            'X-Credits-Remaining' => (string) $result->metadata->creditsRemaining,
        ]);
    }
}

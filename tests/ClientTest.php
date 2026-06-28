<?php

declare(strict_types=1);

namespace ScreenshotAPI\Tests;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use ScreenshotAPI\Client;
use ScreenshotAPI\Exceptions\APIException;
use ScreenshotAPI\Exceptions\AuthenticationException;
use ScreenshotAPI\Exceptions\InsufficientCreditsException;
use ScreenshotAPI\Exceptions\InvalidAPIKeyException;
use ScreenshotAPI\Exceptions\ScreenshotFailedException;
use ScreenshotAPI\Metadata;
use ScreenshotAPI\Result;
use ScreenshotAPI\ScreenshotOptions;

final class ClientTest extends TestCase
{
    private const TEST_KEY = 'sk_test_abc123';

    public function testConstructorRequiresApiKey(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('API key is required');

        new Client('');
    }

    public function testScreenshotOptionsRequiresUrl(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('URL or HTML is required');

        new ScreenshotOptions(url: '');
    }

    public function testBuildsScreenshotRequestWithHeadersAndQueryParams(): void
    {
        $history = [];
        $client = $this->clientWithResponses([
            $this->successfulImageResponse(),
        ], $history);

        $result = $client->screenshot(new ScreenshotOptions(
            url: 'https://example.com',
            width: 1280,
            height: 720,
            fullPage: true,
            type: 'webp',
            quality: 85,
            colorScheme: 'dark',
            waitUntil: 'networkidle0',
            waitForSelector: '#app',
            delay: 500,
            blockAds: true,
            removeCookieBanners: false,
            cssInject: 'body { background: black; }',
            jsInject: 'document.body.dataset.ready = "true"',
            stealthMode: true,
            devicePixelRatio: 2,
            timezone: 'America/New_York',
            locale: 'en-US',
            cacheTtl: 300,
            preloadFonts: true,
            removeElements: ['.cookie', '#promo'],
            removePopups: true,
            mockupDevice: 'browser',
            geoLocation: [
                'latitude' => 37.7749,
                'longitude' => -122.4194,
                'accuracy' => 25,
            ],
        ));

        self::assertCount(1, $history);

        $request = $history[0]['request'];
        self::assertInstanceOf(Request::class, $request);
        self::assertSame('/api/v1/screenshot', $request->getUri()->getPath());
        self::assertSame(self::TEST_KEY, $request->getHeaderLine('x-api-key'));

        parse_str($request->getUri()->getQuery(), $query);
        self::assertSame('https://example.com', $query['url']);
        self::assertSame('1280', $query['width']);
        self::assertSame('720', $query['height']);
        self::assertSame('true', $query['fullPage']);
        self::assertSame('webp', $query['type']);
        self::assertSame('85', $query['quality']);
        self::assertSame('dark', $query['colorScheme']);
        self::assertSame('networkidle0', $query['waitUntil']);
        self::assertSame('#app', $query['waitForSelector']);
        self::assertSame('500', $query['delay']);
        self::assertSame('true', $query['blockAds']);
        self::assertSame('false', $query['removeCookieBanners']);
        self::assertSame('body { background: black; }', $query['cssInject']);
        self::assertSame('document.body.dataset.ready = "true"', $query['jsInject']);
        self::assertSame('true', $query['stealthMode']);
        self::assertSame('2', $query['devicePixelRatio']);
        self::assertSame('America/New_York', $query['timezone']);
        self::assertSame('en-US', $query['locale']);
        self::assertSame('300', $query['cacheTtl']);
        self::assertSame('true', $query['preloadFonts']);
        self::assertSame('.cookie,#promo', $query['removeElements']);
        self::assertSame('true', $query['removePopups']);
        self::assertSame('browser', $query['mockupDevice']);
        self::assertSame('37.7749', $query['geoLatitude']);
        self::assertSame('-122.4194', $query['geoLongitude']);
        self::assertSame('25', $query['geoAccuracy']);

        self::assertInstanceOf(Result::class, $result);
        self::assertSame('image-bytes', $result->image);
        self::assertSame('image/webp', $result->contentType);
        self::assertSame(950, $result->metadata->creditsRemaining);
        self::assertSame('ss_test_123', $result->metadata->screenshotId);
        self::assertSame(321, $result->metadata->durationMs);
    }

    public function testHtmlCaptureUsesPostJsonBody(): void
    {
        $history = [];
        $client = $this->clientWithResponses([
            new Response(200, [
                'content-type' => 'application/pdf',
                'x-screenshot-id' => 'ss_html',
            ], '%PDF'),
        ], $history);

        $result = $client->screenshot(new ScreenshotOptions(
            url: 'https://example.com/base',
            width: 1200,
            fullPage: false,
            type: 'pdf',
            html: '<h1>Hello</h1>',
            blockAds: true,
            removeCookieBanners: true,
            cssInject: 'body{color:red}',
            jsInject: 'document.title = "x"',
            devicePixelRatio: 3,
            removeElements: ['.modal', '#banner'],
            geoLocation: [
                'latitude' => 0,
                'longitude' => 0,
            ],
        ));

        $request = $history[0]['request'];
        self::assertInstanceOf(Request::class, $request);
        self::assertSame('POST', $request->getMethod());
        self::assertSame('/api/v1/screenshot', $request->getUri()->getPath());
        self::assertSame(self::TEST_KEY, $request->getHeaderLine('x-api-key'));

        $body = json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('https://example.com/base', $body['url']);
        self::assertSame('<h1>Hello</h1>', $body['html']);
        self::assertSame(1200, $body['width']);
        self::assertFalse($body['fullPage']);
        self::assertSame('pdf', $body['type']);
        self::assertTrue($body['blockAds']);
        self::assertTrue($body['removeCookieBanners']);
        self::assertSame('body{color:red}', $body['cssInject']);
        self::assertSame('document.title = "x"', $body['jsInject']);
        self::assertSame(3, $body['devicePixelRatio']);
        self::assertSame(['.modal', '#banner'], $body['removeElements']);
        self::assertSame(['latitude' => 0, 'longitude' => 0], $body['geoLocation']);
        self::assertSame('application/pdf', $result->contentType);
    }

    public function testOmitsOptionalQueryParamsWhenUnset(): void
    {
        $history = [];
        $client = $this->clientWithResponses([
            $this->successfulImageResponse(),
        ], $history);

        $client->screenshot(new ScreenshotOptions(url: 'https://example.com'));

        $request = $history[0]['request'];
        self::assertInstanceOf(Request::class, $request);

        parse_str($request->getUri()->getQuery(), $query);
        self::assertSame(['url' => 'https://example.com'], $query);
    }

    public function testDefaultsResponseMetadataWhenHeadersAreMissing(): void
    {
        $history = [];
        $client = $this->clientWithResponses([
            new Response(200, [], 'image-bytes'),
        ], $history);

        $result = $client->screenshot(new ScreenshotOptions(url: 'https://example.com'));

        self::assertSame('image/png', $result->contentType);
        self::assertSame(0, $result->metadata->creditsRemaining);
        self::assertSame('', $result->metadata->screenshotId);
        self::assertSame(0, $result->metadata->durationMs);
    }

    public function testSaveWritesImageAndReturnsMetadata(): void
    {
        $history = [];
        $client = $this->clientWithResponses([
            $this->successfulImageResponse(),
        ], $history);
        $path = tempnam(sys_get_temp_dir(), 'screenshotapi-');
        self::assertIsString($path);

        try {
            $metadata = $client->save(new ScreenshotOptions(url: 'https://example.com'), $path);

            self::assertInstanceOf(Metadata::class, $metadata);
            self::assertSame('image-bytes', file_get_contents($path));
            self::assertSame(950, $metadata->creditsRemaining);
        } finally {
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    public function testSaveThrowsWhenFileCannotBeWritten(): void
    {
        $history = [];
        $client = $this->clientWithResponses([
            $this->successfulImageResponse(),
        ], $history);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to write screenshot');

        $client->save(
            new ScreenshotOptions(url: 'https://example.com'),
            sys_get_temp_dir() . '/' . uniqid('screenshotapi-missing-dir-', true) . '/screenshot.png',
        );
    }

    public function testMapsAuthenticationError(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid authentication');

        $history = [];
        $this->clientWithResponses([
            $this->jsonErrorResponse(401, ['error' => 'Invalid authentication']),
        ], $history)->screenshot(new ScreenshotOptions(url: 'https://example.com'));
    }

    public function testMapsInsufficientCreditsErrorWithBalance(): void
    {
        $history = [];

        try {
            $this->clientWithResponses([
                $this->jsonErrorResponse(402, [
                    'error' => 'Not enough credits',
                    'balance' => 0,
                ]),
            ], $history)->screenshot(new ScreenshotOptions(url: 'https://example.com'));

            self::fail('Expected InsufficientCreditsException.');
        } catch (InsufficientCreditsException $exception) {
            self::assertSame(402, $exception->statusCode);
            self::assertSame('insufficient_credits', $exception->errorCode);
            self::assertSame(0, $exception->balance);
            self::assertSame('Not enough credits', $exception->getMessage());
        }
    }

    public function testMapsInvalidApiKeyError(): void
    {
        $this->expectException(InvalidAPIKeyException::class);
        $this->expectExceptionMessage('API key is invalid');

        $history = [];
        $this->clientWithResponses([
            $this->jsonErrorResponse(403, ['message' => 'API key is invalid']),
        ], $history)->screenshot(new ScreenshotOptions(url: 'https://example.com'));
    }

    public function testMapsScreenshotFailedError(): void
    {
        $this->expectException(ScreenshotFailedException::class);
        $this->expectExceptionMessage('Browser crashed');

        $history = [];
        $this->clientWithResponses([
            $this->jsonErrorResponse(500, [
                'error' => 'Capture failed',
                'message' => 'Browser crashed',
            ]),
        ], $history)->screenshot(new ScreenshotOptions(url: 'https://example.com'));
    }

    public function testMapsUnknownStatusToApiException(): void
    {
        $history = [];

        try {
            $this->clientWithResponses([
                $this->jsonErrorResponse(429, ['error' => 'Too many requests']),
            ], $history)->screenshot(new ScreenshotOptions(url: 'https://example.com'));

            self::fail('Expected APIException.');
        } catch (APIException $exception) {
            self::assertSame(429, $exception->statusCode);
            self::assertSame('unknown_error', $exception->errorCode);
            self::assertSame('Too many requests', $exception->getMessage());
        }
    }

    public function testHandlesNonJsonErrorBody(): void
    {
        $history = [];

        try {
            $this->clientWithResponses([
                new Response(400, ['content-type' => 'text/plain'], 'bad request'),
            ], $history)->screenshot(new ScreenshotOptions(url: 'https://example.com'));

            self::fail('Expected APIException.');
        } catch (APIException $exception) {
            self::assertSame(400, $exception->statusCode);
            self::assertSame('unknown_error', $exception->errorCode);
            self::assertSame('Unknown error', $exception->getMessage());
        }
    }

    public function testWrapsNetworkErrors(): void
    {
        $history = [];

        try {
            $this->clientWithResponses([
                new ConnectException('Connection refused', new Request('GET', 'https://example.com')),
            ], $history)->screenshot(new ScreenshotOptions(url: 'https://example.com'));

            self::fail('Expected APIException.');
        } catch (APIException $exception) {
            self::assertSame(0, $exception->statusCode);
            self::assertSame('request_failed', $exception->errorCode);
            self::assertStringContainsString('Request failed: Connection refused', $exception->getMessage());
        }
    }

    /**
     * @param array<int, Response|\Throwable> $responses
     * @param array<int, array{request: mixed, response?: mixed, error?: mixed}> $history
     */
    private function clientWithResponses(array $responses, array &$history): Client
    {
        $mock = new MockHandler($responses);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($history));

        $httpClient = new HttpClient([
            'handler' => $stack,
            'http_errors' => false,
        ]);

        return new Client(
            apiKey: self::TEST_KEY,
            baseUrl: 'https://api.test',
            httpClient: $httpClient,
        );
    }

    private function successfulImageResponse(): Response
    {
        return new Response(200, [
            'content-type' => 'image/webp',
            'x-credits-remaining' => '950',
            'x-screenshot-id' => 'ss_test_123',
            'x-duration-ms' => '321',
        ], 'image-bytes');
    }

    /** @param array<string, mixed> $body */
    private function jsonErrorResponse(int $statusCode, array $body): Response
    {
        return new Response($statusCode, [
            'content-type' => 'application/json',
        ], json_encode($body, JSON_THROW_ON_ERROR));
    }
}

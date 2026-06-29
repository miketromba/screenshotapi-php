<?php

declare(strict_types=1);

namespace ScreenshotAPI;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use ScreenshotAPI\Exceptions\APIException;
use ScreenshotAPI\Exceptions\AuthenticationException;
use ScreenshotAPI\Exceptions\InsufficientCreditsException;
use ScreenshotAPI\Exceptions\InvalidAPIKeyException;
use ScreenshotAPI\Exceptions\ScreenshotFailedException;

class Client
{
    private const DEFAULT_BASE_URL = 'https://screenshotapi.to';
    private const DEFAULT_TIMEOUT = 60;

    private ClientInterface $httpClient;
    private string $apiKey;
    private string $baseUrl;

    public function __construct(
        string $apiKey,
        string $baseUrl = self::DEFAULT_BASE_URL,
        float $timeout = self::DEFAULT_TIMEOUT,
        ?ClientInterface $httpClient = null,
    ) {
        if (empty($apiKey)) {
            throw new \InvalidArgumentException('API key is required');
        }

        $this->apiKey = $apiKey;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->httpClient = $httpClient ?? new HttpClient([
            'timeout' => $timeout,
        ]);
    }

    public function screenshot(ScreenshotOptions $options): Result
    {
        try {
            $response = $options->usesPostBody()
                ? $this->httpClient->request(
                    'POST',
                    $this->baseUrl . '/api/v1/screenshot',
                    [
                        'headers' => ['x-api-key' => $this->apiKey],
                        'json' => $options->toRequestBody(),
                    ]
                )
                : $this->httpClient->request(
                    'GET',
                    $this->baseUrl . '/api/v1/screenshot',
                    [
                        'query' => $options->toQueryParams(),
                        'headers' => ['x-api-key' => $this->apiKey],
                    ]
                );
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $this->handleError($e->getResponse());
            }

            throw new APIException(
                'Request failed: ' . $e->getMessage(),
                0,
                'request_failed'
            );
        } catch (GuzzleException $e) {
            throw new APIException(
                'Request failed: ' . $e->getMessage(),
                0,
                'request_failed'
            );
        }

        if ($response->getStatusCode() >= 400) {
            $this->handleError($response);
        }

        $metadata = new Metadata(
            creditsRemaining: (int) ($response->getHeaderLine('x-credits-remaining') ?: '0'),
            screenshotId: $response->getHeaderLine('x-screenshot-id') ?: '',
            durationMs: (int) ($response->getHeaderLine('x-duration-ms') ?: '0'),
        );

        return new Result(
            image: (string) $response->getBody(),
            contentType: $response->getHeaderLine('content-type') ?: 'image/png',
            metadata: $metadata,
        );
    }

    public function save(ScreenshotOptions $options, string $path): Metadata
    {
        $result = $this->screenshot($options);
        $bytesWritten = @file_put_contents($path, $result->image);

        if ($bytesWritten === false) {
            throw new \RuntimeException("Failed to write screenshot to {$path}");
        }

        return $result->metadata;
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @throws APIException
     * @return never
     */
    private function handleError(\Psr\Http\Message\ResponseInterface $response): never
    {
        $body = (string) $response->getBody();
        $decoded = json_decode($body, true);
        $data = is_array($decoded) ? $decoded : [];

        $message = $data['error'] ?? $data['message'] ?? 'Unknown error';
        $statusCode = $response->getStatusCode();

        match ($statusCode) {
            401 => throw new AuthenticationException($message),
            402 => throw new InsufficientCreditsException($message, (int) ($data['creditBalance'] ?? $data['balance'] ?? 0)),
            403 => throw new InvalidAPIKeyException($message),
            500 => throw new ScreenshotFailedException($data['message'] ?? $message),
            default => throw new APIException($message, $statusCode, 'unknown_error'),
        };
    }
}

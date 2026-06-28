<?php

declare(strict_types=1);

namespace ScreenshotAPI\Exceptions;

class APIException extends \RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $statusCode,
        public readonly string $errorCode,
    ) {
        parent::__construct($message, $statusCode);
    }
}

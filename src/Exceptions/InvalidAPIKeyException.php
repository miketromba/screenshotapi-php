<?php

declare(strict_types=1);

namespace ScreenshotAPI\Exceptions;

class InvalidAPIKeyException extends APIException
{
    public function __construct(string $message)
    {
        parent::__construct($message, 403, 'invalid_api_key');
    }
}

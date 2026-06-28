<?php

declare(strict_types=1);

namespace ScreenshotAPI\Exceptions;

class ScreenshotFailedException extends APIException
{
    public function __construct(string $message)
    {
        parent::__construct($message, 500, 'screenshot_failed');
    }
}

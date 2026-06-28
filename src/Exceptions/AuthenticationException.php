<?php

declare(strict_types=1);

namespace ScreenshotAPI\Exceptions;

class AuthenticationException extends APIException
{
    public function __construct(string $message)
    {
        parent::__construct($message, 401, 'authentication_error');
    }
}

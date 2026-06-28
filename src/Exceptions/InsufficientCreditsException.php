<?php

declare(strict_types=1);

namespace ScreenshotAPI\Exceptions;

class InsufficientCreditsException extends APIException
{
    public function __construct(
        string $message,
        public readonly int $balance,
    ) {
        parent::__construct($message, 402, 'insufficient_credits');
    }
}

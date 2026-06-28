<?php

declare(strict_types=1);

namespace ScreenshotAPI;

class Metadata
{
    public function __construct(
        public readonly int $creditsRemaining,
        public readonly string $screenshotId,
        public readonly int $durationMs,
    ) {}
}

<?php

declare(strict_types=1);

namespace ScreenshotAPI;

class Result
{
    public function __construct(
        public readonly string $image,
        public readonly string $contentType,
        public readonly Metadata $metadata,
    ) {}
}

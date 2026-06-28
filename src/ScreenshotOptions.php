<?php

declare(strict_types=1);

namespace ScreenshotAPI;

class ScreenshotOptions
{
    public function __construct(
        public readonly ?string $url = null,
        public readonly ?int $width = null,
        public readonly ?int $height = null,
        public readonly ?bool $fullPage = null,
        public readonly ?string $type = null,
        public readonly ?int $quality = null,
        public readonly ?string $colorScheme = null,
        public readonly ?string $waitUntil = null,
        public readonly ?string $waitForSelector = null,
        public readonly ?int $delay = null,
        public readonly ?string $html = null,
        public readonly ?bool $blockAds = null,
        public readonly ?bool $removeCookieBanners = null,
        public readonly ?string $cssInject = null,
        public readonly ?string $jsInject = null,
        public readonly ?bool $stealthMode = null,
        public readonly ?int $devicePixelRatio = null,
        public readonly ?string $timezone = null,
        public readonly ?string $locale = null,
        public readonly ?int $cacheTtl = null,
        public readonly ?bool $preloadFonts = null,
        /** @var list<string>|null */
        public readonly ?array $removeElements = null,
        public readonly ?bool $removePopups = null,
        public readonly ?string $mockupDevice = null,
        /** @var array{latitude: float, longitude: float, accuracy?: float}|null */
        public readonly ?array $geoLocation = null,
    ) {
        if (($url === null || $url === '') && ($html === null || $html === '')) {
            throw new \InvalidArgumentException('URL or HTML is required');
        }
    }

    /** @return array<string, string> */
    public function toQueryParams(): array
    {
        $params = [];

        $this->setScalar($params, 'url', $this->url);
        $this->setScalar($params, 'width', $this->width);
        $this->setScalar($params, 'height', $this->height);
        $this->setBoolean($params, 'fullPage', $this->fullPage);
        $this->setScalar($params, 'type', $this->type);
        $this->setScalar($params, 'quality', $this->quality);
        $this->setScalar($params, 'colorScheme', $this->colorScheme);
        $this->setScalar($params, 'waitUntil', $this->waitUntil);
        $this->setScalar($params, 'waitForSelector', $this->waitForSelector);
        $this->setScalar($params, 'delay', $this->delay);
        $this->setBoolean($params, 'blockAds', $this->blockAds);
        $this->setBoolean($params, 'removeCookieBanners', $this->removeCookieBanners);
        $this->setScalar($params, 'cssInject', $this->cssInject);
        $this->setScalar($params, 'jsInject', $this->jsInject);
        $this->setBoolean($params, 'stealthMode', $this->stealthMode);
        $this->setScalar($params, 'devicePixelRatio', $this->devicePixelRatio);
        $this->setScalar($params, 'timezone', $this->timezone);
        $this->setScalar($params, 'locale', $this->locale);
        $this->setScalar($params, 'cacheTtl', $this->cacheTtl);
        $this->setBoolean($params, 'preloadFonts', $this->preloadFonts);
        if ($this->removeElements !== null) {
            $params['removeElements'] = implode(',', $this->removeElements);
        }
        $this->setBoolean($params, 'removePopups', $this->removePopups);
        $this->setScalar($params, 'mockupDevice', $this->mockupDevice);
        if ($this->geoLocation !== null) {
            $this->setScalar($params, 'geoLatitude', $this->geoLocation['latitude']);
            $this->setScalar($params, 'geoLongitude', $this->geoLocation['longitude']);
            $this->setScalar($params, 'geoAccuracy', $this->geoLocation['accuracy'] ?? null);
        }

        return $params;
    }

    /** @return array<string, mixed> */
    public function toRequestBody(): array
    {
        $body = [];

        $this->setBodyValue($body, 'url', $this->url);
        $this->setBodyValue($body, 'html', $this->html);
        $this->setBodyValue($body, 'width', $this->width);
        $this->setBodyValue($body, 'height', $this->height);
        $this->setBodyValue($body, 'fullPage', $this->fullPage);
        $this->setBodyValue($body, 'type', $this->type);
        $this->setBodyValue($body, 'quality', $this->quality);
        $this->setBodyValue($body, 'colorScheme', $this->colorScheme);
        $this->setBodyValue($body, 'waitUntil', $this->waitUntil);
        $this->setBodyValue($body, 'waitForSelector', $this->waitForSelector);
        $this->setBodyValue($body, 'delay', $this->delay);
        $this->setBodyValue($body, 'blockAds', $this->blockAds);
        $this->setBodyValue($body, 'removeCookieBanners', $this->removeCookieBanners);
        $this->setBodyValue($body, 'cssInject', $this->cssInject);
        $this->setBodyValue($body, 'jsInject', $this->jsInject);
        $this->setBodyValue($body, 'stealthMode', $this->stealthMode);
        $this->setBodyValue($body, 'devicePixelRatio', $this->devicePixelRatio);
        $this->setBodyValue($body, 'timezone', $this->timezone);
        $this->setBodyValue($body, 'locale', $this->locale);
        $this->setBodyValue($body, 'cacheTtl', $this->cacheTtl);
        $this->setBodyValue($body, 'preloadFonts', $this->preloadFonts);
        $this->setBodyValue($body, 'removeElements', $this->removeElements);
        $this->setBodyValue($body, 'removePopups', $this->removePopups);
        $this->setBodyValue($body, 'mockupDevice', $this->mockupDevice);
        $this->setBodyValue($body, 'geoLocation', $this->geoLocation);

        return $body;
    }

    public function usesPostBody(): bool
    {
        return $this->html !== null && $this->html !== '';
    }

    /** @param array<string, string> $params */
    private function setScalar(array &$params, string $key, string|int|float|null $value): void
    {
        if ($value !== null) {
            $params[$key] = (string) $value;
        }
    }

    /** @param array<string, string> $params */
    private function setBoolean(array &$params, string $key, ?bool $value): void
    {
        if ($value !== null) {
            $params[$key] = $value ? 'true' : 'false';
        }
    }

    /** @param array<string, mixed> $body */
    private function setBodyValue(array &$body, string $key, mixed $value): void
    {
        if ($value !== null) {
            $body[$key] = $value;
        }
    }
}

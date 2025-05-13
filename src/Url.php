<?php
declare(strict_types=1);

namespace Thinktomorrow\Url;

class Url
{
    private ParsedUrl $parsedUrl;

    private function __construct(ParsedUrl $parsedUrl)
    {
        $this->parsedUrl = $parsedUrl;
    }

    public static function fromString(string $url): self
    {
        return new static(ParsedUrl::fromString($url));
    }

    public function setCustomRoot(Root $root): self
    {
        $this->parsedUrl = $this->parsedUrl->replaceRoot($root);

        return $this;
    }

    public function getRoot(): Root
    {
        return $this->parsedUrl->getRoot();
    }

    public function secure(): self
    {
        return $this->scheme();
    }

    public function nonSecure(): self
    {
        return $this->scheme(false);
    }

    private function scheme(bool $secure = true): self
    {
        $this->parsedUrl = $this->parsedUrl->replaceScheme($secure ? 'https' : 'http');

        return $this;
    }

    public function get(): string
    {
        return $this->parsedUrl->get();
    }

    public function getScheme(): ?string
    {
        return $this->parsedUrl->getScheme();
    }

    public function getHost(): ?string
    {
        return $this->parsedUrl->getHost();
    }

    public function getPort(): ?string
    {
        return $this->parsedUrl->getPort();
    }

    public function getPath(): ?string
    {
        return $this->parsedUrl->getPath();
    }

    public function getQuery(): ?string
    {
        return $this->parsedUrl->getQuery();
    }

    public function getHash(): ?string
    {
        return $this->parsedUrl->getHash();
    }

    public function hasScheme(): bool
    {
        return $this->parsedUrl->hasScheme();
    }

    public function hasHost(): bool
    {
        return $this->parsedUrl->hasHost();
    }

    public function hasPort(): bool
    {
        return $this->parsedUrl->hasPort();
    }

    public function hasPath(): bool
    {
        return $this->parsedUrl->hasPath();
    }

    public function hasQuery(): bool
    {
        return $this->parsedUrl->hasQuery();
    }

    public function hasHash(): bool
    {
        return $this->parsedUrl->hasHash();
    }

    public function isAbsolute(): bool
    {
        return $this->parsedUrl->hasHost();
    }

    public function isSecure(): bool
    {
        return $this->parsedUrl->isSecure();
    }

    public function localize(?string $localeSegment = null, array $available_locales = []): self
    {
        $localizedPath = str_replace(
            '//',
            '/',
            rtrim('/'.trim($localeSegment.$this->delocalizePath($available_locales), '/'), '/')
        );

        $this->parsedUrl = $this->parsedUrl->replacePath($localizedPath);

        return $this;
    }

    private function delocalizePath(array $available_locales): string
    {
        if (! $this->parsedUrl->hasPath()) {
            return '';
        }

        $path_segments = explode('/', trim($this->parsedUrl->getPath(), '/'));

        // Remove the locale segment if present
        if (in_array($path_segments[0], array_keys($available_locales))) {
            unset($path_segments[0]);
        }

        return '/'.implode('/', $path_segments);
    }

    public function __toString(): string
    {
        return $this->get();
    }
}

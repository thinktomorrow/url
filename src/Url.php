<?php

namespace Thinktomorrow\Url;

class Url
{
    /** @var ParsedUrl */
    private $parsedUrl;

    private function __construct(ParsedUrl $parsedUrl)
    {
        $this->parsedUrl = $parsedUrl;
    }

    public static function fromString(string $url)
    {
        return new static( ParsedUrl::fromUrlString($url) );
    }

    public function setCustomRoot(Root $root)
    {
        $this->parsedUrl = $this->parsedUrl->replaceRoot($root);

        return $this;
    }

    public function secure()
    {
        return $this->scheme(true);
    }

    public function nonSecure()
    {
        return $this->scheme(false);
    }

    private function scheme(bool $secure = true)
    {
        $this->parsedUrl = $this->parsedUrl->replaceScheme($secure ? 'https' : 'http');

        return $this;
    }

    public function get()
    {
        return $this->parsedUrl->get();
    }

    public function isAbsolute(): bool
    {
        return $this->parsedUrl->hasHost();
    }

    public function localize(string $localeSegment = null, array $available_locales = [])
    {
        $localizedPath = str_replace('//', '/',
            rtrim('/'.trim($localeSegment.$this->delocalizePath($available_locales), '/'), '/')
        );

        $this->parsedUrl = $this->parsedUrl->replacePath($localizedPath);

        return $this;
    }

    private function delocalizePath(array $available_locales)
    {
        if (!$this->parsedUrl->hasPath()) {
            return;
        }

        $path_segments = explode('/', trim($this->parsedUrl->path(), '/'));

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

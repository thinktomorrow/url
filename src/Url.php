<?php

namespace Thinktomorrow\Url;

class Url
{
    /** @var ParsedUrl */
    private $parsedUrl;

    private $root;

    private $secure = false;

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
        $this->root = $root;

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
        $this->secure = $secure;

        return $this;
    }

    public function get()
    {
        if ($this->root) {
            if ($this->secure) {
                $this->root->secure($this->secure);
            }

            // Path is reconstructed. Taken care of possible double slashes
            $path = str_replace('//', '/', '/'.trim($this->reassembleWithoutRoot(), '/'));

            return rtrim($this->root->get().$path,'/');
        }

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

    /**
     * Construct a full url with the parsed url elements
     * resulted from a parse_url() function call.
     *
     * @return string
     */
    private function reassembleWithoutRoot()
    {
        /**
         * In some rare conditions the path is interpreted as the host when there is no domain.tld format given.
         * This is still considered a valid url, be it with only a tld as indication.
         */
        $path = ($this->parsedUrl->hasPath() && $this->parsedUrl->path() != $this->root->host())
                    ? $this->parsedUrl->path()
                    : '';

        return $path
            .($this->parsedUrl->hasQuery() ? '?'.$this->parsedUrl->query() : '')
            .($this->parsedUrl->hasHash() ? '#'.$this->parsedUrl->hash() : '');
    }
}

<?php

namespace Thinktomorrow\Url;

use Thinktomorrow\Url\Exceptions\InvalidUrl;

class Root
{
    /** @var string|null */
    private $scheme;

    /** @var string|null */
    private $host;

    /** @var string|null */
    private $port;

    /** @var bool */
    private $anonymousScheme;

    /** @var null|string */
    private $defaultScheme;

    /** @var bool */
    private bool $secure = false;

    /** @var bool */
    private $valid = false;

    private function __construct(?string $scheme = null, ?string $host = null, ?string $port = null, bool $anonymousScheme = false, ?string $defaultScheme = 'http://')
    {
        $this->scheme = $scheme;
        $this->host = $host;
        $this->port = $port;
        $this->anonymousScheme = $anonymousScheme;
        $this->defaultScheme = $defaultScheme;

        if (false !== filter_var($this->get(), FILTER_VALIDATE_URL)) {
            $this->valid = true;
        }

        if ($this->composeScheme() == 'https://') {
            $this->secure();
        }
    }

    public static function fromString(string $host)
    {
        return new static(...array_values(static::parse($host)));
    }

    public function get()
    {
        return $this->composeScheme() .
                $this->host() .
                ($this->port() ? ':'.$this->port : null);
    }

    public function valid(): bool
    {
        return $this->valid;
    }

    public function secure(): self
    {
        $this->secure = true;
        $this->scheme = 'https';

        return $this;
    }

    private function composeScheme()
    {
        return $this->scheme() ? $this->scheme().'://' : ($this->anonymousScheme ? '//' : $this->defaultScheme);
    }

    public function replaceScheme(string $scheme): self
    {
        return new static(
            $scheme,
            $this->host,
            $this->port,
            false,
            $this->defaultScheme
        );
    }

    public function defaultScheme(?string $scheme = null): self
    {
        return new static(
            $this->scheme,
            $this->host,
            $this->port,
            $this->anonymousScheme,
            $scheme
        );
    }

    public function host(): ?string
    {
        return $this->host;
    }

    public function scheme(): ?string
    {
        return $this->scheme;
    }

    public function port(): ?string
    {
        return $this->port;
    }

    private static function parse(string $url)
    {
        if (in_array($url, ['//','/'])) {
            return [
                'scheme' => null,
                'host' => null,
                'port' => null,
                'anonymousScheme' => false,
            ];
        }

        $parsed = parse_url($url);

        if (false === $parsed) {
            throw new InvalidUrl('Failed to parse url. Invalid url ['.$url.'] passed as parameter.');
        }

        return [
            'scheme' => $parsed['scheme'] ?? null,
            'host' => static::parseHost($parsed),
            'port' => $parsed['port'] ?? null,
            'anonymousScheme' => static::isAnonymousScheme($url),
        ];
    }

    public function __toString(): string
    {
        return $this->get();
    }

    /**
     * If an url is passed with anonymous scheme, e.g. //example.com, parse_url will ignore this and
     * strip the first tags so we need to explicitly reassemble the 'anonymous scheme' manually
     *
     * @param string $host
     * @return bool
     */
    private static function isAnonymousScheme(string $host): bool
    {
        $parsed = parse_url($host);

        return ! isset($parsed['scheme']) && (0 === strpos($host, '//') && isset($parsed['host']));
    }

    private static function parseHost(array $parsed): ?string
    {
        if (isset($parsed['host'])) {
            return $parsed['host'];
        }

        if (! isset($parsed['path'])) {
            return null;
        }

        // e.g. /foo/bar
        if ((0 === strpos($parsed['path'], '/'))) {
            return null;
        }

        // Invalid tld (missing .tld)
        if (false == strpos($parsed['path'], '.')) {
            return null;
        }

        // e.g. example.com/foo
        if ((0 < strpos($parsed['path'], '/'))) {
            return substr($parsed['path'], 0, strpos($parsed['path'], '/'));
        }

        // e.g. foo or example.com
        return $parsed['path'];
    }
}

<?php
declare(strict_types=1);

namespace Thinktomorrow\Url;

use JetBrains\PhpStorm\Pure;
use Thinktomorrow\Url\Exceptions\InvalidUrl;

class Root
{
    private ?string $scheme;
    private ?string $host;
    private ?string $port;

    private bool $anonymousScheme;
    private ?string $defaultScheme;
    private bool $valid;

    private function __construct(?string $scheme = null, ?string $host = null, ?string $port = null, bool $anonymousScheme = false, ?string $defaultScheme = 'https://')
    {
        $this->scheme = $scheme;
        $this->host = $host;
        $this->port = $port;
        $this->anonymousScheme = $anonymousScheme;
        $this->defaultScheme = $defaultScheme;

        $this->valid = (false !== filter_var($this->get(), FILTER_VALIDATE_URL));

//        if ($this->composeScheme() == 'https://') {
//            $this->secure();
//        }
    }

    public static function fromString(string $host): self
    {
        return new static(...array_values(static::parse($host)));
    }

    #[Pure]
    public function get(): string
    {
        return $this->composeScheme() .
                $this->getHost() .
                ($this->getPort() ? ':'.$this->port : null);
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function isSecure(): self
    {
        $this->scheme = 'https';

        return $this;
    }

    #[Pure]
    private function composeScheme(): ?string
    {
        return $this->getScheme()
            ? $this->getScheme().'://'
            : ($this->anonymousScheme ? '//' : $this->defaultScheme);
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

    public function defaultScheme(?string $defaultScheme = null): self
    {
        return new static(
            $this->scheme,
            $this->host,
            $this->port,
            $this->anonymousScheme,
            $defaultScheme
        );
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function getScheme(): ?string
    {
        return $this->scheme;
    }

    public function getPort(): ?string
    {
        return $this->port;
    }

    private static function parse(string $url): array
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
            'port' => isset($parsed['port']) ? (string) $parsed['port'] : null,
            'anonymousScheme' => static::isAnonymousScheme($url),
        ];
    }

    #[Pure]
    public function __toString(): string
    {
        return $this->get();
    }

    /**
     * If an url is passed with anonymous scheme, e.g. //example.com, parse_url will ignore this and
     * strip the first tags, so we need to explicitly reassemble the 'anonymous scheme' manually
     *
     * @param string $host
     * @return bool
     */
    private static function isAnonymousScheme(string $host): bool
    {
        $parsed = parse_url($host);

        return ! isset($parsed['scheme']) && (str_starts_with($host, '//') && isset($parsed['host']));
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
        if ((str_starts_with($parsed['path'], '/'))) {
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

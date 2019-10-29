<?php

namespace Thinktomorrow\Url;

use Thinktomorrow\Url\Exceptions\InvalidUrl;

class ParsedUrl
{
    /** @var null|string */
    private $scheme;

    /** @var null|string */
    private $host;

    /** @var string|null */
    private $port;

    /** @var null|string */
    private $path;

    /** @var null|string */
    private $query;

    /** @var null|string */
    private $hash;

    public function __construct(?string $scheme = null, ?string $host = null, ?string $port = null, ?string $path = null, ?string $query = null, ?string $hash = null)
    {
        $this->scheme = $scheme;
        $this->host = $host;
        $this->port = $port;
        $this->path = $path;
        $this->query = $query;
        $this->hash = $hash;
    }

    public static function fromUrlString(string $url)
    {
        return new static(...array_values(static::parse($url)));
    }

    public function get(): string
    {
        return $this->reassembleUrl();
    }

    public function replaceScheme(string $scheme): self
    {
        return new static(
            $scheme,
            $this->host,
            $this->port,
            $this->path,
            $this->query,
            $this->hash
        );
    }

    public function replacePath(string $path): self
    {
        return new static(
            $this->scheme,
            $this->host,
            $this->port,
            $path,
            $this->query,
            $this->hash
        );
    }

    private static function parse(string $url): array
    {
        // Specific case where we accept double slashes and convert it to a relative url.
        // This would otherwise not be able to be parsed.
        if ($url == '//') {
            $url = '/';
        }

        $parsed = parse_url($url);

        if (false === $parsed) {
            throw new InvalidUrl('Failed to parse url. Invalid url ['.$url.'] passed as parameter.');
        }

        // If a schemeless url is passed, parse_url will ignore this and strip the first tags
        // so we need to explicitly reassemble the 'anonymous scheme' manually
        $hasAnonymousScheme = (0 === strpos($url, '//') && isset($parsed['host']));

        return [
            'scheme' => $parsed['scheme'] ?? ($hasAnonymousScheme ? '//' : null),
            'host'   => $parsed['host'] ?? null,
            'port'   => $parsed['port'] ?? null,
            'path'   => $parsed['path'] ?? null,
            'query'  => $parsed['query'] ?? null,
            'hash'   => $parsed['fragment'] ?? null,
        ];
    }

    private function reassembleUrl(): string
    {
        return  $this->assembleScheme() .
                $this->host() .
                ($this->port() ? ':' . $this->port() : '') .
                $this->path() .
                ($this->query() ? '?' . $this->query() : '') .
                ($this->hash() ? '#' . $this->hash() : '');
    }

    private function assembleScheme(): string
    {
        if(!$this->hasScheme()) return '';

        // Anonymous scheme already ends with double slashes
        if($this->scheme() == '//') return $this->scheme();

        return $this->scheme() .'://';
    }

    public function scheme(): ?string
    {
        return $this->scheme;
    }

    public function host(): ?string
    {
        return $this->host;
    }

    public function port(): ?string
    {
        return $this->port;
    }

    public function path(): ?string
    {
        return $this->path;
    }

    public function query(): ?string
    {
        return $this->query;
    }

    public function hash(): ?string
    {
        return $this->hash;
    }

    public function hasScheme(): bool
    {
        return !!$this->scheme;
    }

    public function hasHost(): bool
    {
        return !!$this->host;
    }

    public function hasPort(): bool
    {
        return !!$this->port;
    }

    public function hasPath(): bool
    {
        return !!$this->path;
    }

    public function hasQuery(): bool
    {
        return !!$this->query;
    }

    public function hasHash(): bool
    {
        return !!$this->hash;
    }
}

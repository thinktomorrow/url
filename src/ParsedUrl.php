<?php

namespace Thinktomorrow\Url;

use Thinktomorrow\Url\Exceptions\InvalidUrl;

class ParsedUrl
{
    /** @var Root */
    private $root;

    /** @var null|string */
    private $path;

    /** @var null|string */
    private $query;

    /** @var null|string */
    private $hash;

    public function __construct(Root $root, ?string $path = null, ?string $query = null, ?string $hash = null)
    {
        $this->root = $root;
        $this->path = $path;
        $this->query = $query;
        $this->hash = $hash;
    }

    public static function fromString(string $url)
    {
        return new static(...array_values(static::parse($url)));
    }

    public function get(): string
    {
        return  $this->root->get() .
            ($this->hasPath() ? '/' . $this->path() : '') .
            ($this->hasQuery() ? '?' . $this->query() : '') .
            ($this->hasHash() ? '#' . $this->hash() : '');
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

        $root = Root::fromString($url)->defaultScheme(null);

        return [
            'root' => $root,
            // Check if path could match host because this means something as foobar.com is passed and this is regarded as 'path' by the parse_url function
            'path'   => (isset($parsed['path']) && $parsed['path'] && $parsed['path'] != $root->host()) ? trim($parsed['path'], '/') : null,
            'query'  => $parsed['query'] ?? null,
            'hash'   => $parsed['fragment'] ?? null,
        ];
    }

    public function replaceRoot(Root $root): self
    {
        return new static(
            $root,
            $this->path,
            $this->query,
            $this->hash
        );
    }

    public function replaceScheme(string $scheme): self
    {
        return new static(
            $this->root->replaceScheme($scheme),
            $this->path,
            $this->query,
            $this->hash
        );
    }

    public function replacePath(string $path): self
    {
        return new static(
            $this->root,
            trim($path, '/'),
            $this->query,
            $this->hash
        );
    }

    public function scheme(): ?string
    {
        return $this->root->scheme();
    }

    public function host(): ?string
    {
        return $this->root->host();
    }

    public function port(): ?string
    {
        return $this->root->port();
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
        return !!$this->root->scheme();
    }

    public function hasHost(): bool
    {
        return !!$this->root->host();
    }

    public function hasPort(): bool
    {
        return !!$this->root->port();
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

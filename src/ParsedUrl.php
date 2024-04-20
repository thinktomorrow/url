<?php
declare(strict_types=1);

namespace Thinktomorrow\Url;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use Thinktomorrow\Url\Exceptions\InvalidUrl;

class ParsedUrl
{
    private Root $root;
    private ?string $path;
    private ?string $query;
    private ?string $hash;

    public function __construct(Root $root, ?string $path = null, ?string $query = null, ?string $hash = null)
    {
        $this->root = $root;
        $this->path = $path;
        $this->query = $query;
        $this->hash = $hash;
    }

    public static function fromString(string $url): self
    {
        return new static(...array_values(static::parse($url)));
    }

    public function get(): string
    {
        $result = $this->root->get() .
            ($this->hasPath() ? '/' . $this->getPath() : '') .
            ($this->hasQuery() ? '?' . $this->getQuery() : '') .
            ($this->hasHash() ? '#' . $this->getHash() : '');

        return str_replace('///', '//', $result);
    }

    #[ArrayShape([
        'root' => Root::class,
        'path' => 'null|string',
        'query' => 'null|string',
        'hash' => 'null|string',
    ])]
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

        $root = Root::fromString($url)->defaultScheme();

        return [
            'root' => $root,
            // Check if path could match host because this means something as foobar.com is passed and this is regarded as 'path' by the parse_url function
            'path' => (isset($parsed['path']) && $parsed['path'] && $parsed['path'] != $root->getHost()) ? trim($parsed['path'], '/') : null,
            'query' => $parsed['query'] ?? null,
            'hash' => $parsed['fragment'] ?? null,
        ];
    }

    #[Pure]
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

    #[Pure]
    public function replacePath(string $path): self
    {
        return new static(
            $this->root,
            trim($path, '/'),
            $this->query,
            $this->hash
        );
    }

    #[Pure]
    public function getScheme(): ?string
    {
        return $this->root->getScheme();
    }

    #[Pure]
    public function getHost(): ?string
    {
        return $this->root->getHost();
    }

    #[Pure]
    public function getPort(): ?string
    {
        return $this->root->getPort();
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function getQuery(): ?string
    {
        return $this->query;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function isSecure(): bool
    {
        return $this->root->getScheme() === 'https';
    }

    #[Pure]
    public function hasScheme(): bool
    {
        return ! ! $this->root->getScheme();
    }

    #[Pure]
    public function hasHost(): bool
    {
        return ! ! $this->root->getHost();
    }

    #[Pure]
    public function hasPort(): bool
    {
        return ! ! $this->root->getPort();
    }

    public function hasPath(): bool
    {
        return ! ! $this->path;
    }

    public function hasQuery(): bool
    {
        return ! ! $this->query;
    }

    public function hasHash(): bool
    {
        return ! ! $this->hash;
    }
}

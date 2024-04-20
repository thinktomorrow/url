<?php

namespace Thinktomorrow\Url\Tests;

use PHPUnit\Framework\TestCase;
use Thinktomorrow\Url\Exceptions\InvalidUrl;
use Thinktomorrow\Url\Root;

class RootTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated()
    {
        $this->assertInstanceOf(Root::class, Root::fromString('nl'));
    }

    /** @test */
    public function it_normalizes_domain_to_complete_root()
    {
        $urls = [
            'example.com' => 'https://example.com',
            'example.com/' => 'https://example.com',
            'example.com/foo/bar' => 'https://example.com',
            'http://example.com' => 'http://example.com',
            'https://example.com' => 'https://example.com',
            'http://example.com/foo/bar' => 'http://example.com',
            'localhost:5000' => 'https://localhost:5000',
            '127.0.0.1' => 'https://127.0.0.1',
            'foobar' => 'https://', // invalid host
            'foo/bar' => 'https://', // invalid host

            // Schemeless
            '//example.com/foo/bar?s=q' => '//example.com',

            // Edgecases where root is completely empty - are there any?
            '/' => 'https://',
            '//' => 'https://',
            '' => 'https://',
        ];

        foreach ($urls as $original => $result) {
            $this->assertEquals($result, Root::fromString($original)->get());
        }
    }

    /** @test */
    public function it_can_set_root_as_secure()
    {
        $urls = [
            'example.com' => 'https://example.com',
            'http://example.com' => 'https://example.com',
            'https://example.com' => 'https://example.com',
        ];

        foreach ($urls as $original => $result) {
            $this->assertEquals($result, Root::fromString($original)->isSecure()->get());
        }

        $this->assertEquals(Root::fromString('uk.foobar.com')->isSecure(), Root::fromString('https://uk.foobar.com'));
    }

    /** @test */
    public function it_can_validate_root()
    {
        $this->assertFalse(Root::fromString('')->isValid());
        $this->assertFalse(Root::fromString('foobar')->isValid());

        $this->assertTrue(Root::fromString('foobar.com')->isValid());
        $this->assertTrue(Root::fromString('https://example.com')->isValid());
    }

    /** @test */
    public function it_can_validate_root_for_hashtag()
    {
        $this->assertFalse(Root::fromString('#')->isValid());
    }

    /** @test */
    public function instance_can_be_printed_as_string()
    {
        $this->assertEquals('https://foobar.com', (string) Root::fromString('foobar.com'));
    }

    /** @test */
    public function it_throws_exception_if_url_cannot_be_parsed()
    {
        $this->expectException(InvalidUrl::class);

        Root::fromString('javascript://');
    }

    /** @test */
    public function it_can_get_scheme()
    {
        $urls = [
            'example.com' => null,
            '//example.com' => null,
            'http://example.com' => 'http',
            'https://example.com' => 'https',
        ];

        foreach ($urls as $original => $result) {
            $this->assertEquals($result, Root::fromString($original)->getScheme());
        }
    }
}

<?php

namespace Thinktomorrow\Url\Tests;

use PHPUnit\Framework\TestCase;
use Thinktomorrow\Url\Exceptions\InvalidUrl;
use Thinktomorrow\Url\Root;
use Thinktomorrow\Url\Url;

class UrlTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated()
    {
        $this->assertInstanceOf(Url::class, Url::fromString('fake'));
    }

    /** @test */
    public function without_localization_parameter_urls_aren_not_altered()
    {
        $urls = [
            'http://example.com/fr',
            'http://example.com/fr/foo/bar',
            'http://example.com/fr/foo/bar?s=q',
            'http://example.fr/fr/foo/bar',
            'https://example.com/fr/foo/bar',
            'https://example.com/fr/foo/bar#index',
            '//example.com/fr/foo/bar',
            'http://example.com/fr/foo/bar',
        ];

        foreach ($urls as $url) {
            $this->assertEquals($url, Url::fromString($url)->get());
        }
    }

    /** @test */
    public function it_accepts_a_locale_segment()
    {
        $urls = [
            null                             => '/fr',
            '//'                             => '/fr',
            '/foo/bar'                       => '/fr/foo/bar',
            'foo/bar'                        => '/fr/foo/bar',
            ''                               => '/fr',
            'http://example.com'             => 'http://example.com/fr',
            'http://example.com/foo/bar'     => 'http://example.com/fr/foo/bar',
            'http://example.com/foo/bar?s=q' => 'http://example.com/fr/foo/bar?s=q',
        ];

        foreach ($urls as $original => $result) {
            $this->assertEquals($result,
                Url::fromString($original)
                    ->localize('fr', ['fr' => 'BE_fr', '/' => 'en'])
                    ->get()
            );
        }
    }

    /** @test */
    public function it_can_set_a_hidden_locale()
    {
        $urls = [
            '/foo/bar'                       => '/foo/bar',
            'foo/bar'                        => '/foo/bar',
            ''                               => '',
            'http://example.com'             => 'http://example.com',
            'http://example.com/foo/bar'     => 'http://example.com/foo/bar',
            'http://example.com/foo/bar?s=q' => 'http://example.com/foo/bar?s=q',
        ];

        foreach ($urls as $original => $result) {
            $this->assertEquals($result,
                Url::fromString($original)
                    ->localize(null, ['fr' => 'BE_fr', '/' => 'en'])
                    ->get()
            );
        }
    }

    /** @test */
    public function with_missing_host_it_still_tries_to_render_a_proper_url()
    {
        $this->assertEquals('/foo/bar', Url::fromString('foo/bar')->get());
        $this->assertEquals('http://foo/bar', Url::fromString('foo/bar')->nonSecure()->get());
        $this->assertEquals('https://foo/bar', Url::fromString('foo/bar')->secure()->get());
    }

    /** @test */
    public function it_removes_existing_locale_segment()
    {
        $urls = [
            ''                              => '/nl',
            '/fr/foo/bar'                   => '/nl/foo/bar',
            'fr/foo/bar'                    => '/nl/foo/bar',
            'fr'                            => '/nl',
            'http://example.com/fr'         => 'http://example.com/nl',
            'http://example.com/fr/foo/bar' => 'http://example.com/nl/foo/bar',
        ];

        foreach ($urls as $original => $result) {
            $this->assertEquals($result,
                Url::fromString($original)
                    ->localize('nl', ['fr' => 'BE_fr', '/' => 'en'])
                    ->get()
            );
        }
    }

    /** @test */
    public function it_keeps_passed_root_if_not_set_explicitly()
    {
        $this->assertEquals('http://example.fr/fr/foo/bar',
            Url::fromString('http://example.fr/foo/bar')
                ->localize('fr', ['fr' => 'BE_fr', '/' => 'en'])
                ->get()
        );
    }

    /** @test */
    public function it_can_set_custom_root()
    {
        $this->assertEquals('https://example.com/fr/foo/bar',
            Url::fromString('/foo/bar')
                ->setCustomRoot(Root::fromString('https://example.com'))
                ->localize('fr')
                ->get()
        );
    }

    /** @test */
    public function it_can_set_custom_root_with_secure()
    {
        $this->assertEquals('https://example.com/fr/foo/bar',
            Url::fromString('/foo/bar')
                ->setCustomRoot(Root::fromString('https://example.com'))
                ->localize('fr')
                ->secure()
                ->get()
        );
    }

    /** @test */
    public function it_can_set_custom_unsecure_root_with_secure()
    {
        $this->assertEquals('https://example.com/fr/foo/bar',
            Url::fromString('/foo/bar')
                ->setCustomRoot(Root::fromString('http://example.com'))
                ->localize('fr')
                ->secure()
                ->get()
        );
    }

    /** @test */
    public function it_can_set_custom_root_without_path()
    {
        $this->assertEquals('https://example.com',
            Url::fromString('/')
                ->setCustomRoot(Root::fromString('https://example.com'))
                ->get()
        );
    }


    /** @test */
    public function it_can_set_url_as_secure()
    {
        $this->assertEquals('https://example.com/fr/foo/bar',
            Url::fromString('http://example.com/foo/bar')
                ->localize('fr', ['fr' => 'BE_fr', '/' => 'en'])
                ->secure()
                ->get()
        );
    }

    /** @test */
    public function it_can_set_url_as_unsecure()
    {
        $this->assertEquals('http://example.com/fr/foo/bar',
            Url::fromString('https://example.com/foo/bar')
                ->localize('fr', ['fr' => 'BE_fr', '/' => 'en'])
                ->nonSecure()
                ->get()
        );
    }

    /** @test */
    public function it_can_check_if_given_url_is_absolute()
    {
        $urls = [
            '/foo/bar'                  => false,
            'foo/bar'                   => false,
            ''                          => false,
            'example.com'               => true,
            'http://example.com'        => true,
            '//example.com/foo/bar?s=q' => true,
            'https://example.com'       => true,
        ];

        foreach ($urls as $original => $result) {
            $this->assertEquals($result, Url::fromString($original)->isAbsolute());
        }
    }

    /** @test */
    public function instance_can_be_printed_as_string()
    {
        $this->assertEquals('foobar.com', (string) Url::fromString('foobar.com'));
    }

    /** @test */
    public function url_scheme_stays_the_same()
    {
        $this->assertEquals('http://foobar.com', Url::fromString('http://foobar.com')->get());
        $this->assertEquals('https://foobar.com', Url::fromString('https://foobar.com')->get());
        $this->assertEquals('//foobar.com', Url::fromString('//foobar.com')->get());
        $this->assertEquals('foobar.com', Url::fromString('foobar.com')->get());
    }

    /** @test */
    public function url_can_be_forced_to_prepend_non_secure_scheme()
    {
        $this->assertEquals('http://foobar.com', Url::fromString('foobar.com')->nonSecure()->get());
    }

    /** @test */
    public function url_can_be_forced_to_prepend_secure_scheme()
    {
        $this->assertEquals('https://foobar.com', Url::fromString('foobar.com')->secure()->get());
        $this->assertEquals('https://foobar.com', Url::fromString('http://foobar.com')->secure()->get());
        $this->assertEquals('https://foobar.com', Url::fromString('https://foobar.com')->secure()->get());
    }

    /** @test */
    public function it_throws_exception_if_url_cannot_be_parsed()
    {
        $this->expectException(InvalidUrl::class);

        Url::fromString('javascript://');
    }
}

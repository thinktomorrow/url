# Url

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

An url helper class to easily extract certain parts of the url. It is basically a wrapper around the native `parse_url` function. 
Currently, `parse_url` has no solid support for parsing relative url strings or url without an explicit scheme. This package aims to
provide that support.

This small package is framework agnostic and has no dependencies.

## Install via composer
``` bash
$ composer require thinktomorrow/url
```

## Usage

### Create an Url
Create a new url instance by calling the static `fromString` method with your url string. 
Note that in case of a malformed url, an `InvalidUrl` exception will be thrown. This
validation is based on what the native `parse_url` considers a malformed url string.
```php
\Thinktomorrow\Url\Url::fromString('https://example.com');
```

Now you have access to the different parts of the url string. You can retrieve the following parts:
```php
// scheme
\Thinktomorrow\Url\Url::fromString('https://example.com')->getScheme(); // https

// host
\Thinktomorrow\Url\Url::fromString('https://example.com')->getHost(); // example.com

// port
\Thinktomorrow\Url\Url::fromString('https://example.com:9000')->getPort(); // 9000

// path
\Thinktomorrow\Url\Url::fromString('https://example.com/foo/bar')->getPath(); // foo/bar

// query
\Thinktomorrow\Url\Url::fromString('https://example.com?foo=bar')->getQuery(); // foo=bar

// hash
\Thinktomorrow\Url\Url::fromString('https://example.com#foobar')->getHash(); // foobar
```

## Prepending a scheme
You can use the `Thinktomorrow\Url\Url` class to manipulate and parse your url string.

You can secure an url with the `secure()` method.
```php
Url::fromString('example.com')->secure()->get(); // 'https://example.com'
```

By default the usage of `scheme` forces a secure scheme. You can force a non-secure scheme with the `nonSecure` method.
```php
Url::fromString('example.com')->nonSecure()->get(); // 'http://example.com'
Url::fromString('https://example.com')->nonSecure()->get(); // 'http://example.com'
```

## Changing url root
In the case you need to change the url root, you can use the `setCustomRoot` method.
This expects a `\Thinktomorrow\Url\Root` object as argument.
```php
Url::fromString('http://example.com/foobar')
    ->setCustomRoot(Root::fromString('https://newroot.be'))
    ->get(); // 'https://newroot.be/foobar'
```

## Localizing the url
In case you use the url path segment for localization purposes, you can inject the locale segment with the `localize` method
```php
Url::fromString('http://example.com/foobar')
    ->localize('en')
    ->get(); // 'http://example.com/en/foobar'
```

The `localize` method also accepts a second parameter to enlist all available locales. In the case that passed url
contains any of these locales, it will be properly stripped off first.
```php
Url::fromString('http://example.com/en/foobar')
    ->localize('fr', ['en','fr'])
    ->get(); // 'http://example.com/fr/foobar'
```

If you pass `null` as the locale parameter, any locale segment will be removed.
```php
Url::fromString('http://example.com/en/foobar')
    ->localize(null, ['en','fr'])
    ->get(); // 'http://example.com/foobar'
```

## Testing

``` bash
$ vendor/bin/phpunit
```

## Security

If you discover any security related issues, please email ben@thinktomorrow.be instead of using the issue tracker.

## Credits

- Ben Cavens <ben@thinktomorrow.be>
- Philippe Damen <philippe@thinktomorrow.be>

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://poser.pugx.org/thinktomorrow/url/v/stable?format=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/thinktomorrow/url/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/thinktomorrow/url.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/thinktomorrow/url.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/thinktomorrow/url.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/thinktomorrow/url
[link-travis]: https://travis-ci.org/thinktomorrow/url
[link-scrutinizer]: https://scrutinizer-ci.com/g/thinktomorrow/url/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/thinktomorrow/url
[link-downloads]: https://packagist.org/packages/thinktomorrow/url
[link-author]: https://github.com/bencavens

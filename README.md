# locale

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

## Install

Via Composer

``` bash
$ composer require thinktomorrow/url
```

The package will be autodiscovered if you're using laravel so no need to add the provider to the config/app.php file in this case.

## Usage

This package only has 2 classes Url and Root.
The Url class is used to make sure your url is formatted properly.

Additionally you can localize it, secure it and set a custom root url.

```php
Url::fromString('/foo/bar')
    ->setCustomRoot(Root::fromString('https://example.com'))
    ->localize('fr')
    ->secure(true)
    ->get()
```

This will return `https://example.com/fr/foo/bar`.

Secondly the Root class is used to make sure the root is formatted properly.

```php
Root::fromString('https://example.com')->valid();
Root::fromString('https://example.com')->host();
Root::fromString('https://example.com')->scheme();
Root::fromString('https://example.com')->secure()->get();
```

## Testing

``` bash
$ vendor/bin/phpunit
```

## Security

If you discover any security related issues, please email ben@thinktomorrow.be instead of using the issue tracker.

## Credits

- Ben Cavens <ben@thinktomorrow.be>

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

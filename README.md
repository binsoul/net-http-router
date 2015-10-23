# net-http-router

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

This package provides a web router implementation for PSR-7 request. It uses matchers to extract information from the given request and returns a route object.

Your application can use the information provided by the matching route to dispatch it. A dispatcher is not provided by this package.

## Install

Via composer:

``` bash
$ composer require binsoul/net-http-router
```

## Testing

``` bash
$ composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/binsoul/net-http-router.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/binsoul/net-http-router.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/binsoul/net-http-router
[link-downloads]: https://packagist.org/packages/binsoul/net-http-router
[link-author]: https://github.com/binsoul

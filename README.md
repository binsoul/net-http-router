# net-http-router

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

This package provides a web router implementation for PSR-7 requests. It uses matchers to extract information from the given request and returns a route object.

Your application can use the information provided by the matching route to dispatch it. A dispatcher is not provided by this package.

## Install

Via composer:

``` bash
$ composer require binsoul/net-http-router
```

## Matcher

A matcher can be a closure, an object with an __invoke method or an object implementing the Matcher interface.

If a matcher decides that the route was found it can optionally set an response:

``` php
$router->addMatcher(
    function (Route $route)
    {
        if ($route->getMissingPath() == '/hello') {
            $route->found(new Response('Hello world!'));
        }
    }
);

$route = $router->match($request); // http:/domain/hello
if ($route->hasResponse()) {
    echo $route->getResponse(); // Hello world!
}
```

### StaticMatcher

The StaticMatcher matches simple paths and sets the provided route parameters of a match.

The following example:
``` php
$router->addMatcher(
    new StaticMatcher(
        [
            '/blog' => ['responder' => 'Blog']
        ]
    )
);

$route = $router->match($request); // http://domain/blog
var_export($route->getData());  
```
would output:
``` text
array('responder' => 'Blog',)
```


### RegexMatcher

The RegexMatcher matches arbitrary regular expressions. Named capture group are set as parameters of the route.

The following example:
``` php
$router->addMatcher(
    new RegexMatcher(
        [
            '/edit/(?<id>[0-9]+)' => ['responder' => 'Edit']
        ]
    )
);

$route = $router->match($request); // http://domain/edit/1
var_export($route->getData());  
```
would output:
``` text
array('id' => 1, 'responder' => 'Edit',)
```



### ParameterMatcher

The ParameterMatcher matches paths with parameter placeholders.

Placeholders can be defined in the following format:
``` text
[prefix+][name][=format[(length)]][?]
```

- Prefix: Any number of characters except "]" and "+" followed by a single "+".
- Name: A single character followed by characters, numbers or "_".
- Format: "=" followed by a defined format name optionally followed a length definition.
- Length: A single number or a number range enclosed in parenthesis.
- Marker: If the definition ends with a single "?" the parameter is optional.

For example the path:
``` text
/[year=number(4)][/+month=number(1-2)?]/[name].html
```
would match:
``` text
/2015/09/article.html
/2015/9/article.html
/2015/article.html
```

Found placeholders are set as parameters of the route. The following example:
``` php
$router->addMatcher(
    new ParameterMatcher(
        [
            '/[year]/[month]/[name].html' => ['responder' => 'Blog']
        ]
    )
);

$route = $router->match($request); // http://domain/2015/09/article.html
var_export($route->getData());  
```
would output:
``` text
array('year' => '2015', 'month' => '09', 'name' => 'article', 'responder' => 'Blog',)
```

### NamespaceMatcher

The NamespaceMatcher allows to group other matchers under a common path prefix:

The following definition:
``` php
$matcher = new NamespaceMatcher(
    '/admin',
    [
        new StaticMatcher(
            [
                '/' => ['responder' => 'Home'],
                '/list' => ['responder' => 'List'],
            ]
        ),
        new RegexMatcher(
            [
                '/edit/(?<id>[0-9]+)' => ['responder' => 'Edit'],
            ]
        ),
    ]
);
```
would match:
``` text
/admin/
/admin/list
/admin/edit/1
```

## Router

Matchers can be registered either as closures or concrete objects or as strings. If a matcher is registered as a string the provided factory is used to lazily build the matcher object.
``` php
$router->addMatcher('AccountMatcher');
$router->setFactory($factory);

// will call $factory->build('AccountMatcher');
$router->match($request); 
```

Matchers are added to an internal queue which will be processed by the default router in the order they were added to the queue.

For example if the queue is build like:
``` php
$router->addMatcher('Foo'); // matches /foo
$router->addMatcher('Bar'); // matches /bar
$router->addMatcher('Baz'); // matches /baz

$router->match($request); // http://domain/foo/bar/baz
```

The route object will change like this:
``` text
Foo is called with missing path = '/foo/bar/baz'    and matched path = ''
Bar is called with missing path = '/bar/baz'        and matched path = '/foo'
Baz is called with missing path = '/baz'            and matched path = '/foo/bar'
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

<?php

namespace BinSoul\Test\Net\Http\Router;

use BinSoul\Net\Http\Router\DefaultRouter;
use BinSoul\Net\Http\Router\Matcher;
use BinSoul\Net\Http\Router\MatcherFactory;
use BinSoul\Net\Http\Router\Route;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class DefaultRouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return RequestInterface
     */
    private function buildRequest()
    {
        $uri = $this->getMock(UriInterface::class);
        $uri->expects($this->any())->method('getPath')->willReturn('/path/to/file.html');

        $request = $this->getMock(RequestInterface::class);
        $request->expects($this->any())->method('getUri')->willReturn($uri);

        return $request;
    }

    public function test_calls_matcher_method()
    {
        $request = $this->buildRequest();

        $matcher = $this->getMock(Matcher::class);
        $matcher->expects($this->once())->method('match');

        $router = new DefaultRouter([$matcher]);
        $router->match($request);
    }

    public function test_calls_callable()
    {
        $request = $this->buildRequest();

        $called = false;
        $matcher = function () use (&$called) {
            $called = true;
        };

        $router = new DefaultRouter([$matcher]);
        $router->match($request);

        $this->assertTrue($called);
    }

    public function test_call_factory_method()
    {
        $request = $this->buildRequest();

        $matcher = $this->getMock(Matcher::class);
        $matcher->expects($this->once())->method('match');

        $factory = $this->getMock(MatcherFactory::class);
        $factory->expects($this->any())->method('build')->willReturn($matcher);

        $router = new DefaultRouter(['foobar'], $factory);
        $router->match($request);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function test_throws_exception_for_missing_factory()
    {
        $request = $this->buildRequest();

        $router = new DefaultRouter(['foobar']);
        $router->match($request);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function test_throws_exception_for_invalid_matcher()
    {
        $request = $this->buildRequest();

        $router = new DefaultRouter([null]);
        $router->match($request);
    }

    public function test_stops_routing_when_route_is_found()
    {
        $request = $this->buildRequest();

        $matcher = $this->getMock(Matcher::class);
        $matcher->expects($this->once())->method('match');

        $called = false;
        $callable = function (Route $route) use (&$called) {
            $called = true;
            $route->found();
        };

        $router = new DefaultRouter(
            [
                $matcher,
                $callable,
                $matcher,
            ]
        );

        $router->match($request);

        $this->assertTrue($called);
    }

    public function test_can_add_matcher()
    {
        $router = new DefaultRouter([]);
        $this->assertEquals(0, count($router->getMatchers()));
        $router->addMatcher('foobar');
        $this->assertEquals(1, count($router->getMatchers()));
    }
}

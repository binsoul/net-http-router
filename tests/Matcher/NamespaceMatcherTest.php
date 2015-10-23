<?php

namespace BinSoul\Test\Net\Http\Router\Matcher;

use BinSoul\Net\Http\Router\DefaultRoute;
use BinSoul\Net\Http\Router\Matcher\NamespaceMatcher;
use BinSoul\Net\Http\Router\Matcher\RegexMatcher;
use BinSoul\Net\Http\Router\Matcher\StaticMatcher;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class NamespaceMatcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $path
     *
     * @return RequestInterface
     */
    private function buildRequest($path)
    {
        $uri = $this->getMock(UriInterface::class);
        $uri->expects($this->any())->method('getPath')->willReturn($path);

        $request = $this->getMock(RequestInterface::class);
        $request->expects($this->any())->method('getUri')->willReturn($uri);

        return $request;
    }

    /**
     * @return NamespaceMatcher
     */
    private function buildMatcher()
    {
        return new NamespaceMatcher(
            '/admin',
            [
                new StaticMatcher(
                    [
                        '/' => ['controller' => 'Home'],
                        '/list' => ['controller' => 'List'],
                    ]
                ),
                new RegexMatcher(
                    [
                        '/edit/(?<id>[0-9]+)' => ['controller' => 'Edit'],
                    ]
                ),
            ]
        );
    }

    public function test_doesnt_match_other_namespace()
    {
        $matcher = $this->buildMatcher();

        $route = new DefaultRoute($this->buildRequest('/'));
        $matcher->match($route);
        $this->assertFalse($route->isFound());
        $this->assertEquals('', $route->getMatchedPath());
    }

    public function test_calls_static_matcher()
    {
        $matcher = $this->buildMatcher();

        $route = new DefaultRoute($this->buildRequest('/admin'));
        $matcher->match($route);
        $this->assertTrue($route->isFound());
        $this->assertEquals('/admin/', $route->getMatchedPath());
        $this->assertEquals('Home', $route->getParameter('controller'));

        $route = new DefaultRoute($this->buildRequest('/admin/list'));
        $matcher->match($route);
        $this->assertTrue($route->isFound());
        $this->assertEquals('/admin/list', $route->getMatchedPath());
        $this->assertEquals('List', $route->getParameter('controller'));
    }

    public function test_calls_regex_matcher()
    {
        $matcher = $this->buildMatcher();

        $route = new DefaultRoute($this->buildRequest('/admin/edit/9'));
        $matcher->match($route);
        $this->assertTrue($route->isFound());
        $this->assertEquals('/admin/edit/9', $route->getMatchedPath());
        $this->assertEquals('Edit', $route->getParameter('controller'));
        $this->assertEquals(9, $route->getParameter('id'));
    }
}

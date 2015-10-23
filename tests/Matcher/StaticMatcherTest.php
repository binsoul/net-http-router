<?php

namespace BinSoul\Test\Net\Http\Router\Matcher;

use BinSoul\Net\Http\Router\DefaultRoute;
use BinSoul\Net\Http\Router\Matcher\StaticMatcher;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class StaticMatcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $path
     *
     * @return RequestInterface
     */
    private function buildRequest($path = '/path/to/file.html')
    {
        $uri = $this->getMock(UriInterface::class);
        $uri->expects($this->any())->method('getPath')->willReturn($path);

        $request = $this->getMock(RequestInterface::class);
        $request->expects($this->any())->method('getUri')->willReturn($uri);

        return $request;
    }

    /**
     * @param bool $allowPartialMatches
     *
     * @return StaticMatcher
     */
    private function buildMatcher($allowPartialMatches = true)
    {
        return new StaticMatcher(
            [
                '/path' => ['uri' => '/path'],
                '/path/to/file.html' => ['uri' => '/path/to/file.html'],
                '/unknown' => ['uri' => '/unknown'],
            ],
            $allowPartialMatches
        );
    }

    public function test_matches_longest_path()
    {
        $route = new DefaultRoute($this->buildRequest());
        $matcher = $this->buildMatcher();
        $matcher->match($route);

        $this->assertTrue($route->isFound());
        $this->assertEquals('/path/to/file.html', $route->getMatchedPath());
        $this->assertEquals('/path/to/file.html', $route->getParameter('uri'));
    }

    public function test_matches_shortest_path()
    {
        $route = new DefaultRoute($this->buildRequest('/path'));
        $matcher = $this->buildMatcher();
        $matcher->match($route);

        $this->assertTrue($route->isFound());
        $this->assertEquals('/path', $route->getMatchedPath());
        $this->assertEquals('/path', $route->getParameter('uri'));
    }

    public function test_matches_partial_path()
    {
        $route = new DefaultRoute($this->buildRequest('/path/to'));
        $matcher = $this->buildMatcher();
        $matcher->match($route);

        $this->assertFalse($route->isFound());
        $this->assertEquals('/path', $route->getMatchedPath());
    }

    public function test_doesnt_match_partial_path()
    {
        $route = new DefaultRoute($this->buildRequest('/path/to'));
        $matcher = $this->buildMatcher(false);
        $matcher->match($route);

        $this->assertFalse($route->isFound());
        $this->assertEquals('', $route->getMatchedPath());
    }

    public function test_doesnt_match_unknown_path()
    {
        $route = new DefaultRoute($this->buildRequest('/foobar'));
        $matcher = $this->buildMatcher();
        $matcher->match($route);

        $this->assertFalse($route->isFound());
        $this->assertEquals('', $route->getMatchedPath());
    }
}

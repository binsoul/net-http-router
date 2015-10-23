<?php

namespace BinSoul\Test\Net\Http\Router\Matcher;

use BinSoul\Net\Http\Router\DefaultRoute;
use BinSoul\Net\Http\Router\Matcher\RegexMatcher;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class RegexMatcherTest extends \PHPUnit_Framework_TestCase
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
     * @return RegexMatcher
     */
    private function buildMatcher($allowPartialMatches = true)
    {
        return new RegexMatcher(
            [
                '/path/[^/]+' => ['uri' => '/path[^/]+'],
                '/.*/file.html' => ['uri' => '/.*/file.html'],
                '/path/to/[0-9]+' => ['uri' => '/path/to/[0-9]+'],
            ],
            $allowPartialMatches
        );
    }

    public function test_matches_full_path()
    {
        $route = new DefaultRoute($this->buildRequest());
        $matcher = $this->buildMatcher();

        $matcher->match($route);

        $this->assertTrue($route->isFound());
        $this->assertEquals('/path/to/file.html', $route->getMatchedPath());
        $this->assertEquals('/.*/file.html', $route->getParameter('uri'));
    }

    public function test_matches_longest_path()
    {
        $route = new DefaultRoute($this->buildRequest('/path/to/10'));
        $matcher = $this->buildMatcher();

        $matcher->match($route);

        $this->assertTrue($route->isFound());
        $this->assertEquals('/path/to/10', $route->getMatchedPath());
        $this->assertEquals('/path/to/[0-9]+', $route->getParameter('uri'));
    }

    public function test_matches_partial_path()
    {
        $route = new DefaultRoute($this->buildRequest('/path/to/123/foo'));
        $matcher = $this->buildMatcher(true);
        $matcher->match($route);

        $this->assertFalse($route->isFound());
        $this->assertEquals('/path/to/123', $route->getMatchedPath());
        $this->assertEquals('/foo', $route->getMissingPath());
        $this->assertEquals('/path/to/[0-9]+', $route->getParameter('uri'));
    }

    public function test_doesnt_match_partial_path()
    {
        $route = new DefaultRoute($this->buildRequest('/path/to/123/foo'));
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

    /**
     * @expectedException \RuntimeException
     */
    public function test_throws_exception_for_ambious_regex()
    {
        $route = new DefaultRoute($this->buildRequest());
        $matcher = new RegexMatcher(
            [
                '/path(.*)' => ['uri' => '/path(.*)'],
                '/(.*)/file.html' => ['uri' => '/(.*)/file.html'],
            ]
        );

        $matcher->match($route);
    }
}

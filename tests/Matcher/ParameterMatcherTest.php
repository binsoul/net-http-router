<?php

namespace BinSoul\Test\Net\Http\Router\Matcher;

use BinSoul\Net\Http\Router\DefaultRoute;
use BinSoul\Net\Http\Router\Matcher\ParameterMatcher;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class ParameterMatcherTest extends \PHPUnit_Framework_TestCase
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
     * @return ParameterMatcher
     */
    private function buildMatcher($allowPartialMatches = true)
    {
        return new ParameterMatcher(
            [
                '/archive/[year=number(4)]/[month=number(2)]/[day=number(1-2)]' => ['regex' => 1],
                '/archive[/+year=number][/+month=number?]' => ['regex' => 2],
                '/blog/[entry-+name].html' => ['regex' => 3],
                '/blog/[id=number][.+format?]' => ['regex' => 4],
                '/[+product=any]' => ['regex' => 5],
            ],
            $allowPartialMatches
        );
    }

    public function test_matches_longest_path()
    {
        $route = new DefaultRoute($this->buildRequest('/archive/2015/10/09'));
        $matcher = $this->buildMatcher();

        $matcher->match($route);

        $this->assertTrue($route->isFound());
        $this->assertEquals('/archive/2015/10/09', $route->getMatchedPath());
        $this->assertEquals('2015', $route->getParameter('year'));
        $this->assertEquals('10', $route->getParameter('month'));
        $this->assertEquals('09', $route->getParameter('day'));
        $this->assertEquals(1, $route->getParameter('regex'));
    }

    public function test_matches_parameter_length()
    {
        $route = new DefaultRoute($this->buildRequest('/archive/2015/10/9'));
        $matcher = $this->buildMatcher();

        $matcher->match($route);

        $this->assertTrue($route->isFound());
        $this->assertEquals('/archive/2015/10/9', $route->getMatchedPath());
        $this->assertEquals('2015', $route->getParameter('year'));
        $this->assertEquals('10', $route->getParameter('month'));
        $this->assertEquals('9', $route->getParameter('day'));
        $this->assertEquals(1, $route->getParameter('regex'));

        $route = new DefaultRoute($this->buildRequest('/archive/2015/8/7'));
        $matcher->match($route);
        $this->assertFalse($route->isFound());
        $this->assertNotEquals(1, $route->getParameter('regex'));
    }

    public function test_matches_optional_parameters()
    {
        $route = new DefaultRoute($this->buildRequest('/archive/2015'));
        $matcher = $this->buildMatcher();

        $matcher->match($route);

        $this->assertTrue($route->isFound());
        $this->assertEquals('/archive/2015', $route->getMatchedPath());
        $this->assertEquals('2015', $route->getParameter('year'));
        $this->assertFalse($route->hasParameter('month'));

        $route = new DefaultRoute($this->buildRequest('/archive/2015/10'));
        $matcher = $this->buildMatcher();
        $matcher->match($route);
        $this->assertTrue($route->isFound());
        $this->assertEquals('/archive/2015/10', $route->getMatchedPath());
        $this->assertEquals('2015', $route->getParameter('year'));
        $this->assertEquals('10', $route->getParameter('month'));

        $route = new DefaultRoute($this->buildRequest('/blog/1'));
        $matcher->match($route);

        $this->assertTrue($route->isFound());
        $this->assertEquals('/blog/1', $route->getMatchedPath());
        $this->assertNull($route->getParameter('format'));

        $route = new DefaultRoute($this->buildRequest('/blog/1.json'));
        $matcher->match($route);

        $this->assertTrue($route->isFound());
        $this->assertEquals('/blog/1.json', $route->getMatchedPath());
        $this->assertEquals('json', $route->getParameter('format'));
    }

    public function test_matches_multichar_prefix()
    {
        $route = new DefaultRoute($this->buildRequest('/blog/entry-foobar.html'));
        $matcher = $this->buildMatcher();

        $matcher->match($route);

        $this->assertTrue($route->isFound());
        $this->assertEquals('/blog/entry-foobar.html', $route->getMatchedPath());
        $this->assertEquals('foobar', $route->getParameter('name'));
        $this->assertEquals(3, $route->getParameter('regex'));
    }

    public function test_matches_without_parameters()
    {
        $route = new DefaultRoute($this->buildRequest('/blog/en'));
        $matcher = new ParameterMatcher(
            [
                '/blog/en' => ['regex' => 1],
            ]
        );

        $matcher->match($route);

        $this->assertTrue($route->isFound());
        $this->assertEquals('/blog/en', $route->getMatchedPath());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_throws_exception_for_invalid_definition()
    {
        $route = new DefaultRoute($this->buildRequest('/blog/en'));
        $matcher = new ParameterMatcher(
            [
                '/blog/[en=?]' => ['regex' => 1],
            ]
        );

        $matcher->match($route);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_throws_exception_for_invalid_format()
    {
        $route = new DefaultRoute($this->buildRequest('/blog/en'));
        $matcher = new ParameterMatcher(
            [
                '/blog/[en=foobar]' => ['regex' => 1],
            ]
        );

        $matcher->match($route);
    }
}

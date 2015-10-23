<?php

namespace BinSoul\Test\Net\Http\Router;

use BinSoul\Net\Http\Router\DefaultRoute;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

class DefaultRouteTest extends \PHPUnit_Framework_TestCase
{
    /**
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

    public function test_returns_request()
    {
        $request = $this->buildRequest();

        $route = new DefaultRoute($request);
        $this->assertSame($request, $route->getRequest());
    }

    public function test_returns_request_path()
    {
        $request = $this->buildRequest();

        $route = new DefaultRoute($request);
        $this->assertEquals('/path/to/file.html', $route->getMissingPath());
        $this->assertEquals('', $route->getMatchedPath());
    }

    public function test_resolves_segments()
    {
        $request = $this->buildRequest();
        $route = new DefaultRoute($request);

        $route->matchPath('/path');
        $this->assertEquals('/to/file.html', $route->getMissingPath());
        $this->assertEquals('/path', $route->getMatchedPath());

        $route->matchPath('/to/');
        $this->assertEquals('/file.html', $route->getMissingPath());
        $this->assertEquals('/path/to', $route->getMatchedPath());

        $route->matchPath('/file.html');
        $this->assertEquals('', $route->getMissingPath());
        $this->assertEquals('/path/to/file.html', $route->getMatchedPath());

        $request = $this->buildRequest();
        $route = new DefaultRoute($request);

        $route->matchPath('/');
        $this->assertEquals('/path/to/file.html', $route->getMissingPath());
        $this->assertEquals('', $route->getMatchedPath());
    }

    public function test_ending_slash()
    {
        $request = $this->buildRequest('/path/to/');
        $route = new DefaultRoute($request);

        $route->matchPath('/path/to/');
        $this->assertEquals('/', $route->getMissingPath());
        $this->assertEquals('/path/to', $route->getMatchedPath());

        $route->matchPath('/');
        $this->assertEquals('', $route->getMissingPath());
        $this->assertEquals('/path/to/', $route->getMatchedPath());
    }

    public function test_cannot_be_last()
    {
        $request = $this->buildRequest('/path/to');
        $route = new DefaultRoute($request);

        $route->matchPath('/path/to', false);
        $this->assertEquals('/', $route->getMissingPath());
        $this->assertEquals('/path/to', $route->getMatchedPath());

        $route->matchPath('/');
        $this->assertEquals('', $route->getMissingPath());
        $this->assertEquals('/path/to/', $route->getMatchedPath());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_throws_exception_for_different_start()
    {
        $request = $this->buildRequest('/path/to/');
        $route = new DefaultRoute($request);
        $route->matchPath('/pathto');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_throws_exception_for_invalid_segment()
    {
        $request = $this->buildRequest('/path/to/file.html');
        $route = new DefaultRoute($request);
        $route->matchPath('/path/to/file');
    }

    public function test_can_be_resolved()
    {
        $request = $this->buildRequest();

        $route = new DefaultRoute($request);
        $this->assertFalse($route->isFound());

        $response = $this->getMock(ResponseInterface::class);
        $route->found($response);

        $this->assertTrue($route->isFound());

        $route = new DefaultRoute($request);
        $route->found();

        $this->assertTrue($route->isFound());
    }

    public function test_returns_response()
    {
        $request = $this->buildRequest();

        $route = new DefaultRoute($request);
        $this->assertFalse($route->hasResponse());

        $response = $this->getMock(ResponseInterface::class);
        $route->found($response);

        $this->assertTrue($route->hasResponse());
        $this->assertSame($response, $route->getResponse());
    }

    public function test_parameters()
    {
        $request = $this->buildRequest();

        $route = new DefaultRoute($request);
        $this->assertFalse($route->hasParameter('foo'));

        $route->setParameter('foo', 'bar');
        $this->assertTrue($route->hasParameter('foo'));
        $this->assertEquals('bar', $route->getParameter('foo'));
        $this->assertEquals('qux', $route->getParameter('baz', 'qux'));

        $this->assertEquals(['foo' => 'bar'], $route->getParameters());
    }
}

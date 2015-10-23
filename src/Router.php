<?php

namespace BinSoul\Net\Http\Router;

use Psr\Http\Message\RequestInterface;

/**
 * Generates a route from a request.
 */
interface Router
{
    /**
     * Resolves a route for the given request.
     *
     * @param RequestInterface $request
     *
     * @return Route
     */
    public function match(RequestInterface $request);

    /**
     * Adds a matcher to the internal list of matchers.
     *
     * @param Matcher|callable|string $matcher
     */
    public function addMatcher($matcher);

    /**
     * Returns all registered matchers.
     *
     * @return Matcher[]|\callable[]|\string[]
     */
    public function getMatchers();
}

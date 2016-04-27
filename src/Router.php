<?php

declare (strict_types = 1);

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
    public function match(RequestInterface $request): Route;

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
    public function getMatchers(): array;

    /**
     * Sets the factory.
     *
     * @param MatcherFactory $factory
     */
    public function setFactory(MatcherFactory $factory);
}

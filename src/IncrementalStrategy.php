<?php

declare (strict_types = 1);

namespace BinSoul\Net\Http\Router;

/**
 * Calls matchers in the sequence determined by the provided array.
 */
trait IncrementalStrategy
{
    /**
     * Calls matchers with the given route.
     *
     * @param Route                         $route
     * @param Matcher[]|callable[]|string[] $matchers
     * @param MatcherFactory                $factory
     */
    protected function apply(Route $route, array $matchers, MatcherFactory $factory = null)
    {
        foreach ($matchers as $matcher) {
            if (is_object($matcher) && $matcher instanceof Matcher) {
                $matcher->match($route);
            } elseif (is_callable($matcher)) {
                $matcher($route);
            } elseif (is_string($matcher)) {
                if ($factory === null) {
                    throw new \RuntimeException(sprintf('No factory available to build matcher "%s".', $matcher));
                }

                $factory->buildMatcher($matcher)->match($route);
            } else {
                throw new \RuntimeException(sprintf('Invalid matcher of type "%s".', gettype($matcher)));
            }

            if ($route->isFound()) {
                break;
            }
        }
    }
}

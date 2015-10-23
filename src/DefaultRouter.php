<?php

namespace BinSoul\Net\Http\Router;

use Psr\Http\Message\RequestInterface;

/**
 * Provides a default implementation of the {@see Router} interface.
 */
class DefaultRouter implements Router
{
    use IncrementalStrategy;

    /** @var Matcher[]|callable[]|string[] */
    private $matchers;
    /** @var MatcherFactory */
    private $factory;

    /**
     * Constructs an instance of this class.
     *
     * @param Matcher[]|callable[]|string[] $matchers
     * @param MatcherFactory                $factory
     */
    public function __construct(array $matchers = [], MatcherFactory $factory = null)
    {
        $this->matchers = $matchers;
        $this->factory = $factory;
    }

    public function match(RequestInterface $request)
    {
        $route = new DefaultRoute($request);

        $this->apply($route, $this->matchers, $this->factory);

        return $route;
    }

    /**
     * @param Matcher|callable|string $matcher
     */
    public function addMatcher($matcher)
    {
        $this->matchers[] = $matcher;
    }

    /**
     * @return Matcher[]|\callable[]|\string[]
     */
    public function getMatchers()
    {
        return $this->matchers;
    }
}

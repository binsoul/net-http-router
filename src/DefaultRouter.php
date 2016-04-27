<?php

declare (strict_types = 1);

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

    public function match(RequestInterface $request): Route
    {
        $route = new DefaultRoute($request);

        $this->apply($route, $this->matchers, $this->factory);

        return $route;
    }

    public function addMatcher($matcher)
    {
        $this->matchers[] = $matcher;
    }

    public function getMatchers(): array
    {
        return $this->matchers;
    }

    public function setFactory(MatcherFactory $factory)
    {
        $this->factory = $factory;
    }
}

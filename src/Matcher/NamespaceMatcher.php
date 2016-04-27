<?php

declare (strict_types = 1);

namespace BinSoul\Net\Http\Router\Matcher;

use BinSoul\Net\Http\Router\IncrementalStrategy;
use BinSoul\Net\Http\Router\Matcher;
use BinSoul\Net\Http\Router\MatcherFactory;
use BinSoul\Net\Http\Router\Route;

/**
 * Provides a common path prefix for other matchers.
 */
class NamespaceMatcher implements Matcher
{
    use IncrementalStrategy;

    /** @var string */
    private $pathPrefix;
    /** @var Matcher[]|callable[]|string[] */
    private $matchers;
    /** @var MatcherFactory */
    private $factory;

    /**
     * Constructs an instance of this class.
     *
     * @param string                        $pathPrefix
     * @param Matcher[]|callable[]|string[] $matchers
     * @param MatcherFactory                $factory
     */
    public function __construct(string $pathPrefix, array $matchers = [], MatcherFactory $factory = null)
    {
        $this->matchers = $matchers;
        $this->factory = $factory;
        $this->pathPrefix = '/'.trim($pathPrefix, '/');
    }

    public function match(Route $route)
    {
        if (stripos($route->getMissingPath(), $this->pathPrefix) !== 0) {
            return;
        }

        $route->matchPath($this->pathPrefix, false);

        $this->apply($route, $this->matchers, $this->factory);
    }
}

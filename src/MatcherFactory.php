<?php

declare (strict_types = 1);

namespace BinSoul\Net\Http\Router;

/**
 * Builds concrete {@see Matcher} instances.
 */
interface MatcherFactory
{
    /**
     * Builds a matcher for the provided name.
     *
     * @param string $name
     *
     * @return Matcher
     */
    public function buildMatcher($name): Matcher;
}

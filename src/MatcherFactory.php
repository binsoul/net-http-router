<?php

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
    public function build($name);
}

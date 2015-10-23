<?php

namespace BinSoul\Net\Http\Router;

/**
 * Matches known path segments and sets route parameters.
 */
interface Matcher
{
    /**
     * Processes the route.
     *
     * @param Route $route
     */
    public function match(Route $route);
}

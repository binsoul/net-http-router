<?php

namespace BinSoul\Net\Http\Router;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Provides methods and necessary data to resolve a path.
 */
interface Route
{
    /**
     * Indicates if the route has been completely resolved.
     *
     * @return bool
     */
    public function isFound();

    /**
     * Marks the route as completely resolved.
     *
     * Routers should stop calling any other matchers if the route is found.
     * If a matcher knows how to respond to the request it can set a response when calling this method.
     *
     * @param ResponseInterface $response
     */
    public function found(ResponseInterface $response = null);

    /**
     * Marks a part of the path as resolved.
     *
     * Path can be a single segment or a number of segments separated by slash. Only segments at the beginning
     * of the missing path can be marked as matched.
     *
     * @param string $path
     * @param bool   $canBeLastMatch
     */
    public function matchPath($path, $canBeLastMatch = true);

    /**
     * Returns the part of the path which has been marked as matched.
     *
     * @return string
     */
    public function getMatchedPath();

    /**
     * Returns the part of the path which is not resolved yet.
     *
     * @return string
     */
    public function getMissingPath();

    /**
     * Returns the request of the route.
     *
     * @return RequestInterface
     */
    public function getRequest();

    /**
     * Indicates if a response was set.
     *
     * @return bool
     */
    public function hasResponse();

    /**
     * Returns the response.
     *
     * @return ResponseInterface
     */
    public function getResponse();

    /**
     * Indicates if a parameter exists.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasParameter($key);

    /**
     * Returns the value of a parameter.
     *
     * If the parameter doesn't exist the provided default value is returned.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getParameter($key, $default = null);

    /**
     * Sets the value of a parameter.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function setParameter($key, $value);

    /**
     * Returns all parameters as an array indexed by parameter name.
     *
     * @return mixed[]
     */
    public function getParameters();
}

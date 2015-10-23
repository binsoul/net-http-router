<?php

namespace BinSoul\Net\Http\Router\Matcher;

use BinSoul\Net\Http\Router\Matcher;
use BinSoul\Net\Http\Router\Route;

/**
 * Matches a list of regular expressions.
 *
 * Paths are sorted by length descending which means that the longest path matches first. If partial matches
 * are allowed sub parts of the request path will generate a match. Otherwise the complete request path has to be equal
 * to the matched path.
 *
 * Named capture groups are set as parameters of the route.
 *
 * The map of regular expressions to parameters should be provided in the following form:
 * <code>
 * [
 *      'regex1' => [
 *          'key1' => 'value1',
 *          'key2' => 'value2',
 *      ],
 *      'regex2' => [
 *          'key1' => 'value1',
 *          'key2' => 'value2',
 *      ]
 * ]
 * </code>
 */
class RegexMatcher implements Matcher
{
    /** @var string */
    protected $map;
    /** @var bool */
    protected $allowPartialMatches;

    /**
     * Constructs an instance of this class.
     *
     * @param mixed[][] $map
     * @param bool      $allowPartialMatches
     */
    public function __construct(array $map, $allowPartialMatches = true)
    {
        $this->map = $map;
        $this->allowPartialMatches = $allowPartialMatches;
    }

    public function match(Route $route)
    {
        $this->matchRegularExpressions($route, $this->map, $this->allowPartialMatches);
    }

    /**
     * Finds the longest matching path.
     *
     * @param Route     $route
     * @param mixed[][] $map
     * @param bool      $allowPartialMatches
     */
    protected function matchRegularExpressions(Route $route, array $map, $allowPartialMatches)
    {
        $paths = [];

        $missingPath = $route->getMissingPath();
        foreach ($map as $regex => $parameters) {
            if (!preg_match('#'.$regex.'#i', $missingPath, $matches)) {
                continue;
            }

            $match = $matches[0];
            if (!$allowPartialMatches && strtolower(trim($match, '/')) != strtolower(trim($missingPath, '/'))) {
                continue;
            }

            if (isset($paths[$match])) {
                throw new \RuntimeException(sprintf('Multiple regular expressions match "%s".', $match));
            }

            $paths[$match] = $parameters;
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $paths[$match][$key] = $value;
                }
            }
        }

        if (count($paths) == 0) {
            return;
        }

        uksort(
            $paths,
            function ($path1, $path2) {
                $path1Length = strlen($path1);
                $path2Length = strlen($path2);

                return $path1Length == $path2Length ? 0 : ($path1Length < $path2Length ? 1 : -1);
            }
        );

        $parameters = reset($paths);
        $path = key($paths);
        $route->matchPath($path);
        foreach ($parameters as $key => $value) {
            $route->setParameter($key, $value);
        }

        if (trim($route->getMissingPath(), '/') == '') {
            $route->found();
        }
    }
}

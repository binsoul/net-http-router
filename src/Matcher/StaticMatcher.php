<?php

namespace BinSoul\Net\Http\Router\Matcher;

use BinSoul\Net\Http\Router\Matcher;
use BinSoul\Net\Http\Router\Route;

/**
 * Matches a list of static paths.
 *
 * Paths are sorted by length descending which means that the longest path matches first. If partial matches
 * are allowed sub parts of the request path will generate a match. Otherwise the complete request path has to be equal
 * to the matched path.
 *
 * The map of paths to parameters should be provided in the following form:
 * <code>
 * [
 *      '/path1' => [
 *          'key1' => 'value1',
 *          'key2' => 'value2',
 *      ],
 *      '/path2/path3' => [
 *          'key1' => 'value1',
 *          'key2' => 'value2',
 *      ]
 * ]
 * </code>
 */
class StaticMatcher implements Matcher
{
    /** @var string */
    private $map;
    /** @var bool */
    private $allowPartialMatches;

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
        uksort(
            $this->map,
            function ($path1, $path2) {
                $path1Length = strlen($path1);
                $path2Length = strlen($path2);

                return $path1Length == $path2Length ? 0 : ($path1Length < $path2Length ? 1 : -1);
            }
        );

        $missingPath = $route->getMissingPath();
        foreach ($this->map as $path => $parameters) {
            if (stripos($missingPath, $path) !== 0) {
                continue;
            }

            if (!$this->allowPartialMatches && strtolower(trim($path, '/')) != strtolower(trim($missingPath, '/'))) {
                continue;
            }

            $route->matchPath($path);
            foreach ($parameters as $key => $value) {
                $route->setParameter($key, $value);
            }

            if (trim($route->getMissingPath(), '/') == '') {
                $route->found();
            }

            break;
        }
    }
}

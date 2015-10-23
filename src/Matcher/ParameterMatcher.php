<?php

namespace BinSoul\Net\Http\Router\Matcher;

use BinSoul\Net\Http\Router\Route;

/**
 * Matches a list of paths with parameter placeholders.
 *
 * Parameters can be defined in the following format:
 * <code>
 * [prefix+][name][=format[(length)]][?]
 * </code>
 *
 * - Prefix: Any number of characters except "]" and "+" followed by a single "+".
 * - Name: A single character followed by characters, numbers or "_".
 * - Format: "=" followed by a defined format name optionally followed a length definition.
 * - Length: A single number or a number range enclosed in parenthesis.
 * - Marker: If the definition ends with a single "?" the parameter is optional.
 *
 * For example the parameter definition:
 *
 * <code>
 * /[year=number(4)][/+month=number(1-2)?]/[name].html
 * </code>
 *
 * would match:
 *
 * <code>
 * /2015/09/article.html
 * /2015/9/article.html
 * /2015/article.html
 * </code>
 */
class ParameterMatcher extends RegexMatcher
{
    /** Regex for the parameter prefix */
    const PREFIX = '((?<prefix>.*?)\+)?';
    /** Regex for the parameter name */
    const NAME = '(?<name>[a-z][a-z0-9\_]*?)';
    /** Regex for the parameter format */
    const FORMAT = '((=)((?<format>[a-z]+)(\((?<length>[0-9]+(-[0-9]+)?)\))?))?';
    /** Regex for the parameter marker */
    const OPTIONAL = '(?<optional>\?)?';

    /**
     * Map of defined format names.
     *
     * @var string[]
     */
    private static $formats = [
      'any' => '[^/]',
      'char' => '[a-z]',
      'number' => '[0-9]',
      'alpha' => '[a-z0-9_\-]',
    ];

    public function match(Route $route)
    {
        $map = [];
        foreach ($this->map as $path => $parameters) {
            if (!preg_match_all('/\[(.*?)\]/i', $path, $matches, PREG_SET_ORDER)) {
                $map[$path] = $parameters;

                continue;
            }

            $target = $path;
            foreach ($matches as $key => $match) {
                $target = str_replace($match[0], '~~'.$key.'~~', $target);
            }

            $target = preg_quote($target, '#');

            foreach ($matches as $key => $match) {
                $parts = $this->parseParameter($match[1]);

                $format = $this->buildFormatRegex($parts['format'], $parts['length']);
                $regex = '(?<'.$parts['name'].'>'.$format.')';

                if ($parts['prefix'] != '') {
                    $regex = '('.preg_quote($parts['prefix'], '#').$regex.')';
                }

                if ($parts['optional']) {
                    $regex .= '?';
                }

                $target = str_replace('~~'.$key.'~~', $regex, $target);
            }

            $map[$target] = $parameters;
        }

        $this->matchRegularExpressions($route, $map, $this->allowPartialMatches);
    }

    /**
     * Parses a parameter definition and returns the parts.
     *
     * @param string $string
     *
     * @return mixed[]
     */
    private function parseParameter($string)
    {
        if (!preg_match('#^'.self::PREFIX.''.self::NAME.''.self::FORMAT.self::OPTIONAL.'$#', $string, $matches)) {
            throw new \InvalidArgumentException(sprintf('Invalid parameter definition "%s" found.', $string));
        }

        $result = [];
        foreach ($matches as $key => $match) {
            if (!is_int($key)) {
                $result[$key] = $match;
            }
        }

        $result = array_merge(
            [
                'prefix' => '',
                'name' => '',
                'format' => 'any',
                'length' => '',
                'optional' => false,
            ],
            $result
        );

        if ($result['format'] == '') {
            $result['format'] = 'any';
        }

        $result['optional'] = $result['optional'] === '?';

        return $result;
    }

    /**
     * Builds a regex for the parameter format.
     *
     * @param string $format
     * @param string $length
     *
     * @return string
     */
    private function buildFormatRegex($format, $length)
    {
        if (!isset(self::$formats[$format])) {
            throw new \InvalidArgumentException(sprintf('Unknown format "%s" found.', $format));
        }

        $result = self::$formats[$format];

        if ($length == '') {
            return $result.'+';
        }

        $parts = explode('-', $length);
        if (count($parts) == 1) {
            return $result.'{'.$parts[0].'}';
        }

        return $result.'{'.$parts[0].','.$parts[1].'}';
    }
}

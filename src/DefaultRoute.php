<?php

declare (strict_types = 1);

namespace BinSoul\Net\Http\Router;

use BinSoul\Common\Dictionary;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Provides a default implementation of the {@see Route} interface.
 */
class DefaultRoute implements Route
{
    /** @var bool */
    private $isFound = false;
    /** @var string */
    private $matchedPath = '';
    /** @var string */
    private $missingPath = '';
    /** @var RequestInterface */
    private $request;
    /** @var ResponseInterface */
    private $response;
    /** @var Dictionary */
    private $parameters;

    /**
     * Constructs an instance of this class.
     *
     * @param RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
        $this->parameters = new Dictionary();

        $this->missingPath = $request->getUri()->getPath();
    }

    public function isFound(): bool
    {
        return $this->isFound;
    }

    public function found(ResponseInterface $response = null)
    {
        $this->isFound = true;
        $this->response = $response;
    }

    public function matchPath(string $path, bool $canBeLastMatch = true)
    {
        $normalizedPath = '/'.trim($path, '/');
        if ($normalizedPath == '/' && trim($this->missingPath, '/') != '') {
            return;
        }

        $normalizedPathLength = strlen($normalizedPath);
        $match = substr($this->missingPath, 0, $normalizedPathLength);
        if (strtolower($match) != strtolower($normalizedPath)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The path "%s" doesn\'t start with "%s".',
                    $this->getMissingPath(),
                    $path
                )
            );
        }

        $missing = substr($this->missingPath, $normalizedPathLength);
        if ($missing === false || $missing == '') {
            $missing = $canBeLastMatch || $normalizedPath == '/' ? '' : '/';
        } elseif ($missing[0] != '/') {
            throw new \InvalidArgumentException(
                sprintf(
                    'The path "%s" doesn\'t contain "%s".',
                    $this->getMissingPath(),
                    $path
                )
            );
        }

        $this->matchedPath .= $match;
        $this->missingPath = $missing;
    }

    public function getMatchedPath(): string
    {
        return $this->matchedPath;
    }

    public function getMissingPath(): string
    {
        return $this->missingPath;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function hasResponse(): bool
    {
        return $this->response !== null;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function hasParameter(string $key): bool
    {
        return $this->parameters->has($key);
    }

    public function getParameter(string $key, $default = null)
    {
        return $this->parameters->get($key, $default);
    }

    public function setParameter(string $key, $value)
    {
        $this->parameters->set($key, $value);
    }

    public function getParameters(): array
    {
        return $this->parameters->all();
    }
}

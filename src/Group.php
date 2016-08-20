<?php


namespace Lune\Http\Router;


class Group extends RouteCollectorAbstract
{

    private $router;

    private $prefix;

    private $middlewares = [];
    private $parameters = [];

    public function __construct(Router $router, string $prefix, array $middlewares = [], array $parameters = [])
    {
        $this->router = $router;
        $this->prefix = $prefix;
        $this->middlewares = $middlewares;
        $this->parameters = $parameters;

    }

    public function add($method, $path, $handler, array $middlewares = [], array $parameters = [])
    {
        $path = trim($this->prefix . "/" . ltrim($path, '/'), '/');
        $middlewares = array_merge($this->middlewares, $middlewares);
        $parameters = array_merge($this->parameters, $parameters);
        $this->router->add(
            $method,
            $path,
            $handler,
            $middlewares,
            $parameters
        );
    }

    public function group($prefix, callable $callback, array $middlewares = [], array $parameters = [])
    {

        $middlewares = array_merge($this->middlewares, $middlewares);
        $parameters = array_merge($this->parameters, $parameters);
        $this->router->group("{$this->prefix}{$prefix}", $callback, $middlewares, $parameters);
    }
}

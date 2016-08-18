<?php


namespace Lune\Http\Router;


interface RouteCollectorInterface
{
    public function add($method, $path, $handler, array $middlewares = [], array $parameters = []);

    public function get($path, $handler, array $middlewares = [], array $parameters = []);

    public function post($path, $handler, array $middlewares = [], array $parameters = []);

    public function put($path, $handler, array $middlewares = [], array $parameters = []);

    public function patch($path, $handler, array $middlewares = [], array $parameters = []);

    public function delete($path, $handler, array $middlewares = [], array $parameters = []);

    public function head($path, $handler, array $middlewares = [], array $parameters = []);

    public function group($prefix, callable $callback, array $middlewares = [], array $parameters = []);
}
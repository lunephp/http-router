<?php


namespace Lune\Http\Router;


abstract class RouteCollectorAbstract implements RouteCollectorInterface
{


    public function get($path, $handler, array $middlewares = [], array $parameters = [])
    {
        $this->add('GET', $path, $handler, $middlewares, $parameters);
    }

    public function post($path, $handler, array $middlewares = [], array $parameters = [])
    {
        $this->add('POST', $path, $handler, $middlewares, $parameters);
    }

    public function put($path, $handler, array $middlewares = [], array $parameters = [])
    {
        $this->add('PUT', $path, $handler, $middlewares, $parameters);
    }

    public function patch($path, $handler, array $middlewares = [], array $parameters = [])
    {
        $this->add('PATCH', $path, $handler, $middlewares, $parameters);
    }

    public function delete($path, $handler, array $middlewares = [], array $parameters = [])
    {
        $this->add('DELETE', $path, $handler, $middlewares, $parameters);
    }

    public function head($path, $handler, array $middlewares = [], array $parameters = [])
    {
        $this->add('HEAD', $path, $handler, $middlewares, $parameters);
    }


}
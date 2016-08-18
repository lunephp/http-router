<?php


namespace Lune\Http\Router;


use Interop\Container\ContainerInterface;
use League\Route\RouteCollection;
use Lune\Http\Middleware\MiddlewareProvider;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Router extends RouteCollectorAbstract
{
    private $container;
    private $provider;
    private $routeCollection;


    public function __construct(ContainerInterface $container, MiddlewareProvider $provider)
    {
        $this->setContainer($container);
        $this->setProvider($provider);
        $this->routeCollection = new RouteCollection($container);
    }

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function getContainer():ContainerInterface
    {
        return $this->container;
    }


    public function setProvider(MiddlewareProvider $provider)
    {
        $this->provider = $provider;
    }


    public function getProvider():MiddlewareProvider
    {
        return $this->provider;
    }

    public function execute(ServerRequestInterface $request, ResponseInterface $response):ResponseInterface
    {
        return $this->routeCollection->dispatch($request, $response);
    }


    private function convertToCallable($handler):callable
    {
        if (is_callable($handler)) {
            return $handler;
        }

        if (is_string($handler) && strpos($handler, '::')) {
            return $this->convertToCallable(explode('::', $handler));
        }

        if (is_array($handler) && sizeof($handler) == 2) {

            $target = array_shift($handler);
            $method = array_shift($handler);

            if (is_string($target)) {
                if ($this->container->has($target)) {
                    $obj = $this->container->get($target);
                    return $this->convertToCallable([$obj, $method]);
                } else if (class_exists($handler[0])) {
                    $obj = new $target;
                    return $this->convertToCallable([$obj, $method]);
                }
            }
        }


    }


    public function add($method, $path, $handler, array $middlewares = [], array $parameters = [])
    {
        $stack = $this->getProvider()->getStack($middlewares);
        $stack->setParameter($parameters);
        $routeHandler = new RouteHandler($this->convertToCallable($handler), $stack);
        $this->routeCollection->map($method, $path, $routeHandler);
    }

    public function group($prefix, callable $callback, array $middlewares = [], array $parameters = [])
    {
        $group = new Group($this, $prefix, $middlewares, $parameters);
        call_user_func($callback, $group);
    }
}
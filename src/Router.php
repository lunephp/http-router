<?php


namespace Lune\Http\Router;


use Interop\Container\ContainerInterface;
use League\Route\RouteCollection;
use League\Container\Container;
use Lune\Http\Middleware\MiddlewareProvider;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Router extends RouteCollectorAbstract
{
    private $container;
    private $provider;
    private $routeCollection;


    public function __construct(ContainerInterface $container = null, MiddlewareProvider $provider = null)
    {
        $this->setContainer($container??new Container);
        $this->setProvider($provider??new MiddlewareProvider());
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
        $rv = $handler;

        if (is_string($rv)) {
            if (is_string($rv) && class_exists($rv) && method_exists($rv, '__invoke')) {
                $rv = new $rv;
            } else if (strpos($rv, '::')) {
                $rv = explode('::', $rv);
            }
        }


        if (is_array($rv) && isset($rv[0])) {
            $target = array_shift($rv);
            $method = array_shift($rv);

            $rv = function ($request, $response, $args) use ($target, $method) {
                $obj = $this->container->has($target) ? $this->container->get($target) : new $target;
                return call_user_func_array([$obj, $method], [$request, $response, $args]);
            };
        }

        return $rv;
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

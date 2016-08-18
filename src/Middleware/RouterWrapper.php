<?php


namespace Lune\Http\Router\Middleware;


use Lune\Http\Middleware\FrameInterface;
use Lune\Http\Middleware\MiddlewareInterface;
use Lune\Http\Router\Router;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class RouterWrapper implements MiddlewareInterface
{


    private $router;

    public function __construct(Router $router)
    {
        $this->setRouter($router);
    }


    public function getRouter():Router
    {
        return $this->router;
    }


    public function setRouter(Router $router)
    {
        $this->router = $router;
    }

    public function handle(ServerRequestInterface $request, FrameInterface $next, array $parameters = []):ResponseInterface
    {
        return $this->getRouter()->execute($request, $next->handle($request));
    }
}
<?php


namespace Lune\Http\Router;


use Lune\Http\Middleware\FrameInterface;
use Lune\Http\Middleware\Middleware\CallableWrapper;
use Lune\Http\Middleware\Stack;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class RouteHandler
{
    private $stack;
    private $handler;

    public function __construct(callable  $handler, Stack $stack)
    {
        $this->handler = $handler;
        $this->stack = $stack;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $args = []):ResponseInterface
    {
        $stack = clone $this->stack;
        $handler = $this->handler;

        $callback = function (ServerRequestInterface $request, FrameInterface $next, $parameters = []) use ($handler, $args) {
            $response = $next->handle($request);
            return call_user_func_array($handler, [$request, $response, $args]);
        };

        $stack->append(new CallableWrapper($callback));

        return $stack->execute($request, $response);
    }
}
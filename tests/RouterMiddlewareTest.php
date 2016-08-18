<?php


namespace Lune\Http\Router\Tests;

use League\Container\Container;
use Lune\Http\Middleware\MiddlewareProvider;
use Lune\Http\Middleware\Stack;
use Lune\Http\Router\Middleware\RouterWrapper;
use Lune\Http\Router\Router;
use PHPUnit_Framework_TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\ServerRequest;

class RouterMiddlewareTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function testRouterMiddleware()
    {
        $stack = new Stack();
        $router = new Router(new Container(), new MiddlewareProvider());

        $router->get('/test', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) {
            $response->getBody()->write('ok');
            return $response;
        });

        $stack->append(new RouterWrapper($router));
        $request = new ServerRequest([], [], '/test', 'GET');
        $response = $stack->execute($request, new HtmlResponse(''));


        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertEquals((string)$response->getBody(), 'ok');
    }
}
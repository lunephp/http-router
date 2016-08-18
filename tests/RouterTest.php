<?php


namespace Lune\Http\Router\Tests;

use League\Container\Container;
use Lune\Http\Middleware\FrameInterface;
use Lune\Http\Middleware\Middleware\CallableWrapper;
use Lune\Http\Middleware\MiddlewareProvider;
use Lune\Http\Router\Group;
use Lune\Http\Router\Router;
use PHPUnit_Framework_TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\ServerRequest;

class RouterTest extends PHPUnit_Framework_TestCase
{

    private function buildRouter():Router
    {
        $container = new Container();
        $provider = new MiddlewareProvider();

        return new Router($container, $provider);
    }

    private function buildRequest($path, $method = "GET"):ServerRequest
    {
        return new ServerRequest([], [], $path, $method);
    }

    /**
     * @test
     */
    public function testBasicRouter()
    {

        $router = $this->buildRouter();
        $router->get('/test', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) {
            $response->getBody()->write('ok');
            return $response;
        });

        $response = $router->execute($this->buildRequest('/test'), new HtmlResponse(""));

        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertEquals((string)$response->getBody(), 'ok');

    }


    private function buildRouterWithGroup():Router
    {
        $router = $this->buildRouter();
        $router->group("/group", function (Group $group) {
            $group->get('/', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) {
                $response->getBody()->write('ok_main');
                return $response;
            });
            $group->get('/sub', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) {
                $response->getBody()->write('ok_sub');
                return $response;
            });
        });

        return $router;
    }

    /**
     * @test
     */
    public function testGroup()
    {
        $router = $this->buildRouterWithGroup();
        $response = $router->execute($this->buildRequest('/group'), new HtmlResponse(""));
        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertEquals((string)$response->getBody(), 'ok_main');

        $router = $this->buildRouterWithGroup();
        $response = $router->execute($this->buildRequest('/group/sub'), new HtmlResponse(""));
        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertEquals((string)$response->getBody(), 'ok_sub');

    }

    private function getInterceptorMiddleware()
    {
        return new CallableWrapper(function (ServerRequestInterface $request, FrameInterface $frame, $parameters = []) {
            return new HtmlResponse("intercepted");
        });
    }

    /**
     * @test
     */

    public function testBasicMiddleware()
    {
        $router = $this->buildRouter();
        $router->getProvider()->addStack('stack', [$this->getInterceptorMiddleware()]);
        $router->get('/test', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) {
            $response->getBody()->write('ok');
            return $response;
        }, ['stack']);

        $response = $router->execute($this->buildRequest('/test'), new HtmlResponse(""));

        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertEquals((string)$response->getBody(), 'intercepted');
    }


    public function testGroupMiddleware()
    {
        $router = $this->buildRouter();
        $router->getProvider()->addStack('stack', [$this->getInterceptorMiddleware()]);

        $router->group("/group", function (Group $group) {
            $group->get('/', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) {
                $response->getBody()->write('ok_main');
                return $response;
            });
            $group->get('/sub', function (ServerRequestInterface $request, ResponseInterface $response, $args = []) {
                $response->getBody()->write('ok_sub');
                return $response;
            });
        }, ['stack']);
        
        $response = $router->execute($this->buildRequest('/group/sub'), new HtmlResponse(""));
        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertEquals((string)$response->getBody(), 'intercepted');

    }
}
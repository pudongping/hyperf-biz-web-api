<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Hyperf\Utils\Context;

class AuthMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {

        $token = $request->getHeaderLine('token');
        $userInfo = [
            'user_id' => 68,
        ];

        $request = Context::override(ServerRequestInterface::class, function () use ($request, $userInfo) {
            return $request->withAttribute('userInfo', $userInfo);
        });

        // $request = Context::get(ServerRequestInterface::class);
        // $request = $request->withAttribute('userInfo', $userInfo);
        // Context::set(ServerRequestInterface::class, $request);

        return $handler->handle($request);
    }
}
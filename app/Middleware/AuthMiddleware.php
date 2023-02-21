<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Hyperf\Context\Context;

class AuthMiddleware implements MiddlewareInterface
{

    public function __construct(protected ContainerInterface $container)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {

        $token = $request->getHeaderLine('token');
        $userInfo = [
            'user_id' => 68,
        ];

        $request = Context::override(ServerRequestInterface::class, function () use ($request, $userInfo) {
            return $request->withAttribute('user_info', $userInfo);
        });

        // $request = Context::get(ServerRequestInterface::class);
        // $request = $request->withAttribute('user_info', $userInfo);
        // Context::set(ServerRequestInterface::class, $request);

        return $handler->handle($request);
    }
}
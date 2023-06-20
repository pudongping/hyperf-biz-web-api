<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Hyperf\HttpServer\Contract\RequestInterface;

class LogInfoMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var RequestInterface
     */
    protected $request;

    public function __construct(
        ContainerInterface $container,
        RequestInterface $request
    ) {
        $this->container = $container;
        $this->request = $request;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (config('app.log_request_only')) {
            logger()->info('Request ========> ' . $this->logFormat($this->getRequestInfo($request)));
        }
        $response = $handler->handle($request);
        if (config('app.log_response_only')) {
            logger()->info('Response ========> ' . $this->logFormat($this->getResponseInfo($response)));
        }
        return $response;
    }

    private function getRequestInfo(ServerRequestInterface $request)
    {
        return [
            'method' => $request->getMethod(),  // 当前请求方法 GET/POST/PUT/PATCH ……
            'current_url' => $this->request->url(),
            'full_url' => $this->request->fullUrl(),
            'original_params' => $this->request->all(),  // 客户端请求的所有原始数据
            'origin' => $this->request->header('Origin'),  // 外部请求源链接地址
            'user_agent' => $this->request->header('user-agent'),  // 请求设备信息
            'headers' => $request->getHeaders(),
            'server_params' => $request->getServerParams(),
            'remote_addr' => $request->getServerParams()['remote_addr'] ?? '',  // 浏览当前页面的用户的 IP 地址
        ];
    }

    private function getResponseInfo(ResponseInterface $response)
    {
        // 控制器返回的数据
        $result = $response->getBody()->getContents();
        return json_decode($result, true);
    }

    private function logFormat($data)
    {
        return config('app.log_format_human') ? var_export($data, true) : json_encode($data, 256);
    }

}
<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Hyperf\Context\Context;
use Hyperf\DbConnection\Db;
use function Hyperf\Config\config;

class InitParamsMiddleware implements MiddlewareInterface
{

    public function __construct(protected ContainerInterface $container)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {

        $tempValue = [];
        $tempValueKey = config('app.context_key.temp_value');

        // 调用接口时是否需要开启 debug 模式
        $tempValue['debug'] = $debug = (boolean)request()->input('debug', false);

        // 调用接口时是否需要显示出报错信息
        $tempValue['raw'] = (boolean)request()->input('raw', false);

        // 是否开启分页
        $tempValue['is_show_page'] = (boolean)request()->input('is_show_page', false);
        // 当前页码，默认第一页
        $tempValue['page'] = (int)request()->input('page', 1);
        // 每页显示数，默认每页显示 20 条数据
        $tempValue['per_page'] = (int)request()->input('per_page', config('app.default_per_page'));

        // 排序规则 => $tempValue['order_by'] = id,desc|name,asc
        $tempValue['order_by'] = trim(request()->input('order_by', ''));

        if (config('app.debug') && $debug) {
            // 开启日志记录
            $connections = array_keys(config('databases', []));
            foreach ($connections as $connection) {
                Db::connection($connection)->enableQueryLog();
            }
        }

        // 存储到当前协程的上下文中
        if (Context::has($tempValueKey)) {
            $tempValue = array_merge(Context::get($tempValueKey), $tempValue);
        }
        Context::set($tempValueKey, $tempValue);

        return $handler->handle($request);
    }
}
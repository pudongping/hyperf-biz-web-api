<?php
/**
 * 助手函数
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2021-08-29 22:08
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

use Hyperf\Utils\ApplicationContext;
use Hyperf\Redis\Redis;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Server\ServerFactory;
use Swoole\Websocket\Frame;
use Swoole\WebSocket\Server as WebSocketServer;
use Psr\SimpleCache\CacheInterface;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\AsyncQueue\JobInterface;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\Utils\Context;
use Hyperf\DB\DB as HyperfSimpleDB;
use Hyperf\Paginator\LengthAwarePaginator;

if (! function_exists('container')) {
    /**
     * 获取容器对象
     *
     * @param string $id
     * @return mixed|\Psr\Container\ContainerInterface
     */
    function container(string $id = '')
    {
        $container = ApplicationContext::getContainer();

        if ($id) {
            return $container->get($id);
        }

        return $container;
    }
}

if (! function_exists('redis')) {
    /**
     *  获取 Redis 协程客户端
     *
     * @return Redis|mixed
     */
    function redis()
    {
        return container()->get(Redis::class);
    }
}

if (! function_exists('std_out_log')) {
    /**
     * 控制台日志
     *
     * @return StdoutLoggerInterface|mixed
     */
    function std_out_log()
    {
        return container()->get(StdoutLoggerInterface::class);
    }
}

if (! function_exists('logger')) {
    /**
     * 文件日志
     *
     * @return \Psr\Log\LoggerInterface
     */
    function logger()
    {
        return container()->get(LoggerFactory::class)->make();
    }
}

if (! function_exists('request')) {
    /**
     * request 实例
     *
     * @return RequestInterface|mixed
     */
    function request()
    {
        return container()->get(RequestInterface::class);
    }
}

if (! function_exists('response')) {
    /**
     * response 实例
     *
     * @return ResponseInterface|mixed
     */
    function response()
    {
        return container()->get(ResponseInterface::class);
    }
}

if (! function_exists('server')) {
    /**
     * 基于 swoole server 的 server 实例
     *
     * @return \Swoole\Coroutine\Server|\Swoole\Server
     */
    function server()
    {
        return container()->get(ServerFactory::class)->getServer()->getServer();
    }
}

if (! function_exists('frame')) {
    /**
     * websocket frame 实例
     *
     * @return mixed|Frame
     */
    function frame()
    {
        return container()->get(Frame::class);
    }
}

if (! function_exists('websocket')) {
    /**
     * websocket 实例
     *
     * @return mixed|WebSocketServer
     */
    function websocket()
    {
        return container()->get(WebSocketServer::class);
    }
}

if (! function_exists('cache')) {
    /**
     * 简单的缓存实例
     *
     * @return mixed|CacheInterface
     */
    function cache()
    {
        return container()->get(CacheInterface::class);
    }
}

if (! function_exists('simple_db')) {
    /**
     * 极简 DB
     *
     * @return HyperfSimpleDB|mixed
     */
    function simple_db()
    {
        return container()->get(HyperfSimpleDB::class);
    }
}

if (! function_exists('queue_push')) {
    /**
     * 将任务投递到异步队列中
     *
     * @param JobInterface $job
     * @param int $delay
     * @param string $key
     * @return bool
     */
    function queue_push(JobInterface $job, int $delay = 0, string $key = 'default'): bool
    {
        $driver = container()->get(DriverFactory::class)->get($key);
        return $driver->push($job, $delay);
    }
}

if (! function_exists('event_dispatch')) {
    /**
     * 事件分发
     *
     * @param object $event  事件对象
     * @return object
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function event_dispatch(object $event)
    {
        return container()->get(\Psr\EventDispatcher\EventDispatcherInterface::class)->dispatch($event);
    }
}

if (! function_exists('format_throwable')) {
    /**
     * 将错误异常对象格式化成字符串
     *
     * @param Throwable $throwable
     * @return string
     */
    function format_throwable(Throwable $throwable): string
    {
        return container()->get(FormatterInterface::class)->format($throwable);
    }
}

if (! function_exists('microtime_float')) {
    /**
     * 当前毫秒数
     *
     * @return float
     */
    function microtime_float()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }
}

if (! function_exists('get_client_ip')) {
    /**
     * 获取客户端 ip
     *
     * @return mixed|string
     */
    function get_client_ip()
    {
        $xForwardedFor = request()->getHeaderLine('X-Forwarded-For');
        $xRealIp = request()->getHeaderLine('X-Real-IP');
        $remoteAddr = request()->getServerParams()['remote_addr'];
        $realIp = $xForwardedFor ?: $xRealIp ?: $remoteAddr ?: '127.0.0.1';
        return $realIp;
    }
}

if (! function_exists('get_current_action')) {
    /**
     * 获取当前请求的控制器和方法
     *
     * @return array
     */
    function get_current_action(): array
    {
        $obj = request()->getAttribute(Dispatched::class);

        if (property_exists($obj, 'handler')
            && isset($obj->handler)
            && property_exists($obj->handler, 'callback')
        ) {
            $action = $obj->handler->callback;
        } else {
            throw new \Exception('The route is undefined! Please check!');
        }

        $errMsg = 'The controller and method are not found! Please check!';
        if (is_array($action)) {
            list($controller, $method) = $action;
        } elseif (is_string($action)) {
            if (strstr($action, '::')) {
                list($controller, $method) = explode('::', $action);
            } elseif (strstr($action, '@')) {
                list($controller, $method) = explode('@', $action);
            } else {
                list($controller, $method) = [false, false];
                logger()->error($errMsg);
                std_out_log()->error($errMsg);
            }
        } else {
            list($controller, $method) = [false, false];
            logger()->error($errMsg);
            std_out_log()->error($errMsg);
        }
        return compact('controller', 'method');
    }
}

if (! function_exists('set_global_init_params')) {
    /**
     * 重新设置全局初始化参数（在一次请求生命周期中替换掉 App\Middleware\InitParamsMiddleware::class 中的定义）
     *
     * @param string $key
     * @param null $value
     * @return mixed|null
     */
    function set_global_init_params(string $key, $value = null)
    {
        $tempValueKey = config('app.context_key.temp_value');
        if (! Context::has($tempValueKey)) return $value;
        $contextData = Context::get($tempValueKey, []);

        $override = array_merge($contextData, [
            $key => $value
        ]);

        return Context::set($tempValueKey, $override);
    }
}

if (! function_exists('get_global_init_params')) {
    /**
     * 获取初始化全局参数 （App\Middleware\InitParamsMiddleware::class 中定义）
     *
     * @param string|null $key
     * @param null $default
     * @return false|mixed|null
     */
    function get_global_init_params(?string $key = '', $default = null)
    {
        $tempValueKey = config('app.context_key.temp_value');
        if (! Context::has($tempValueKey)) return $default;
        $contextData = Context::get($tempValueKey, []);
        if (! $key) return $contextData;
        $value = $contextData[$key] ?? $default;

        return $value;
    }
}

if (! function_exists('prepare_for_page')) {
    /**
     * 拼接分页数据结构
     *
     * @param LengthAwarePaginator $obj  分页数据集
     * @return array
     */
    function prepare_for_page(LengthAwarePaginator $obj): array
    {
        $res = [];
        $pageArr = $obj->toArray();
        $res['total'] = $pageArr['total'];  // 数据总数
        $res['count'] = $obj->count();  // 当前页的条数
        $res['current_page'] = $pageArr['current_page'];  // 当前页数
        $res['last_page'] = $pageArr['last_page'];  // 最后页数
        $res['per_page'] = $pageArr['per_page'];  // 每页的数据条数
        $res['from'] = $pageArr['from'];  // 当前页中第一条数据的编号
        $res['to'] = $pageArr['to'];  // 当前页中最后一条数据的编号
        $res['links']['first_page_url'] = $pageArr['first_page_url'];  // 第一页的 url
        $res['links']['last_page_url'] = $pageArr['last_page_url'];  // 最后一页的 url
        $res['links']['prev_page_url'] = $pageArr['prev_page_url'];  // 上一页的 url
        $res['links']['next_page_url'] = $pageArr['next_page_url'];  // 下一页的 url
        $res['links']['path'] = $pageArr['path'];  // 所有 url 的基本路径
        return $res;
    }
}

if (! function_exists('auth')) {
    /**
     * 获取用户信息
     *
     * @return array|null
     */
    function auth(): ?array
    {
        return request()->getAttribute('userInfo');
    }
}
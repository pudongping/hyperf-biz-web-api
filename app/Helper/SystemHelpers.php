<?php
/**
 * 框架系统相关助手函数
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2021-08-29 22:08
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

if (! function_exists('container')) {
    /**
     * 获取容器对象
     *
     * @param string $id
     * @return mixed|\Psr\Container\ContainerInterface|string
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function container(string $id = ''): mixed
    {
        $container = \Hyperf\Utils\ApplicationContext::getContainer();

        if ($id) return $container->get($id);

        return $container;
    }
}

if (! function_exists('redis')) {
    /**
     * 获取 Redis 协程客户端
     *
     * @param string $poolName 连接池名称
     * @return mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function redis(string $poolName = 'default'): mixed
    {
        return container()->get(\Hyperf\Redis\RedisFactory::class)->get($poolName);
    }
}

if (! function_exists('std_out_log')) {
    /**
     * 控制台日志
     *
     * @return \Hyperf\Contract\StdoutLoggerInterface|mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function std_out_log(): mixed
    {
        return container()->get(\Hyperf\Contract\StdoutLoggerInterface::class);
    }
}

if (! function_exists('logger')) {
    /**
     * 文件日志
     *
     * @param string $name
     * @param string $group
     * @return \Psr\Log\LoggerInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function logger(string $name = 'hyperf', string $group = 'default'): \Psr\Log\LoggerInterface
    {
        return container()->get(\Hyperf\Logger\LoggerFactory::class)->get($name, $group);
    }
}

if (! function_exists('request')) {
    /**
     * request 实例
     *
     * @return \Hyperf\HttpServer\Contract\RequestInterface|mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function request(): mixed
    {
        return container()->get(\Hyperf\HttpServer\Contract\RequestInterface::class);
    }
}

if (! function_exists('response')) {
    /**
     * response 实例
     *
     * @return \Hyperf\HttpServer\Contract\ResponseInterface|mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function response(): mixed
    {
        return container()->get(\Hyperf\HttpServer\Contract\ResponseInterface::class);
    }
}

if (! function_exists('server')) {
    /**
     * 基于 swoole server 的 server 实例
     *
     * @return \Swoole\Coroutine\Server|\Swoole\Server
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function server(): mixed
    {
        return container()->get(\Hyperf\Server\ServerFactory::class)->getServer()->getServer();
    }
}

if (! function_exists('frame')) {
    /**
     * websocket frame 实例
     *
     * @return mixed|\Swoole\Websocket\Frame
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function frame(): mixed
    {
        return container()->get(\Swoole\Websocket\Frame::class);
    }
}

if (! function_exists('websocket')) {
    /**
     * websocket 实例
     *
     * @return mixed|\Swoole\WebSocket\Server
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function websocket(): mixed
    {
        return container()->get(\Swoole\WebSocket\Server::class);
    }
}

if (! function_exists('cache')) {
    /**
     * 简单的缓存实例
     *
     * @return mixed|\Psr\SimpleCache\CacheInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function cache(): mixed
    {
        return container()->get(\Psr\SimpleCache\CacheInterface::class);
    }
}

if (! function_exists('cache_remember')) {
    /**
     * 获取并缓存
     *
     * @param string $key 缓存key
     * @param int $ttl 缓存过期时间，单位：秒（s）。如果为 0 时，则表示永不过期
     * @param callable $callBack 取不到缓存数据时，获取数据的执行闭包
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    function cache_remember(string $key, int $ttl, callable $callBack): mixed
    {
        $value = cache()->get($key);
        if (! is_null($value)) {
            return $value;
        }

        cache()->set($key, $value = $callBack(), $ttl);

        return $value;
    }
}

if (! function_exists('simple_db')) {
    /**
     * 极简 DB
     *
     * @return \Hyperf\DB\DB|mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function simple_db(): mixed
    {
        return container()->get(\Hyperf\DB\DB::class);
    }
}

if (! function_exists('simple_db_debug_sql')) {
    /**
     * 极简 DB 打印 sql 语句
     *
     * @param string $sql 预处理 sql 语句
     * @param array $bindings  绑定参数
     * @param float $executeTime  程序执行时间
     * @return array
     */
    function simple_db_debug_sql(string $sql, array $bindings = [], float $executeTime = 0.0): array
    {
        $executeSql = \Hyperf\Utils\Str::replaceArray('?', $bindings, $sql);
        logger()->info(sprintf('simple db sql debug ==> time：%ss ==> %s', $executeTime, $executeSql));

        $key = config('app.context_key.simple_sql');

        $sqlArr = \Hyperf\Context\Context::get($key, []);
        $sqlArr[] = [
            'query' => $executeSql,
            'code_execute_time' => sprintf('%ss', $executeTime),  // 代码执行时间（不是 sql 执行时间）
        ];

        \Hyperf\Context\Context::set($key, $sqlArr);

        return $sqlArr;
    }
}

if (! function_exists('queue_push')) {
    /**
     * 将任务投递到异步队列中
     *
     * @param \Hyperf\AsyncQueue\JobInterface $job
     * @param int $delay
     * @param string $key
     * @return bool
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function queue_push(\Hyperf\AsyncQueue\JobInterface $job, int $delay = 0, string $key = 'default'): bool
    {
        $driver = container()->get(\Hyperf\AsyncQueue\Driver\DriverFactory::class)->get($key);
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
    function event_dispatch(object $event): object
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
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function format_throwable(Throwable $throwable): string
    {
        return container()->get(\Hyperf\ExceptionHandler\Formatter\FormatterInterface::class)->format($throwable);
    }
}

if (! function_exists('time_milliseconds')) {
    /**
     * 毫秒
     * 1 秒（s）= 1,000 毫秒（ms）
     *
     * @return int
     */
    function time_milliseconds(): int
    {
        return (int)round(microtime(true) * 1000);
    }
}

if (! function_exists('time_microseconds')) {
    /**
     * 微秒
     * 1 秒（s）= 1,000 毫秒（ms）= 1,000,000 微秒（µs）
     *
     * @return int
     */
    function time_microseconds(): int
    {
        return (int)round(microtime(true) * 1000 * 1000);
    }
}

if (! function_exists('time_nanoseconds')) {
    /**
     * 纳秒
     * 1 秒（s）= 1,000 毫秒（ms）= 1,000,000 微秒（µs）= 1,000,000,000 纳秒（ns）
     *
     * @return int
     */
    function time_nanoseconds(): int
    {
        return (int)hrtime(true);
    }
}

if (! function_exists('get_client_ip')) {
    /**
     * 获取客户端 ip
     *
     * @return string
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function get_client_ip(): string
    {
        $request = request();
        return $request->getHeaderLine('X-Forwarded-For')
            ?: $request->getHeaderLine('X-Real-IP')
            ?: ($request->getServerParams()['remote_addr'] ?? '')
            ?: '127.0.0.1';
    }
}

if (! function_exists('get_current_action')) {
    /**
     * 获取当前请求的控制器和方法
     *
     * @return array
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function get_current_action(): array
    {
        $obj = request()->getAttribute(\Hyperf\HttpServer\Router\Dispatched::class);

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

if (! function_exists('route_original')) {
    /**
     * 获取路由地址
     *
     * @param bool $withParams 是否需要补充参数
     * @return string
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function route_original(bool $withParams = false): string
    {
        $obj = request()->getAttribute(\Hyperf\HttpServer\Router\Dispatched::class);

        if (! property_exists($obj, 'handler')
            || ! isset($obj->handler)
            || ! property_exists($obj->handler, 'route')
        ) {
            throw new \Exception('The route is undefined! Please check!');
        }

        if ($withParams) {
            // eg: "/foo/bar/article/detail/123"
            return request()->getPathInfo();
        }

        // eg: "/foo/bar/{hello}/detail/{id:\d+}"
        return $obj->handler->route;
    }
}

if (! function_exists('is_local')) {
    /**
     * 当前环境是否为本地环境
     *
     * @return bool
     */
    function is_local(): bool
    {
        return config('app_env') === 'local';
    }
}

if (! function_exists('is_dev')) {
    /**
     * 当前环境是否为开发环境
     *
     * @return bool
     */
    function is_dev(): bool
    {
        return config('app_env') === 'dev';
    }
}

if (! function_exists('is_test')) {
    /**
     * 当前环境是否为测试环境
     *
     * @return bool
     */
    function is_test(): bool
    {
        return config('app_env') === 'test';
    }
}

if (! function_exists('is_prod')) {
    /**
     * 当前环境是否为生产环境
     *
     * @return bool
     */
    function is_prod(): bool
    {
        return config('app_env') === 'prod';
    }
}

if (! function_exists('set_global_init_params')) {
    /**
     * 重新设置全局初始化参数（在一次请求生命周期中替换掉 App\Middleware\InitParamsMiddleware::class 中的定义）
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    function set_global_init_params(string $key, mixed $value = null): mixed
    {
        $tempValueKey = config('app.context_key.temp_value');
        if (! \Hyperf\Context\Context::has($tempValueKey)) return $value;
        $contextData = \Hyperf\Context\Context::get($tempValueKey, []);

        $override = array_merge($contextData, [
            $key => $value
        ]);

        return \Hyperf\Context\Context::set($tempValueKey, $override);
    }
}

if (! function_exists('get_global_init_params')) {
    /**
     * 获取初始化全局参数 （App\Middleware\InitParamsMiddleware::class 中定义）
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    function get_global_init_params(?string $key = '', mixed $default = null): mixed
    {
        $tempValueKey = config('app.context_key.temp_value');
        if (! \Hyperf\Context\Context::has($tempValueKey)) return $default;
        $contextData = \Hyperf\Context\Context::get($tempValueKey, []);
        if (! $key) return $contextData;
        $value = $contextData[$key] ?? $default;

        return $value;
    }
}

if (! function_exists('prepare_for_page')) {
    /**
     * 拼接分页数据结构
     *
     * @param \Hyperf\Paginator\LengthAwarePaginator $obj  分页数据集
     * @return array
     */
    function prepare_for_page(\Hyperf\Paginator\LengthAwarePaginator $obj): array
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

if (! function_exists('aes_cbc_encrypt')) {
    /**
     * aes cbc 加密
     *
     * @param mixed $plaintext 明文
     * @param string $key 加密 key
     * @param string $iv 向量
     * @return string
     */
    function aes_cbc_encrypt(mixed $plaintext, string $key, string $iv = ''): string
    {
        if ($iv == '') $iv = mb_substr($key, 0, 16);
        $jsonPlaintext = json_encode($plaintext, 256);
        $encrypted = openssl_encrypt($jsonPlaintext, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($encrypted);
    }
}

if (! function_exists('aes_cbc_decrypt')) {
    /**
     * aes cbc 解密
     *
     * @param string $encrypted 密文
     * @param string $key 解密 key
     * @param string $iv 向量
     * @return array
     */
    function aes_cbc_decrypt(string $encrypted, string $key, string $iv = ''): array
    {
        if ($iv == '') $iv = mb_substr($key, 0, 16);
        $str = base64_decode($encrypted);
        $decrypted = openssl_decrypt($str, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
        if (!$decrypted) {
            return [];
        }
        return json_decode($decrypted, true) ?: [];
    }
}

if (! function_exists('csv_export')) {
    /**
     * csv 数据导出
     *
     * @param string $fileName 文件名 eg: "name.csv"
     * @param array $data 要导出的数据 eg: $data = [['name', 'age'], ['alex', 27]];
     * @return \Psr\Http\Message\ResponseInterface|null
     */
    function csv_export(string $fileName, array $data): ?\Psr\Http\Message\ResponseInterface
    {
        if (! $data) return null;
        $str = '';
        foreach ($data as $v) {
            if (! is_array($v)) {
                continue;
            }
            $value = array_map(function ($val) {
                return sprintf('"%s"', $val);
            }, $v);
            $str .= mb_convert_encoding(implode(',', $value), 'GBK', 'UTF-8') . "\n";
        }
        if (! $str) return null;

        return (new \Hyperf\HttpServer\Response())
            ->withHeader('content-type', 'text/csv')
            ->withHeader('content-disposition', "attachment; filename={$fileName}")
            ->withHeader('content-transfer-encoding', 'binary')
            ->withBody(new \Hyperf\HttpMessage\Stream\SwooleStream($str));
    }
}

if (! function_exists('lock_spin')) {
    /**
     * 自旋锁
     *
     * @param callable $callBack 需要触发的回调函数
     * @param string $key 缓存 key（加锁的颗粒度）
     * @param int $counter 尝试触发多少次直至回调函数处理完成
     * @param int $expireTime 缓存时间（实际上是赌定回调函数处理多少秒内可以处理完成）
     * @param int $loopWaitTime 加锁等待时长
     * @return mixed
     * @throws RedisException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function lock_spin(callable $callBack, string $key, int $counter = 10, int $expireTime = 5, int $loopWaitTime = 500000): mixed
    {
        $result = null;
        while ($counter > 0) {
            $val = microtime() . '_' . uniqid('', true);
            $noticeLog = compact('key', 'val', 'expireTime', 'loopWaitTime', 'counter');
            logger()->notice(__FUNCTION__ . ' ====> ', $noticeLog);
            if (redis()->set($key, $val, ['NX', 'EX' => $expireTime])) {
                if (redis()->get($key) === $val) {
                    try {
                        $result = $callBack();
                    } finally {
                        $delKeyLua = 'if redis.call("GET", KEYS[1]) == ARGV[1] then return redis.call("DEL", KEYS[1]) else return 0 end';
                        redis()->eval($delKeyLua, [$key, $val], 1);
                        logger()->notice(__FUNCTION__ . ' delete key ====> ', $noticeLog);
                    }
                    return $result;
                }
            }
            $counter--;
            usleep($loopWaitTime);
        }
        return $result;
    }
}

if (! function_exists('array_same')) {
    /**
     * 检查两个数组元素是否相同（真子集）
     *
     * @param array $arr1 数组1
     * @param array $arr2 数组2
     * @param bool $assoc 是否带索引检查
     * @return bool arr1 和 arr2 中所有的元素都有（arr1 包含 arr2，arr2 也包含 arr1）则为 true，否则为 false
     */
    function array_same(array $arr1, array $arr2, bool $assoc = false): bool
    {
        return $assoc
            ? (! array_diff_assoc($arr1, $arr2) && ! array_diff_assoc($arr2, $arr1))
            : (! array_diff($arr1, $arr2) && ! array_diff($arr2, $arr1));
    }
}
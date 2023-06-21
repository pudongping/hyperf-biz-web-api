<?php

declare(strict_types=1);

use function Hyperf\Support\env;

return [
    'storage' => Pudongping\HyperfThrottleRequests\Storage\RedisStorage::class,
    'maxAttempts' => 60,  // 在指定时间内允许的最大请求次数
    'decaySeconds' => 60,  // 单位时间（单位：s）
    'prefix' => env('THROTTLE_REQUESTS_PREFIX', env('APP_NAME', '') . ':throttle:'),  // 计数器 key 前缀，默认为：`throttle:`
    'key' => '',  // 具体的计数器的 key
    'generateKeyCallable' => function () {
        $sign = request()->url() . '|' . get_client_ip();
        if ($user = auth()) {
            $sign .= '|' . ($user['user_id'] ?? '');
        }
        return sha1($sign);
    },  // 生成计数器 key 的方法
    'tooManyAttemptsCallback' => []  // 当触发到最大请求次数时的回调方法
];
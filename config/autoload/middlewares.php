<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
return [
    'http' => [
        \App\Middleware\CorsMiddleware::class,
        \App\Middleware\InitParamsMiddleware::class,
        \App\Middleware\LogInfoMiddleware::class,  // 记录客户端请求 api 时所有的参数
        \App\Middleware\AuthMiddleware::class,
        \Hyperf\Validation\Middleware\ValidationMiddleware::class,
    ],
];

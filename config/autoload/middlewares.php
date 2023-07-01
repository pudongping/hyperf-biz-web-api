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
        \Pudongping\HyperfKit\Middleware\CorsMiddleware::class,
        \Pudongping\HyperfKit\Middleware\InitParamsMiddleware::class,
        \Pudongping\HyperfKit\Middleware\LogInfoMiddleware::class,  // 记录客户端请求 api 时所有的参数
        \Hyperf\Validation\Middleware\ValidationMiddleware::class,
    ],
];

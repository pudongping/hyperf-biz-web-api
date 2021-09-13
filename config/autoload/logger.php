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
    'default' => [
        'handler' => [
            // 'class' => Monolog\Handler\StreamHandler::class,
            'class' => Monolog\Handler\RotatingFileHandler::class,  // 日志文件按照日期轮转
            'constructor' => [
                // 'stream' => BASE_PATH . '/runtime/logs/hyperf.log',
                'filename' => BASE_PATH . '/runtime/logs/hyperf.log',
                'maxFiles' => 14,  // 最多只记录 14 天前的日志
                'level' => Monolog\Logger::DEBUG,
            ],
        ],
        'formatter' => [
            'class' => Monolog\Formatter\LineFormatter::class,
            'constructor' => [
                'format' => null,
                'dateFormat' => 'Y-m-d H:i:s',
                'allowInlineLineBreaks' => true,
            ],
        ],
    ],
];

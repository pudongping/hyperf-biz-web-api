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
                'maxFiles' => 7,  // 最多只记录 7 天前的日志
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
        'processors' => [
            [
                'class' => \Pudongping\HyperfKit\Kernel\Log\AppendRequestIdProcessor::class
            ],
            [
                // @see https://github.com/Seldaek/monolog/blob/main/src/Monolog/Processor/MemoryUsageProcessor.php
                'class' => Monolog\Processor\MemoryUsageProcessor::class
            ]
        ]
    ],
];

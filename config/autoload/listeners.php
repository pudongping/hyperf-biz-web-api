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
    Hyperf\AsyncQueue\Listener\QueueLengthListener::class,  // 记录队列长度的监听器
    Hyperf\ExceptionHandler\Listener\ErrorExceptionHandler::class,  // 监听 error_reporting() 错误级别的监听器
    Hyperf\Command\Listener\FailToHandleListener::class,  // 监听命令行失败监听器
];

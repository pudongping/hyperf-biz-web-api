<?php
/**
 * @see document link  https://github.com/Seldaek/monolog/blob/main/doc/02-handlers-formatters-processors.md#processors
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2022-09-28 11:50
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

namespace App\Kernel\Log;

use Hyperf\Utils\Coroutine;
use Monolog\Processor\ProcessorInterface;
use Hyperf\Context\Context;
use Monolog\LogRecord;

class AppendRequestIdProcessor implements ProcessorInterface
{
    public const REQUEST_ID = 'request_id';

    public function __invoke(array|LogRecord $record)
    {
        $record['extra']['request_id'] = Context::getOrSet(self::REQUEST_ID, uniqid());
        $record['extra']['coroutine_id'] = Coroutine::id();
        $record['extra']['parent_coroutine_id'] = Coroutine::parentId();
        return $record;
    }
}
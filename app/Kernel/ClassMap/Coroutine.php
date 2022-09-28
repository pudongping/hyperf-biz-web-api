<?php
/**
 *
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2022-09-28 11:29
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

namespace Hyperf\Utils;

use App\Kernel\Context\Coroutine as Co;
use Swoole\Coroutine as SwooleCoroutine;
use Hyperf\Utils\ApplicationContext;

/**
 * @method static void defer(callable $callable)
 */
class Coroutine
{
    public static function __callStatic($name, $arguments)
    {
        if (! method_exists(SwooleCoroutine::class, $name)) {
            throw new \BadMethodCallException(sprintf('Call to undefined method %s.', $name));
        }
        return SwooleCoroutine::$name(...$arguments);
    }

    /**
     * Returns the current coroutine ID.
     * Returns -1 when running in non-coroutine context.
     */
    public static function id(): int
    {
        return SwooleCoroutine::getCid();
    }

    /**
     * Returns the parent coroutine ID.
     * Returns -1 when running in the top level coroutine.
     * Returns null when running in non-coroutine context.
     *
     * @see https://github.com/swoole/swoole-src/pull/2669/files#diff-3bdf726b0ac53be7e274b60d59e6ec80R940
     */
    public static function parentId(?int $coroutineId = null): ?int
    {
        if ($coroutineId) {
            $cid = SwooleCoroutine::getPcid($coroutineId);
        } else {
            $cid = SwooleCoroutine::getPcid();
        }
        if ($cid === false) {
            return null;
        }

        return $cid;
    }

    /**
     * @return int Returns the coroutine ID of the coroutine just created.
     *             Returns -1 when coroutine create failed.
     */
    public static function create(callable $callable): int
    {
        return ApplicationContext::getContainer()->get(Co::class)->create($callable);
    }

    public static function inCoroutine(): bool
    {
        return Coroutine::id() > 0;
    }
}

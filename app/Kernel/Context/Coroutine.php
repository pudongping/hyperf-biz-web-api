<?php
/**
 *
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2022-09-28 11:28
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

namespace App\Kernel\Context;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\Utils;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swoole\Coroutine as SwooleCoroutine;
use App\Kernel\Log\AppendRequestIdProcessor;

class Coroutine
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var null|FormatterInterface
     */
    protected $formatter;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->logger = $container->get(StdoutLoggerInterface::class);
        if ($container->has(FormatterInterface::class)) {
            $this->formatter = $container->get(FormatterInterface::class);
        }
    }

    /**
     * @return int Returns the coroutine ID of the coroutine just created.
     *             Returns -1 when coroutine create failed.
     */
    public function create(callable $callable): int
    {
        $id = Utils\Coroutine::id();
        $result = SwooleCoroutine::create(function () use ($callable, $id) {
            try {
                // 按需复制，禁止复制 Socket，不然会导致 Socket 跨协程调用从而报错
                Utils\Context::copy($id, [
                    AppendRequestIdProcessor::REQUEST_ID,
                    ServerRequestInterface::class,
                ]);
                call($callable);
            } catch (\Throwable $throwable) {
                if ($this->formatter) {
                    $this->logger->warning($this->formatter->format($throwable));
                } else {
                    $this->logger->warning((string) $throwable);
                }
            }
        });
        return is_int($result) ? $result : -1;
    }
}

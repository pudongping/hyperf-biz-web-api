<?php
/**
 * 节流处理
 * 用途：限制访问频率
 * 做法：限制单位时间内访问指定服务/路由的次数（频率）
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2022-06-07 15:53
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

namespace App\Helper;

use Carbon\Carbon;
use Psr\Http\Message\ResponseInterface;
use Hyperf\Utils\Context;
use App\Constants\ErrorCode;
use App\Exception\ApiException;

class ThrottleRequestsHelper
{

    /**
     * 用于做计时器的缓存 key 后缀
     *
     * @var string
     */
    protected $keySuffix = ':timer';

    /**
     * 处理节流
     *
     * @param int $maxAttempts  在指定时间内允许的最大请求次数
     * @param int $decaySeconds  单位时间（s）
     * @param string $prefix  计数器缓存 key 前缀
     * @param string $key 计数器缓存 key
     */
    public function handle(int $maxAttempts = 60, int $decaySeconds = 60, string $prefix = 'throttle', string $key = '')
    {
        if (! $key) {
            $key = $this->resolveRequestSignature();
        }
        $cacheKey = $prefix . ':' . $key;

        // 单位时间内已经超过了访问次数时
        if ($this->tooManyAttempts($cacheKey, $maxAttempts)) {
            throw $this->buildException($cacheKey, $maxAttempts);
        }

        $this->hit($cacheKey, $decaySeconds);

        $this->setHeaders($cacheKey, $maxAttempts);
    }

    /**
     * 生成缓存 key
     *
     * @return string
     */
    public function resolveRequestSignature(): string
    {
        $str = request()->url() . '|' . get_client_ip();
        if ($user = auth()) {
            $str .= '|' . (string)$user['user_id'];
        }

        return sha1($str);
    }

    /**
     * 在指定时间内增加指定键的计数器
     *
     * @param string $key  计数器的缓存key
     * @param int $decaySeconds  指定时间（S）
     * @return int  计数器具体增加到多少值
     */
    private function hit(string $key, int $decaySeconds = 60): int
    {
        $timerKey = $key . $this->keySuffix;

        // 计时器的有效期时间戳
        $expirationTime = Carbon::now()->addRealSeconds($decaySeconds)->getTimestamp();
        // 计时器
        redis()->set($timerKey, $decaySeconds, ['NX', 'EX' => $expirationTime]);

        // 计数器
        return redis()->incr($key);  // 返回增加到多少的具体数字
    }

    /**
     * 判断访问次数是否已经达到了临界值
     *
     * @param string $key  计数器的缓存 key
     * @param int $maxAttempts  在指定时间内允许的最大请求次数
     * @return bool
     */
    private function tooManyAttempts(string $key, int $maxAttempts): bool
    {
        // 获取计数器的值，如果计数器不存在，则初始化计数器的值为 0
        // 获取一个不存在的键时，会直接返回 false
        $counterNumber = redis()->get($key) ?: 0;

        // 判断计时器是否存在，如果计时器不存在，则对应的计数器没有存在的意义（存在过多的计数器会占用 redis 空间）
        if (! redis()->exists($key . $this->keySuffix)) {  // 存在缓存 key 时返回 int => 1，不存在时返回 int => 0
            // 有该键名且删除成功返回 int => 1，无该键名时返回 int => 0
            redis()->del($key);  // 删除计数器缓存，防止计时器失效后，下一次用户访问时不是从 1 开始计数
        } else {
            if ($counterNumber >= $maxAttempts) {  // 判断计数器在单位时间内是否达到了临界值
                return true;
            }
        }

        return false;
    }

    /**
     * 超过访问次数限制时，构建异常信息
     *
     * @param string $key  计数器的缓存 key
     * @param int $maxAttempts  在指定时间内允许的最大请求次数
     * @return ApiException
     */
    protected function buildException(string $key, int $maxAttempts)
    {
        // 距离允许下一次请求还有多少秒
        $retryAfter = $this->getTimeUntilNextRetry($key);

        $this->setHeaders($key, $maxAttempts, $retryAfter);

        // 429 Too Many Requests
        return new ApiException(ErrorCode::REQUEST_FREQUENTLY);
    }

    /**
     * 设置返回头数据
     *
     * @param string $key  计数器的缓存key
     * @param int $maxAttempts  在指定时间内允许的最大请求次数
     * @param int|null $retryAfter  距离下次重试请求需要等待的时间（s）
     */
    protected function setHeaders(string $key, int $maxAttempts, ?int $retryAfter = null)
    {
        // 设置返回头数据
        $headers = $this->getHeaders(
            $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts, $retryAfter),  // 计算剩余访问次数
            $retryAfter
        );

        // 添加返回头数据到请求头中
        $this->addHeaders($headers);
    }

    /**
     * 获取返回头数据
     *
     * @param int $maxAttempts  在指定时间内允许的最大请求次数
     * @param int $remainingAttempts  在指定时间段内剩下的请求次数
     * @param int|null $retryAfter  距离下次重试请求需要等待的时间（s）
     * @return int[]
     */
    protected function getHeaders(int $maxAttempts, int $remainingAttempts, ?int $retryAfter = null)
    {
        $headers = [
            'X-RateLimit-Limit' => $maxAttempts,  // 在指定时间内允许的最大请求次数
            'X-RateLimit-Remaining' => $remainingAttempts,  // 在指定时间段内剩下的请求次数
        ];

        if (! is_null($retryAfter)) {  // 只有当用户访问频次超过了最大频次之后才会返回以下两个返回头字段
            $headers['Retry-After'] = $retryAfter;  // 距离下次重试请求需要等待的时间（s）
            $headers['X-RateLimit-Reset'] = Carbon::now()->addRealSeconds($retryAfter)->getTimestamp();  // 距离下次重试请求需要等待的时间戳（s）
        }

        return $headers;
    }

    /**
     * 添加请求头数据
     *
     * @param array $headers
     */
    protected function addHeaders(array $headers = [])
    {
        $response = Context::get(ResponseInterface::class);

        foreach ($headers as $key => $header) {
            $response = $response->withHeader($key, $header);
        }

        Context::set(ResponseInterface::class, $response);
    }

    /**
     * 计算距离允许下一次请求还有多少秒
     *
     * @param string $key
     * @return false|int|mixed|string
     */
    private function getTimeUntilNextRetry(string $key)
    {
        // 在 $this->tooManyAttempts() 方法中已经判断了计时器的缓存 key 是否存在，因此在这里毋需再次累赘判断
        // 计时器的有效期减去当前时间戳
        return redis()->get($key . $this->keySuffix) - Carbon::now()->getTimestamp();
    }

    /**
     * 计算剩余访问次数
     *
     * @param string $key 计数器的缓存key
     * @param int $maxAttempts  在指定时间内允许的最大请求次数
     * @param int|null $retryAfter  距离下次重试请求需要等待的时间（s）
     * @return false|int|mixed|string
     */
    private function calculateRemainingAttempts(string $key, int $maxAttempts, ?int $retryAfter = null)
    {
        if (is_null($retryAfter)) {
            // 获取一个不存在的键时，会直接返回 false
            $counterNumber = redis()->get($key) ?: 0;
            return $maxAttempts - $counterNumber;  // 剩余访问次数
        }

        return 0;
    }

}
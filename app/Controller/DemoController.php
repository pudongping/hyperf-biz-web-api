<?php
/**
 *
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2023-07-01 23:45
 */
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Annotation\AutoController;
use Pudongping\HyperfKit\Exception\ApiException;
use App\Constants\ErrorCode;
use Hyperf\Context\Context;
use Pudongping\SmartAssist\TimeHelper;
use Psr\Http\Message\ServerRequestInterface;
use Pudongping\HyperfKit\Kernel\Log\AppendRequestIdProcessor;

#[AutoController]
class DemoController extends AbstractController
{

    public function t1()
    {
        $params = request()->all();
        return $this->send($params);
    }

    public function t2()
    {
        throw new ApiException(ErrorCode::ERR_HTTP_UNPROCESSABLE_ENTITY);
        return $this->send();
    }

    public function t3()
    {
        $params = [
            'tt' => TimeHelper::Microseconds()
        ];

        $key = 'alex';  // 没有被协程上下文复制，因此每一次协程切换时，都是一次崭新的开始
        // Psr\Http\Message\ServerRequestInterface 和 Pudongping\HyperfKit\Kernel\Log\AppendRequestIdProcessor::REQUEST_ID
        // 被自动复制，因此在一次协程请求生命周期都一样
        Context::override(ServerRequestInterface::class, function () use ($key, $params) {
            return request()->withAttribute($key, $params);
        });

        Context::set($key, $params);
        $reqId = Context::getOrSet(AppendRequestIdProcessor::REQUEST_ID, TimeHelper::Microseconds());

        $result = [
            'alike' => [],
            'unlikeness' => [],
            'req_id' => [],
            'req_id_wrap' => $reqId
        ];
        go(function () use ($key, &$result) {

            $a = Context::getOrSet($key, TimeHelper::Microseconds());
            $aa = request()->getAttribute($key);
            dump(1111);
            $result['unlikeness']['a'] = $a;
            $result['alike']['a'] = $aa;
            $result['req_id']['a'] = Context::getOrSet(AppendRequestIdProcessor::REQUEST_ID, TimeHelper::Microseconds());

            go(function () use ($key, &$result) {

                $b = Context::getOrSet($key, TimeHelper::Microseconds());
                $bb = request()->getAttribute($key);
                dump(2222);
                $result['unlikeness']['b'] = $b;
                $result['alike']['b'] = $bb;
                $result['req_id']['b'] = Context::getOrSet(AppendRequestIdProcessor::REQUEST_ID, TimeHelper::Microseconds());

                go(function () use ($key, &$result) {

                    $c = Context::getOrSet($key, TimeHelper::Microseconds());
                    $cc = request()->getAttribute($key);
                    dump(3333);
                    $result['unlikeness']['c'] = $c;
                    $result['alike']['c'] = $cc;
                    $result['req_id']['c'] = Context::getOrSet(AppendRequestIdProcessor::REQUEST_ID, TimeHelper::Microseconds());

                });

            });

        });

        usleep(1000 * 200);

        return $this->send($result);
    }

}
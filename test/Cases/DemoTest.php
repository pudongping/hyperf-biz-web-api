<?php
/**
 *
 *
 * Created by PhpStorm
 * User: Alex
<<<<<<< HEAD
 * Date: 2023-07-01 12:51
=======
 * Date: 2023-07-02 00:03
>>>>>>> 框架轻量化（基于 hyperf 3.0）
 */
declare(strict_types=1);

namespace HyperfTest\Cases;

use HyperfTest\HttpTestCase;
use Pudongping\HyperfKit\Helper\GuzzleHttpHelper;

/**
 * @internal
 * @coversNothing
 */
class DemoTest extends HttpTestCase
{

    public function testT1()
    {
        $res = $this->post('/demo/t1', [
            'name' => 'alex',
            'age' => 18
        ]);

        $this->assertSame(0, $res['code']);
        $this->assertSame('请求成功', $res['msg']);
        $this->assertSame('alex', $res['data']['name']);
        $this->assertSame(18, $res['data']['age']);
    }

    public function testT2()
    {
        $res = $this->get('/demo/t2');
        $this->assertSame(422, $res['code']);
        $this->assertSame('参数校验错误', $res['msg']);
    }

    // composer test -- --filter=testT3
    public function testT3()
    {
        $res = $this->get('/demo/t3');

        $this->assertSame(0, $res['code']);
        $this->assertSame('请求成功', $res['msg']);

        $alike = array_column(array_values($res['data']['alike']), 'tt');

        // request 上下文会被自动复制，因此都一样
        $this->assertEquals(1, count(array_unique($alike)));

        // 相反，每一次 coroutine 都会重新创建，因此不一样
        $unlikeness = array_values($res['data']['unlikeness']);
        $this->assertEquals(3, count(array_unique($unlikeness)));

        $reqId = array_values($res['data']['req_id']);
        $this->assertEquals(1, count(array_unique($reqId)));
        $this->assertEquals($res['data']['req_id_wrap'], $reqId[0]);
    }

    // composer test -- --filter=testGuzzleHttpHelper
    public function testGuzzleHttpHelper()
    {
        $client = container()->get(GuzzleHttpHelper::class);

        $get = $client->get('http://httpbin.org/get', [
            'body' => [
                'method' => 'get'
            ],
            'headers' => [
                'accept' => 'application/json'
            ]
        ]);

        $this->assertTrue(is_array($get));
        $this->assertSame('get', $get['args']['method']);
    }

}
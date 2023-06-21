<?php
/**
 * English document link : https://docs.guzzlephp.org/en/stable/
 * zh-CN document link : https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2021-08-30 20:57
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

namespace App\Helper;

use Hyperf\Guzzle\ClientFactory;

class GuzzleHttpHelper
{

    protected $clientFactory;

    public function __construct(ClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
    }

    public function get(string $url, array $params = [])
    {
        $arr = [
            'headers' => $params['headers'] ?? [],
            'query' => [],
            'http_errors' => false,  // 支持错误输出
        ];

        if (isset($params['body'])) {
            $arr['query'] = $params['body'];
            unset($params['body']);
        }

        return $this->response('GET', $url, array_merge($arr, $params));
    }

    public function post(string $url, array $params = [])
    {
        $arr = [
            'headers' => $params['headers'] ?? [],
            'form_params' => [],
            'http_errors' => false,  // 支持错误输出
        ];

        if (isset($params['body'])) {
            $arr['form_params'] = $params['body'];
            unset($params['body']);
        }

        return $this->response('POST', $url, array_merge($arr, $params));
    }

    public function upload(string $url, string $filePath)
    {
        $arr = [
            'multipart' => [
                [
                    'name' => 'file_name',
                    'contents' => fopen($filePath, 'r')
                ],
            ],
        ];

        return $this->response('POST', $url, $arr);
    }

    public function put(string $url, array $params = [])
    {
        $arr = [
            'headers' => $params['headers'] ?? [],
            'json' => [],
            'http_errors' => false,  // 支持错误输出
        ];

        if (isset($params['body'])) {
            $arr['json'] = $params['body'];
            unset($params['body']);
        }

        return $this->response('PUT', $url, array_merge($arr, $params));
    }

    public function delete(string $url, array $params = [])
    {
        $arr = [
            'headers' => $params['headers'] ?? [],
            'json' => [],
            'http_errors' => false,  // 支持错误输出
        ];

        if (isset($params['body'])) {
            $arr['json'] = $params['body'];
            unset($params['body']);
        }

        return $this->response('DELETE', $url, array_merge($arr, $params));
    }

    public function response($method, $url, $args): array
    {
        $enable = config('app.log_guzzle_enable');
        $enable && logger()->info(sprintf("此时为 %s 请求，请求地址为 ====> %s 参数为 ====> %s", $method, $url, var_export($args, true)));
        $client = $this->clientFactory->create();
        $response = $client->request($method, $url, $args);
        $contents = $response->getBody()->getContents();
        $enable && logger()->info(sprintf("请求返回的结果为  ====> %s ", var_export($contents, true)));
        return json_decode($contents, true);
    }

}
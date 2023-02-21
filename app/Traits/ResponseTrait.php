<?php
/**
 *
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2021-08-30 15:49
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

namespace App\Traits;

use App\Constants\ErrorCode;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Pudongping\HyperfKit\Traits\ResponseTrait as HyperfKitResponseTrait;

trait ResponseTrait
{

    use HyperfKitResponseTrait;

    /**
     * 正确时返回
     *
     * @param mixed $data 返回数据
     * @return PsrResponseInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    final public function send(mixed $data): PsrResponseInterface
    {
        $ret['code'] = ErrorCode::SUCCESS;
        $ret['msg'] = ErrorCode::getMessage(ErrorCode::SUCCESS);
        $ret['data'] = $this->parseData($data);

        if ($sql = $this->showSql()) {
            $ret['query'] = $sql;
        }

        return response()->json($ret);
    }

}
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
use Pudongping\HyperfKit\Traits\ResponseTrait as HyperfKitResponseTrait;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

trait ResponseTrait
{

    use HyperfKitResponseTrait;

    final public function send($data): PsrResponseInterface
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
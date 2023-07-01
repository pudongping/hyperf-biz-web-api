<?php

declare(strict_types=1);

namespace App\Constants;

use Hyperf\Constants\Annotation\Constants;
use Pudongping\HyperfKit\Constants\ErrorCode as HyperfKitErrorCode;

#[Constants]
class ErrorCode extends HyperfKitErrorCode
{

    // ========= http 状态码 ==========

    /**
     * @Message("请求成功")
     */
    public const SUCCESS = 0;

    /**
     * @Message("登录已过期，请重新登录")
     */
    public const ERR_HTTP_UNAUTHORIZED = 1001;  // 因自身项目当初设定的就为 1001，因此这里默认为 1001，新项目可自定义为 401
    // public const ERR_HTTP_UNAUTHORIZED = 401;

}

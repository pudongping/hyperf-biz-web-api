<?php

declare(strict_types=1);

namespace App\Constants;

use Hyperf\Constants\Annotation\Constants;
use Pudongping\HyperfKit\Constants\ErrorCode as HyperfKitErrorCode;

/**
 * @Constants
 */
class ErrorCode extends HyperfKitErrorCode
{

    // ========= http 状态码 ==========

    /**
     * @Message("请求成功")
     */
    const SUCCESS = 0;

}

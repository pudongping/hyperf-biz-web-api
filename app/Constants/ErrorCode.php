<?php

declare(strict_types=1);

namespace App\Constants;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * @Constants
 */
class ErrorCode extends AbstractConstants
{

    // ========= http 状态码 ==========

    /**
     * @Message("请求成功")
     */
    const SUCCESS = 200;

    /**
     * @Message("请求异常")
     */
    const ERROR = 400;

    /**
     * @Message("登录已过期，请重新登录")
     */
    const ERR_HTTP_UNAUTHORIZED = 401;

    /**
     * @Message("无权操作")
     */
    const NO_AUTH = 403;

    /**
     * @Message("未查询到")
     */
    const NOT_FOUND = 404;

    /**
     * @Message("不允许请求该方法")
     */
    const ERR_HTTP_METHOD_NOT_ALLOWED = 405;

    /**
     * @Message("请求体类型错误")
     */
    const ERR_HTTP_UNSUPPORTED_MEDIA_TYPE = 415;

    /**
     * @Message("参数校验错误")
     */
    const ERR_HTTP_UNPROCESSABLE_ENTITY = 422;

    /**
     * @Message("请求频次达到上限")
     */
    const REQUEST_FREQUENTLY = 429;

    /**
     * @Message("服务器内部错误")
     */
    const SERVER_ERROR = 500;

    // ========= 系统业务状态码 10000 起 ==========

    /**
     * @Message("参数缺失")
     */
    const PARAM_MISSING = 10000;

    /**
     * @Message("参数错误")
     */
    const PARAM_ERROR = 10001;

    /**
     * @Message("数据库操作失败")
     */
    const ERR_QUERY = 10002;

    /**
     * @Message("数据库连接失败")
     */
    const ERR_DB = 10003;

    /**
     * @Message("数据不存在")
     */
    const ERR_MODEL = 10004;

}

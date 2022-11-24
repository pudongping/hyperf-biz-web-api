<?php
/**
 *
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2021-08-30 15:45
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

namespace App\Exception\Handler;

use Hyperf\ExceptionHandler\ExceptionHandler;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Hyperf\Validation\ValidationException;
use Hyperf\Database\Exception\QueryException;
use Hyperf\Database\Model\ModelNotFoundException;
use App\Traits\ResponseTrait;
use App\Constants\ErrorCode;
use App\Exception\ApiException;
use Hyperf\HttpMessage\Exception\NotFoundHttpException;

class ApiExceptionHandler extends ExceptionHandler
{

    use ResponseTrait;

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $code = ErrorCode::SERVER_ERROR;
        $msg = '';

        switch (true) {
            case $throwable instanceof ValidationException:
                $code = ErrorCode::PARAM_MISSING;
                $msg = $throwable->validator->errors()->first();
                break;
            case $throwable instanceof QueryException:
                $code = ErrorCode::ERR_QUERY;
                break;
            case $throwable instanceof \PDOException:
                $code = ErrorCode::ERR_DB;
                break;
            case $throwable instanceof ModelNotFoundException:
                $code = ErrorCode::ERR_MODEL;
                break;
            case $throwable instanceof NotFoundHttpException:
                $code = ErrorCode::NOT_FOUND;
                $msg = '路由未定义或不支持当前请求';
                break;
            case $throwable instanceof ApiException:
                $code = $throwable->getCode() ?: ErrorCode::SERVER_ERROR;
                $msg = ErrorCode::SERVER_ERROR == $code ? ErrorCode::getMessage($code) : $throwable->getMessage();
                break;
        }

        $msg = $msg ?: ErrorCode::getMessage($code) ?: 'Whoops, No Error Data';

        // 阻止异常冒泡
        $this->stopPropagation();

        logger()->error(format_throwable($throwable));  // 记录错误日志

        return $this->fail($code, [], $msg);
    }

    /**
     * 判断该异常处理器是否要对该异常进行处理
     *
     * @param Throwable $throwable 抛出的异常
     * @return bool  该异常处理器是否处理该异常
     */
    public function isValid(Throwable $throwable): bool
    {
        return true;
    }

}
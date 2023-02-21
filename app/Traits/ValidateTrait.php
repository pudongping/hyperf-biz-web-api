<?php
/**
 *
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2022-03-03 16:35
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

namespace App\Traits;

use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Di\Annotation\Inject;
use App\Constants\ErrorCode;
use App\Exception\ApiException;

trait ValidateTrait
{

    #[Inject]
    protected ValidatorFactoryInterface $validationFactory;

    /**
     * 自动加载验证器验证参数
     *
     * @param array $args 所需要验证的原始数据
     * @param array $rules 验证规则
     * @param array $messages 错误提示
     * @param array $customAttributes 验证规则字段属性
     * @param string $reqPath 所需要加载的验证器
     * @return array 验证通过后的数据
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    final public function validate(array $args = [], array $rules = [], array $messages = [], array $customAttributes = [], string $reqPath = ''): array
    {
        $defaultValidator = get_current_action()['controller'];
        $defaultValidator = str_replace('Controller', 'Request', $defaultValidator);

        $reqPath = $reqPath ?: $defaultValidator;

        try {
            $request = make($reqPath);
        } catch (\Throwable $e) {
            logger()->error(format_throwable($e));
            throw new ApiException(ErrorCode::SERVER_ERROR, sprintf('[ %s.php ] 文件不存在！', $reqPath));
        }

        $args = $args ?: request()->all();
        $rules = $rules ?: (method_exists($request, 'rules') ? $request->rules() : []);
        $messages = $messages ?: (method_exists($request, 'messages') ? $request->messages() : []);
        $customAttributes = $customAttributes ?: (method_exists($request, 'attributes') ? $request->attributes() : []);

        $validator = $this->validationFactory->make($args, $rules, $messages, $customAttributes);

        if ($validator->fails()) {
            throw new ApiException(ErrorCode::ERR_HTTP_UNPROCESSABLE_ENTITY, $validator->errors()->first());
        }

        return $validator->validated();
    }

}
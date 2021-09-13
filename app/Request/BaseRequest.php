<?php
/**
 * 基础验证器
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2021-08-30 19:33
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

namespace App\Request;

use Hyperf\Validation\Request\FormRequest;

class BaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        $rules = [

        ];

        return array_merge(parent::messages(), $rules);
    }

    /**
     * 根据请求方法自动切换验证规则
     *
     * @param array $rules  设定的验证规则
     * @return array  当前请求方法所需要的验证规则
     * @throws \Exception
     */
    public function useRule(array $rules): array
    {
        $method = get_current_action()['method'];

        if ($method && array_key_exists($method, $rules)) {
            return $rules[$method];
        }

        return [];
    }
}
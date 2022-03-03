<?php
/**
 * 业务相关助手函数
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2022-03-03 16:19
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

if (! function_exists('auth')) {
    /**
     * 获取用户信息
     *
     * @return array|null
     */
    function auth(): ?array
    {
        return request()->getAttribute('userInfo');
    }
}
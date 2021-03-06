<?php
/**
 * 应用自定义配置文件
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2021-08-29 21:49
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

return [

    /**
     * app debug mode
     */
    'debug' => env('APP_DEBUG', false),

    /**
     * 默认每页显示 20 条数据
     */
    'default_per_page' => env('PER_PAGE', 20),

    /**
     * 请求日志是否格式化输出
     */
    'log_format_human' => env('LOG_FORMAT_HUMAN', false),

    /**
     * 协程上下文的 key
     */
    'context_key' => [
        'temp_value' => 'temp_value',  // 一次请求周期中临时保存的全局数据
        'simple_sql' => 'simple_sql',  // 一次请求周期中执行的极简 DB sql 语句
    ],

];
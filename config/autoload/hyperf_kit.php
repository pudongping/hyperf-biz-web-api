<?php
/**
 *
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2023-06-26 12:08
 */
declare(strict_types=1);

use function Hyperf\Support\env;

return [

    /**
     * app debug mode
     */
    'debug' => env('APP_DEBUG', false),

    /**
     * 默认每页显示 20 条数据
     */
    'default_per_page' => env('PER_PAGE', 20),

    'log' => [
        /**
         * 请求日志是否格式化输出
         */
        'format_human' => env('LOG_FORMAT_HUMAN', false),

        /**
         * 是否仅记录请求日志
         */
        'request_only' => env('LOG_REQUEST_ONLY', true),

        /**
         * 是否仅记录返回日志
         */
        'response_only' => env('LOG_RESPONSE_ONLY', true),

        /**
         * 是否开启 guzzle 请求日志
         */
        'guzzle_enable' => env('LOG_GUZZLE_ENABLE', true),

        /**
         * 系统发生异常时，是否记录日志
         */
        'exception' => env('LOG_EXCEPTION', true),

        /**
         * 代码执行耗时时间超过多少时，请求返回日志记录由 info 级别改为 warning 级别
         * 单位：秒
         * 默认值为：5s
         */
        'code_run_cost_timeout' => env('LOG_CODE_RUN_COST_TIMEOUT', 5),
    ],

    /**
     * 协程上下文的 key
     */
    'context_key' => [
        'temp_value' => 'context_key.temp_value',  // 一次请求周期中临时保存的全局数据
        'simple_sql' => 'context_key.simple_sql',  // 一次请求周期中执行的极简 DB sql 语句
    ],

];
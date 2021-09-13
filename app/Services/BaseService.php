<?php
/**
 *
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2021-08-30 19:36
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

namespace App\Services;

abstract class BaseService
{

    /**
     * 使用分页
     *
     * @param $model 模型实例
     * @param string $sortColumn 排序字段
     * @param string $sort 排序规则 desc|asc
     * @return mixed 数据集
     */
    public function usePage($model, $sortColumn = 'id', $sort = 'desc')
    {
        $defaultPerPage = config('app.default_per_page');
        $isShowPage = get_global_init_params('is_show_page', false);
        $orderBy = get_global_init_params('order_by', '');
        $perPage = get_global_init_params('per_page', $defaultPerPage);
        $page = get_global_init_params('page', 1);

        $number = ($perPage > 0) ? $perPage : $defaultPerPage;  // 防止 $perPage 为负数

        if (! empty($orderBy)) {
            // 支持 $tempValue['order_by'] = id,desc|name,asc
            $order = explode('|', $orderBy);
            foreach ($order as $value) {
                if (! empty($value)) {
                    [$sortColumn, $sort] = explode(',', $value);
                    $model = $model->orderBy($sortColumn, $sort);
                }
            }
        } elseif ($sortColumn && $sort) {
            if (is_array($sortColumn) && is_array($sort)) {
                // 支持 $sortColumn = ['id','name'] , $sort = ['desc','asc']
                foreach ($sortColumn as $k => $col) {
                    $rank = array_key_exists($k,$sort) ? $sort[$k] : 'desc';
                    $model = $model->orderBy($col, $rank);
                }
            } else {
                $model = $model->orderBy($sortColumn, $sort);
            }
        }

        return $isShowPage ? $model->paginate($number, ['*'], 'page', $page) : $model->get();
    }

    /**
     * 生成开始时间和结束时间的搜索条件
     *
     * @param string $defaultBegin  默认开始时间
     * @param string $defaultEnd  默认结束时间
     * @return array|false  开始时间结束时间数组
     */
    public function searchTime(string $defaultBegin = '', string $defaultEnd = '')
    {
        $begin = request()->input('begin', $defaultBegin);
        if (! empty($begin) && empty($defaultEnd)) {
            $defaultEnd = date('Y-m-d H:i:s');
        }
        $end = request()->input('end', $defaultEnd);
        if (! strtotime($begin) || ! strtotime($end)) {
            return false;
        }
        return [$begin, $end];
    }

}
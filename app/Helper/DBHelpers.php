<?php
/**
 * 数据库相关助手函数
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2023-03-10 14:24
 */
declare(strict_types=1);

if (! function_exists('batch_update')) {
    /**
     * $where = [ 'id' => [180, 181, 182, 183], 'user_id' => [5, 15, 11, 1]];
     * $needUpdateFields = [ 'view_count' => [11, 22, 33, 44], 'updated_at' => ['2019-11-06 06:44:58', '2019-11-30 19:59:34', '2019-11-05 11:58:41', '2019-12-13 01:27:59']];.
     *
     * 最终执行的 sql 语句如下所示
     *
     * UPDATE articles SET
     * view_count = CASE
     * WHEN id = 183 AND user_id = 1 THEN 44
     * WHEN id = 182 AND user_id = 11 THEN 33
     * WHEN id = 181 AND user_id = 15 THEN 22
     * WHEN id = 180 AND user_id = 5 THEN 11
     * ELSE view_count END,
     * updated_at = CASE
     * WHEN id = 183 AND user_id = 1 THEN '2019-12-13 01:27:59'
     * WHEN id = 182 AND user_id = 11 THEN '2019-11-05 11:58:41'
     * WHEN id = 181 AND user_id = 15 THEN '2019-11-30 19:59:34'
     * WHEN id = 180 AND user_id = 5 THEN '2019-11-06 06:44:58'
     * ELSE updated_at END
     *
     *
     * 批量更新数据
     *
     * @param string $tableName 需要更新的表名称
     * @param array $where 需要更新的条件
     * @param array $needUpdateFields 需要更新的字段
     * @return bool|int 更新数据的条数
     */
    function batch_update(string $tableName, array $where, array $needUpdateFields)
    {
        if (empty($where) || empty($needUpdateFields)) {
            return false;
        }

        // 第一个条件数组的值
        $firstWhere = $where[array_key_first($where)];
        // 第一个条件数组的值的总数量
        $whereFirstValCount = count($firstWhere);
        // 需要更新的第一个字段的值的总数量
        $needUpdateFieldsValCount = count($needUpdateFields[array_key_first($needUpdateFields)]);
        if ($whereFirstValCount !== $needUpdateFieldsValCount) {
            return false;
        }
        // 所有的条件字段数组
        $whereKeys = array_keys($where);

        // 绑定参数
        $building = [];

        // $whereArr = [
        //   0 => "id = 180 AND ",
        //   1 => "user_id = 5 AND ",
        //   2 => "id = 181 AND ",
        //   3 => "user_id = 15 AND ",
        //   4 => "id = 182 AND ",
        //   5 => "user_id = 11 AND ",
        //   6 => "id = 183 AND ",
        //   7 => "user_id = 1 AND ",
        // ]
        $whereArr = [];
        $whereBuilding = [];
        foreach ($firstWhere as $k => $v) {
            foreach ($whereKeys as $whereKey) {
                // $whereArr[] = "{$whereKey} = {$where[$whereKey][$k]} AND ";
                $whereArr[] = "{$whereKey} = ? AND ";
                $whereBuilding[] = $where[$whereKey][$k];
            }
        }

        // $whereArray = [
        //     0 => "id = 180 AND user_id = 5",
        //     1 => "id = 181 AND user_id = 15",
        //     2 => "id = 182 AND user_id = 11",
        //     3 => "id = 183 AND user_id = 1",
        // ]
        $whereArrChunk = array_chunk($whereArr, count($whereKeys));
        $whereBuildingChunk = array_chunk($whereBuilding, count($whereKeys));

        $whereArray = [];
        foreach ($whereArrChunk as $val) {
            $valStr = '';
            foreach ($val as $vv) {
                $valStr .= $vv;
            }
            // 去除掉后面的 AND 字符及空格
            $whereArray[] = rtrim($valStr, 'AND ');
        }

        // 需要更新的字段数组
        $needUpdateFieldsKeys = array_keys($needUpdateFields);

        // 拼接 sql 语句
        $sqlStr = '';
        foreach ($needUpdateFieldsKeys as $needUpdateFieldsKey) {
            $str = '';
            foreach ($whereArray as $kk => $vv) {
                // $str .= ' WHEN ' . $vv . ' THEN ' . $needUpdateFields[$needUpdateFieldsKey][$kk];
                $str .= ' WHEN ' . $vv . ' THEN ? ';
                // 合并需要绑定的参数
                $building[] = array_merge($whereBuildingChunk[$kk], [$needUpdateFields[$needUpdateFieldsKey][$kk]]);
            }
            $sqlStr .= $needUpdateFieldsKey . ' = CASE ' . $str . ' ELSE ' . $needUpdateFieldsKey . ' END, ';
        }

        // 去除掉后面的逗号及空格
        $sqlStr = rtrim($sqlStr, ', ');

        $tblSql = 'UPDATE ' . $tableName . ' SET ';

        $tblSql = $tblSql . $sqlStr;

        $building = array_reduce($building, 'array_merge', []);
        // return [$tblSql, $building];
        return \Hyperf\DbConnection\Db::update($tblSql, $building);
    }
}

if (! function_exists('upsert_batch_update')) {
    /**
     * $tableName = 'my_table';
     *   $data = [
     *   [1, 'Tom', 20, 'male'],
     *   [2, 'Jerry', 25, 'female'],
     *   [3, 'Alice', 30, 'male'],
     *   [4, 'Bob', 35, 'female'],
     *   ];
     *   $columns = ['id', 'name', 'age', 'sex'];
     *
     *
     * 最终执行的 sql 语句如下所示
     *
     * INSERT INTO my_table (id, name, age, sex)
     * VALUES (1, 'Tom', 20, 'male'),
     * (2, 'Jerry', 25, 'female'),
     * (3, 'Alice', 30, 'male'),
     * (4, 'Bob', 35, 'female')
     * ON DUPLICATE KEY UPDATE name = VALUES(name), age = VALUES(age), sex = VALUES(sex);
     *
     * 批量更新数据，使用 `INSERT INTO ... ON DUPLICATE KEY UPDATE ...` 语句更新
     *
     * `UPDATE ... SET .. CASE .. WHEN`
     * 可以使用多条件进行更新，但是如果更新的数据较多，可能会导致事务超时或死锁等问题。
     *
     *
     * `INSERT INTO ... ON DUPLICATE KEY UPDATE ...`
     * 这条语句可以一次性插入多条数据，如果数据已经存在则执行更新操作
     * 可以一次性插入多条数据，适用于批量插入数据的场景
     * 可以避免重复插入数据，提高数据的完整性和准确性
     * 如果数据已经存在，则可以直接更新数据，不需要再进行查询操作，但是可能会增加数据库的负载
     * 只能用于更新存在主键或唯一索引的表
     *
     * @param string $tableName 需要更新的表名称
     * @param array $data 需要更新的数据
     * @param array $columns 需要更新的字段（注意第一个字段名称必须具有唯一性）
     * @return bool
     */
    function upsert_batch_update(string $tableName, array $data, array $columns): bool
    {
        $insertValues = [];
        $bindings = [];

        foreach ($data as $row) {
            $insertValues[] = '(' . implode(',', array_fill(0, count($row), '?')) . ')';
            $bindings = array_merge($bindings, $row);
        }

        $updateKeys = implode(',', array_map(function ($column) {
            return "$column = VALUES($column)";
        }, array_slice($columns, 1)));  // 移除掉第一个字段，默认为第一个字段具有唯一性

        $insertQuery = sprintf(
            'INSERT INTO %s (%s) VALUES %s',
            $tableName,
            implode(',', $columns),
            implode(',', $insertValues)
        );

        $updateQuery = sprintf(
            ' ON DUPLICATE KEY UPDATE %s',
            $updateKeys
        );

        $query = $insertQuery . $updateQuery;

        return \Hyperf\DbConnection\Db::statement($query, $bindings);
    }
}
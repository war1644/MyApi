<?php
/**
 * MyModel
 * 针对原本的 update(id,data) 等只能根据id操作 扩展
 * @author dxq1994@gmail.com
 * @version
 * v2018/6/7 下午2:29 初版
 */

namespace Common\Lib;


use PhalApi\Model\NotORMModel;

class MyModel extends NotORMModel
{

    /**
     * 扩展按数组查
     * @author dxq1994@gmail.com
     * @version v2018/6/7 下午2:32 初版
     * @param $data
     * @param string $field
     * @return mixed
     */
    public function fetchOne($data,$field='id') {
        return $this->getORM()
            ->select($field)
            ->where($data)
            ->fetchOne();
    }

    /**
     * 扩展按数组查
     * @author dxq1994@gmail.com
     * @version v2018/6/7 下午2:32 初版
     * @param $data
     * @param string $field
     * @return mixed
     */
    public function fetchAll($data,$field='id') {
        return $this->getORM()
            ->select($field)
            ->where($data)
            ->fetchAll();
    }

    /**
     * 扩展按数组更新数据
     * @author dxq1994@gmail.com
     * @version v2018/6/7 下午2:32 初版
     * @param $data
     * @param $where
     * @return mixed
     */
    public function up($where,$data) {
        return $this->getORM()
            ->where($where)
            ->update($data);
    }

    /**
     * 列表分页查询
     * @author dxq1994@gmail.com
     * @version v2018/6/7 下午2:47 初版
     * @param $where
     * @param string $field
     * @param $order
     * @param int $page
     * @param int $size
     * @return mixed
     */
    public function getList($where,$field='id',$order, $page =1,$size = PAGE_SIZE) {
        return $this->getORM()
            ->select($field)
            ->where($where)
            ->order($order)
            ->limit(($page-1)*PAGE_SIZE, $size)
            ->fetchAll();
    }

    public function getListTotal($where) {
        $total = $this->getORM()
            ->where($where)
            ->count('id');

        return intval($total);
    }


}
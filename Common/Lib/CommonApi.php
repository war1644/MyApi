<?php
namespace Common\Lib;

//use PhalApi\Api;
//* @return int page.count_page 总页数
//* @return int page.current_page 当前页
//* @return int page.next_page 下一页
//* @return int page.total 全部页总条数
// * @exception 4xx 参数传递错误
//* @exception 5xx 服务器内部错误

/**
 * CommonApi 公共Api，为了注释
 * @author dxq1994@gmail.com
 * @version v2018/5/16 下午6:11 初版
 * @return int code 0成功，4xx为ServiceApi请求失败，5xx为业务逻辑请求不正确
 * @return string msg 提示信息
 * @return array info 业务数据

 */
class CommonApi extends \PhalApi\Api
{

    /**
     * 一次取你 所需接口参数
     * @param string $keys 参数名列表(逗号分隔)，省略则获取全部
     * @return array
     */
    protected function data($keys='')
    {
        $data = get_object_vars($this);

        return empty($keys)?$data:subArray($data,$keys);
    }

}
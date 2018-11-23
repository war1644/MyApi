<?php
/**
 * MyRedis 原redis不存在的方法在这扩展
 * @author dxq1994@gmail.com
 * @version
 * v2018/6/4 下午3:08 初版
 */

namespace Common\Lib\Cache;


use PhalApi\Cache\RedisCache;

class MyRedis extends RedisCache
{
    public function setx($key, $value) {
        $this->redis->set($this->formatKey($key), $this->formatValue($value));
    }

    public function exists($key)
    {
        return $this->redis->exists($key);
    }



    /**
     * 重写为json化
     * @author dxq1994@gmail.com
     * @version v2018/6/7 下午3:40 初版
     * @param $value
     * @return string
     */
    protected function formatValue($value) {
        if(is_string($value) || is_numeric($value)) return $value;
        return json_encode($value,JSON_UNESCAPED_UNICODE);
    }

    /**
     * 重写为json化
     * @author dxq1994@gmail.com
     * @version v2018/6/7 下午3:40 初版
     * @param $value
     * @return mixed
     */
    protected function unformatValue($value) {
        if(!$value || !is_string($value)) return $value;
        $result = @json_decode($value,true);
        if($result===null) return $value;
        return $result;
    }

    /**
     *
     * @author dxq1994@gmail.com
     * @version v2018/6/7 下午2:53 初版
     * @param $function_name
     * @param $arguments
     * @return mixed
     */
//    public function __call($function_name,$arguments)
//    {
//        return call_user_func_array([$this->redis,$function_name],$arguments);
//    }




}
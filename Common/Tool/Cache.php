<?php
/**
 * Cache
 * @author dxq1994@gmail.com
 * @version
 * v2018/7/11 上午11:35 初版
 */

namespace Common\Tool;


class Cache
{
    /**
     * 写入
     * @author dxq1994@gmail.com
     * @version 2018/3/24
     * @param mixed $content
     * @param string $name
     * @param string $path
     * @return bool|int
     */
    public static function set($content, $name='', $path='')
    {
        defined('CACHE_PATH') && $path = CACHE_PATH.$path;
        if (!$name) return false;
        self::checkDir($path);
        $file = $path.$name;
        if (is_array($content) || is_object($content)) $content = json_encode($content,JSON_UNESCAPED_UNICODE);
        return file_put_contents($file,$content);
    }

    /**
     * 写入
     * @author dxq1994@gmail.com
     * @version 2018/3/24
     * @param mixed $content
     * @param string $name
     * @param string $path
     * @return bool|int
     */
    public static function add($content, $name='', $path='')
    {
        defined('CACHE_PATH') && $path = CACHE_PATH.$path;
        if (!$name) return false;
        self::checkDir($path);
        $file = $path.$name;
        if (is_array($content) || is_object($content)) $content = json_encode($content,JSON_UNESCAPED_UNICODE);
        return file_put_contents($file,$content."\n",FILE_APPEND);
    }

    /**
     * 获取缓存
     * @author dxq1994@gmail.com
     * @version 2018/3/24
     * @param string $name
     * @param string $path
     * @return mixed
     */
    public static function get( $name='', $path='')
    {
        defined('CACHE_PATH') && $path = CACHE_PATH.$path;
        $file = $path.$name;
        $cache = json_decode(@file_get_contents($file),true);
        return $cache;
    }

    /**
     * 检测是否是有该文件夹，没有则生成
     * @author dxq1994@gmail.com
     * @version 2018/3/24
     * @param string $dir
     * @param int $mode
     * @return bool
     */
    public static function checkDir($dir='', $mode=0770) {
        if (!$dir)  return false;
        if(!is_dir($dir)) {
            if (!file_exists($dir) && mkdir($dir, $mode, true))
                return true;
            return false;
        }
        return true;
    }

    /**
     * 写入日志到文件
     * @author dxq1994@gmail.com
     * @version 2018/3/27
     * @param mixed $log 日志内容
     * @param string $name 日志文件名
     * @param string $path 日志路径
     */
    public static function log($log, $name='', $path='')
    {
        if (!$path){
            defined('CACHE_PATH') && $path = CACHE_PATH.$path;
            $path = $path.date('Ymd/');
        }else{
            defined('CACHE_PATH') && $path = CACHE_PATH.$path;
        }
        if (!$name) $name = date( 'Ymd' );
        self::checkDir($path);
        $file = $path.$name.'.log';
        if (is_array($log) || is_object($log)) $log = json_encode($log,JSON_UNESCAPED_UNICODE);
        $content = "\nTime : ".date('Y-m-d H:i:s')."\nData : ".$log;
        file_put_contents($file,$content,FILE_APPEND);
        #error_log($content,3,$file);
    }


}
<?php
namespace Common\Tool;
/**
 * Http
 * @author dxq1994@gmail.com
 * @version
 * v2018/5/31 上午11:16 初版
 */
class Http
{
    /**
     * 获取IP
     * @return string $ip
     */
    public static function getIp() {
        static $ip = null;
        if ($ip !==null) {
            return $ip;
        }
        //判断是否为代理/别名/常规
        if (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_CLIENT_IP')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } else {
            $ip = getenv('REMOTE_ADDR');
        }
        return $ip;
    }
    /**
     * 递归转义数组中的字符,防止SQL注入
     * @param
     * @return bool 失败则返回false
     */
    public static function sqlDef($arr) {
        foreach ($arr as $k => $v) {
            if (is_string($v)) {
                $arr[$k] = addslashes ($v);
            } elseif (is_array($v)) {
                $arr[$k] = self::sqlDef($v);
            }
        }
        return $arr;
    }

    /**
     * 发起get请求
     * @author dxq1994@gmail.com
     * @version 2018/3/24
     * @param $url
     * @return bool|mixed
     */
    public static function get($url,$head=[]){
        $oCurl = curl_init();
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1 );
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        if ($head) {
            curl_setopt($oCurl, CURLOPT_HTTPHEADER, $head );
        }
        curl_setopt($oCurl, CURLOPT_FOLLOWLOCATION, true);//跟随跳转请求
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if(intval($aStatus["http_code"])==200){
            return $sContent;
        }else{
            return false;
        }
    }
    /**
     * POST 请求
     * @param string $url
     * @param mixed $param
     * @param boolean $post_file 是否文件上传
     * @return string content
     */
    public static function post($url,$param,$head = [],$post_file=false){
        $oCurl = curl_init();
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        if (PHP_VERSION_ID >= 50500 && class_exists('\CURLFile')) {
            $is_curlFile = true;
        } else {
            $is_curlFile = false;
            if (defined('CURLOPT_SAFE_UPLOAD')) {
                curl_setopt($oCurl, CURLOPT_SAFE_UPLOAD, false);
            }
        }
        if (is_string($param)) {
            $strPOST = $param;
        }elseif($post_file) {
            if($is_curlFile) {
                foreach ($param as $key => $val) {
                    if (substr($val, 0, 1) == '@') {
                        $param[$key] = new \CURLFile(realpath(substr($val,1)));
                    }
                }
            }
            $strPOST = $param;
        } else {
            $aPOST = array();
            foreach($param as $key=>$val){
                $aPOST[] = $key."=".urlencode($val);
            }
            $strPOST =  join("&", $aPOST);
        }
        if ($head) {
            curl_setopt($oCurl, CURLOPT_HTTPHEADER, $head );
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_FOLLOWLOCATION, true);//跟随跳转请求
        curl_setopt($oCurl, CURLOPT_POST,true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        return ['body'=>$sContent,'head'=>$aStatus];

    }

    /**
     * POST 请求
     * @param string $url
     * @param mixed $param
     * @param boolean $post_file 是否文件上传
     * @return string content
     */
    public static function headPost($url,$param,$head = [],$post_file=false){
        $oCurl = curl_init();
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        if (PHP_VERSION_ID >= 50500 && class_exists('\CURLFile')) {
            $is_curlFile = true;
        } else {
            $is_curlFile = false;
            if (defined('CURLOPT_SAFE_UPLOAD')) {
                curl_setopt($oCurl, CURLOPT_SAFE_UPLOAD, false);
            }
        }
        if (is_string($param)) {
            $strPOST = $param;
        }elseif($post_file) {
            if($is_curlFile) {
                foreach ($param as $key => $val) {
                    if (substr($val, 0, 1) == '@') {
                        $param[$key] = new \CURLFile(realpath(substr($val,1)));
                    }
                }
            }
            $strPOST = $param;
        } else {
            $aPOST = array();
            foreach($param as $key=>$val){
                $aPOST[] = $key."=".urlencode($val);
            }
            $strPOST =  join("&", $aPOST);
        }
        if ($head) {
            curl_setopt($oCurl, CURLOPT_HTTPHEADER, $head );
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        //输出head头
        curl_setopt($oCurl, CURLOPT_HEADER, 1);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_POST,true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        return ['body'=>$sContent,'head'=>$aStatus];
    }

}
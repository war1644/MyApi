<?php
namespace Common\Tool;

/**
 *
 * ResponseTimeLog
 * 记录接口响应时间
 * @author dxq1994@gmail.com
 * @version
 * v2018/5/16 下午3:21 初版
 */
class ResponseTimeLog {
    // 客户端IP地址
    private $ip;
    // datetime
    private $datetime;
    // 请求开始时间
    private $start;
    // 请求的操作
    private $service;
    // 日志文件储存地址
    private $dir;
    // 耗时
    private $cost;

    public function __construct($start){
        $this->ip = Tools::getIP();
        $this->datetime = date('Y-m-d H:i:s');
        $this->start = $start;
        $this->service = \PhalApi\DI()->request->get('service', '/');
        $this->dir = API_ROOT.'/runtime';
    }

    private function write(){
        if(!is_dir($this->dir)){
            mkdir($this->dir, 0755, true);
        }
        $this->dir .= '/ResponseTime.log';
        $file = fopen($this->dir, 'a+');
        $arr = array($this->ip, $this->datetime, $this->service, $this->cost, PHP_EOL);
        $text = implode(' | ', $arr);
        fwrite($file, $text);
        fclose($file);
    }

    public function __destruct() {
        $this->cost = round(microtime(true) - $this->start,4);
        $this->write();
    }
}

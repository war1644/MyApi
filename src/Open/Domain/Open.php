<?php
namespace Open\Domain;
use Common\Tool\OSS;
use Common\Tool\Cache;
use Common\Tool\Excel;
use Common\Tool\Http;
use Common\Tool\Tools;
use Open\Model\Overtime;
use PhalApi\Logger;
use Open\Model\FileDataProcess;

/**
 *
 * Open
 * @package Open\Domain
 * @author dxq1994@gmail.com
 * @version
 * v2018/5/31 上午11:43 初版
 */
class Open {

    public static $cookie;
    public static $workflowData;
    public static $listData;
    public static $current;

    /**
     * 加班list入口函数
     * @author dxq1994@gmail.com
     * @version v2018/6/21 上午10:53 初版
     * @param $apiObj
     * @return mixed
     */
    public static function overtimeList ($apiObj)
    {
        return Overtime::overtimeList($apiObj);
    }

    /**
     * 提交加班入口函数
     * @author dxq1994@gmail.com
     * @version v2018/6/21 上午10:53 初版
     * @param $apiObj
     * @return mixed
     */
    public static function sendOvertime ($apiObj)
    {
        $day = Overtime::sendOvertime($apiObj);
        return resFormat(["共计 $day 天的加班已提交完成，快去OA里协同工作-->已发事项查看加班记录吧"]);
    }


    public static function test(){
//        FileDataProcess::fileToSql();
//        return md5('');
//        return Overtime::diffOvertime();
//        self::setRedis();
//        self::taskSavePdf();

    }

    public static function task(){
//        return self::taskSavePdf();
    }







}

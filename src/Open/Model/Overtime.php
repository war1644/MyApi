<?php
/**
 * Overtime
 * @author dxq1994@gmail.com
 * @version
 * v2018/6/21 上午10:42 初版
 */

namespace Open\Model;

use Common\Tool\Cache;
use Common\Tool\Http;
use Common\Tool\MultiCurl;
use Common\Tool\Tools;

class Overtime
{
    private static $jsid;
    private static $overtimeData;
    private static $submitedOvertimeData;
    private static $host = 'http://my.com';
    public static $cookie;
    public static $workflowData;
    public static $user;
    public static $month;

    /**
     * list入口函数
     * @author dxq1994@gmail.com
     * @version v2018/6/21 上午10:53 初版
     * @param $apiObj
     * @return mixed
     */
    public static function overtimeList ($apiObj)
    {
        self::overtime($apiObj);
        return self::$overtimeData;
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
        self::overtime($apiObj);
        $c = $apiObj->comment;
        $data = self::$overtimeData['平日加班'];
//        $data = [
//            '2018-08-20'=>['9:11','2:33']
//        ];
        if($data){
            foreach ($data as $k => $v){
                $work = [
                    "$k $v[0]",
                    "$k $v[1]",
                    "$c",
                ];
                self::getOvertimeForm($work);
            }
            return count($data);

        }
        return 0;

//        $data = self::$overtimeData['周末加班'];
//        if($data){
//            foreach ($data as $k => $v){
//                $work = [
//                    "$k $v[0]",
//                    "$k $v[1]",
//                    "$c",
//                ];
//                self::getOvertimeForm($work);
//            }
//        }
//        self::$overtimeData['周末加班'];

//        return self::$overtimeData;
    }

    /**
     * 列出加班信息
     * @author dxq1994@gmail.com
     * @version v2018/6/21 上午10:43 初版
     * @param $apiObj
     * @return array
     */
    private static function overtime($apiObj)
    {
        if($apiObj->username) {
            self::$user = $apiObj->username;
            $res = self::login($apiObj);
            if (!$res) resFormat(2, '登录失败');
        }else if($apiObj->JSESSIONID){
            self::$jsid = $apiObj->JSESSIONID;
        }else{
            return resFormat(1,'缺少必要的参数');
        }
        //取一些信息
        self::getOvertimeForm();

        $time = strval(time()*1000);
        $preId = self::$workflowData['userId'];
        $accountId = self::$workflowData['accountId'];

        self::$month = $m = $apiObj->month;
        $url = "http://my.com/central/queryResult?_dc=$time&period=$m&filter=&choose=&id=&accountId=$accountId&perId=$preId&page=1&start=0&limit=30";
        $cookie = ["Cookie: JSESSIONID=".self::$jsid];
        $response = Http::get($url,$cookie);

        if(!$response)  return resFormat(1,L('request error'));
        $rows = json_decode($response,true)['rows'][0];
        if(!$rows) return resFormat(1,L('rows not have'));

        //开始提取数据
        $days = [];
        $others = [];
        foreach ($rows as $k => $v) {
            if(strpos($k,'-') !== false){
                $weekend = false;
                $timeStr = strip_tags($v,'');
                if(strlen($timeStr)>50) continue;
                $arr = explode('-',$k);
                $goWork = substr($timeStr,0,5);
                $offWork = substr($timeStr,5,5);
                $overtime = substr($timeStr,10);

                if (strpos($timeStr,'xiu') !== false)
                    $weekend = substr($timeStr,-3);

                $days[$m.'-'.$arr[1]] = [$goWork,$offWork,$overtime,$weekend];
            }else{
                $others[$k] = $v;
            }
        }

        self::$overtimeData = self::processOvertime($days);
        //将已经发起还没通过审核的加班移除
        self::submittedOvertimeList();

    }

    /**
     *
     * @author dxq1994@gmail.com
     * @version v2018/6/21 上午10:45 初版
     * @param $days
     * @return array
     */
    public static function processOvertime($days)
    {
        $weekendOvertime = [];
        $daysOvertime = [];
        foreach ($days as $k => $v) {
            #优先处理周末加班
            if($v[3] && (strlen($v[2]) == 3)) {
                $weekendOvertime[$k] = $v;
            }
            #已提交的加班
            if($v[2]) continue;

            #计算加班的但是未提交的
            $timeArr = explode(':',$v[0]);
            # 9点以前打卡的，移位到09:00
            if($timeArr[0] < 9){
                $startTime = strtotime('09:00');
            }else{
                $startTime = strtotime($v[0]);
            }
            $endTime = strtotime($v[1]);

            #跨天加班，超6点的我也爱莫能助了
            if($endTime < $startTime){
                $daysOvertime[$k] = $v;
                continue;
            }
            #不够两小时的
            if($endTime - $startTime < 11*3600) continue;
            $daysOvertime[$k] = $v;
        }
        ksort($daysOvertime);
        ksort($weekendOvertime);
        return ['JSESSIONID'=>self::$jsid,'平日加班'=>$daysOvertime,'周末加班'=>$weekendOvertime];

    }

    /**
     * 对比加班信息
     * @author dxq1994@gmail.com
     * @version v2018/6/29 下午5:53 初版
     */
    public static function diffOvertime()
    {
        self::$jsid = "de58bd87-1f1c-4858-8cb3-0a19c2d4640a";
        self::$cookie = [
            "Cookie: JSESSIONID=".self::$jsid,
            'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.87 Safari/537.36'
        ];
        return self::submittedOvertimeList();

//        $response = \PhalApi\DI()->notorm->overtime->where(['user_name'=>self::$user])->count('id');
//        if(!$response){
//            \PhalApi\DI()->notorm->overtime->save($data);
//        }

    }

    /**
     * 已提交的加班
     * @author dxq1994@gmail.com
     * @version v2018/7/30 下午2:09 初版
     */
    private static function submittedOvertimeList()
    {
        $number = Tools::randNumber(5);
        $url = "http://my.com/seeyon/ajax.do?method=ajaxAction&managerName=colManager&rnd=$number";
//        {"createDate":"2018-07-01#2018-07-30"}
        $startDay = Tools::curMonthFirstDay();
        $endDay = Tools::curMonthLastDay();
        //workflowState 0:未结束
        $formData = ["managerMethod"=>"getSentList", "arguments"=>"[{\"page\":1,\"size\":20},{\"subject\":\"加班申请表\",\"createDate\":\"$startDay#$endDay\",\"workflowState\":\"0\"}]"];
        $list = Http::post($url,$formData,self::$cookie);
        $list = json_decode($list['body'],true);
        if(!isset($list['data'])){
            Cache::log($list,'已提交的加班列表');
            return false;
        }
        $list = $list['data'];
        foreach ($list as $v){
            $url = "http://my.com/seeyon/content/content.do?method=index&isFullPage=true&hasDealArea=false&moduleId=$v[summaryId]&moduleType=1&rightId=$v[formOperationId]&contentType=20&viewState=2&openFrom=listSent&canDeleteISigntureHtml=false&isShowMoveMenu=false&isShowDocLockMenu=false";
            Cache::log($url,'url');
            MultiCurl::add(['url'=>$url,'data'=>[],'header'=>self::$cookie]);
        }
        $curlList = MultiCurl::exec(true);
        if(!$curlList){
            Cache::log($curlList,'已提交加班列表请求失败');
        }

        $overtime = self::$overtimeData['平日加班'];
        foreach ($curlList as $v){
            $submitedDay = self::submittedOvertimeInfo($v);
            if($submitedDay) unset($overtime[$submitedDay]);
        }
        self::$overtimeData['平日加班'] = $overtime;


        /*
         * http://my.com/seeyon/content/content.do?method=index&isFullPage=true&hasDealArea=false&moduleId=6727689985968775268&moduleType=1&rightId=-3122555039757483701&contentType=20&viewState=2&openFrom=listSent&canDeleteISigntureHtml=false&isShowMoveMenu=false&isShowDocLockMenu=false
         * http://my.com/seeyon/content/content.do?method=index&isFullPage=true&hasDealArea=false&moduleId=7989405895779874795&moduleType=1&rightId=-3122555039757483701&contentType=20&viewState=2&openFrom=listSent&canDeleteISigntureHtml=false&isShowMoveMenu=false&isShowDocLockMenu=false
         */

    }


    /**
     * 获取已提交的加班表详情
     * @author dxq1994@gmail.com
     * @version v2018/8/21 下午1:54 初版
     * @return array|bool
     */
    private static function submittedOvertimeInfo($response){

        //displayName:"加班时间起",fieldType:"DATETIME",inputType:"datetime",formatType:"",value:"2018-08-20 18:30"
        $tmpData = [];
        $regex = '/displayName:"加班时间起",fieldType:"DATETIME",inputType:"datetime",formatType:"",value:"(?P<department>.+?)"/';
        preg_match($regex,$response,$tmpData);
        if($tmpData['department']){
            $dateArr = explode(' ',$tmpData['department']);
            $submitedOvertimeData[] = $dateArr[0];
            return $dateArr[0];
        }
        Cache::log($tmpData);
        return false;
    }

    /**
     * 登录
     * @author dxq1994@gmail.com
     * @version v2018/6/21 上午10:46 初版
     * @param $apiObj
     * @return mixed
     */
    public static function login($apiObj)
    {
        if(!isset($apiObj->username) || !isset($apiObj->pwd)) die();
        $post = [
            'login_username' => $apiObj->username,
            'login_password' => $apiObj->pwd,
            'login.timezone' =>'GMT+8',
        ];
        $response = Http::headPost('http://my.com/seeyon/main.do?method=login',$post);
        //Set-Cookie: login_locale=""; Expires=Thu, 01-Jan-1970 00:00:10 GMT; Path=/
        //Set-Cookie: JSESSIONID=721a5bcc-bcfa-459a-b10c-8f28b7060b7d; Path=/seeyon/; HttpOnly
        $tmpData = [];
        $regex = '/Set-Cookie: JSESSIONID=(?P<JID>.+?);/';
        preg_match($regex,$response['body'],$tmpData);
        self::$jsid = $tmpData['JID'];
        return self::$jsid;
    }

    /**
     * 获取加班表单
     * @author dxq1994@gmail.com
     * @version v2018/6/21 上午10:49 初版
     */
    public static function getOvertimeForm($overTime=false)
    {
        $jid = self::$jsid;
//        $jid = 'FB67BF944F5BC7C1FCF34BED54BA5CB0';

        $cookie = self::$cookie = [
            "Cookie: JSESSIONID=$jid",
            'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.87 Safari/537.36'
        ];
        //父表获取必要数据 http://my.com/seeyon/collaboration/collaboration.do?method=newColl&templateId=8995909631771364838
        $url = 'http://my.com/seeyon/collaboration/collaboration.do?method=newColl&templateId=8995909631771364838';

        $response = Http::get($url,$cookie);

        if(!$response)  return resFormat(1,L('request error'));
        $workflowData = $tmpData = [];
        $regex = '/(?P<src>\/seeyon\/content\/content\.do\?(?P<arr>.*))"/';
        preg_match($regex,$response,$tmpData);
        $src = $tmpData["src"];
//        parse_str($tmpData['arr'],$sendData);
        $tmpData = [];

        //获取title id="subject" inputName="标题"  exp:HR03 加班申请表(段绪强 2018-04-02 18:06)
        $regex = '/value="(?P<title>HR03 加班申请表\((.*)\))"/';
        preg_match($regex,$response,$tmpData);
        $title = $tmpData['title'];

        //获取moud id="id" name="id"  exp:<input type="hidden" id="id" name="id" value='6914483630980989410'/>
        $tmpData = [];
        $regex = '/id="id" name="id" value=\'(?P<moduleId>[0-9]+)\'/';
        preg_match($regex,$response,$tmpData);
        $workflowData['moduleId'] = $moduleId = $tmpData['moduleId'];

        //id="createDate" name="createDate" value="2018-06-28 11:29:26" />
        $tmpData = [];
        $regex = '/id="createDate" name="createDate" value="(?P<createDate>.+?)"/';
        preg_match($regex,$response,$tmpData);
        $workflowData['sendDate'] = $tmpData['createDate'];

        //workflowId:'2372107248989260695'
        $tmpData = [];
        $regex = '/workflowId:\'(?P<workflowId>.+)\'/';
        preg_match($regex,$response,$tmpData);
        $workflowData['workflowId'] = $tmpData['workflowId'];

        //var affairMemberName = '段绪强';
        $tmpData = [];
        $regex = '/var affairMemberName = \'(?P<userName>.+)\'/';
        preg_match($regex,$response,$tmpData);
        $workflowData['userName'] = $tmpData['userName'];

        //var matchRequestToken= '1774041684969342225';
        $tmpData = [];
        $regex = '/var matchRequestToken= \'(?P<matchRequestToken>.+)\'/';
        preg_match($regex,$response,$tmpData);
        $workflowData['matchRequestToken'] = $tmpData['matchRequestToken'];

        //var loginAccount = '670869647114347';
        $tmpData = [];
        $regex = '/var loginAccount = \'(?P<accountId>.+?)\'/';
        preg_match($regex,$response,$tmpData);
        $workflowData['accountId'] = $tmpData['accountId'];

        //var CurrentUserId = '-150717638031553211';
        $tmpData = [];
        $regex = '/var CurrentUserId = \'(?P<userId>.+?)\'/';
        preg_match($regex,$response,$tmpData);
        $workflowData['userId'] = $tmpData['userId'];
        //var wfProcessTemplateId = '2372107248989260695';
        $tmpData = [];
        $regex = '/var wfProcessTemplateId = \'(?P<processTemplateId>.+?)\'/';
        preg_match($regex,$response,$tmpData);
        $workflowData['processTemplateId'] = $tmpData['processTemplateId'];

        //attsdatxa='[{xxxx}]'>
        $tmpData = [];
        $regex = '/attsdata=\'\[(?P<attrData>.+?)\]\'/is';
        preg_match($regex,$response,$tmpData);
        $workflowData['attrData'] = json_decode($tmpData['attrData'],true);

        $cookie[0] = "Cookie: JSESSIONID=$jid; avatarImageUrl=$workflowData[userId]; loginPageURL=\"\"";
        self::$cookie = $cookie;


        //加班表获取必要数据
        $url = 'http://my.com'.$src;
//        $rightId = $sendData['rightId'];
        //http://my.com/seeyon/content/content.do?method=index&isFullPage=true&formpage=newcol&isNew=false&moduleId=8995909631771364838&moduleType=1&rightId=-3258477429675572613.-3122555039757483701&contentType=20&&originalNeedClone=true&transOfficeId=&viewState=1&rnd=0.046649582334597595
//        $url = "http://my.com/seeyon/content/content.do?moduleId=8995909631771364838&moduleType=1&rightId=-3122555039757483701&viewState=1";
//        $cookie = ["Cookie: JSESSIONID=49a76348-3e09-4361-baf1-4a38c020eb16"];

        $response = Http::get($url,$cookie);
        if(!$response)  return resFormat(1,L('request error'));

        //var barCodeDepartment = '技术组';
        $tmpData = [];
        $regex = '/var barCodeDepartment = \'(?P<department>.+)\'/';
        preg_match($regex,$response,$tmpData);
        $workflowData['department'] = $tmpData['department'];

        //selectType:"Department",value:"Department|
        $tmpData = [];
        $regex = '/selectType:"Department",value:"(?P<departmentId>.+?)"/';
        preg_match($regex,$response,$tmpData);
        $workflowData['departmentId'] = $tmpData['departmentId'];

        //formatType:"",value:"MY01359"
        $tmpData = [];
        $regex = '/formatType:"",value:"(?P<MYId>MY.+?)"/';
        preg_match($regex,$response,$tmpData);
        $workflowData['MYId'] = $tmpData['MYId'];
//        var_dump($src,$tmpData,$response);

        self::$workflowData = $workflowData;
        if(!$overTime) return false;

        //从页面获取必须参数
        $regex = '/id="mainbodyDataDiv_0" style="display: none">.*?<\/div>/s';
        $tmpData = [];
        preg_match($regex,$response,$tmpData);
//        var_dump($response);die();

        //匹配所有input的id 和value
        //TODO 该正则不支持中文！！！
        $regex = '/<input type="hidden" id="(?P<ids>[a-zA-Z]+)" name="([a-zA-Z]+)" value=\'(?P<values>.*)\' \/>/';
        $arr = [];
        preg_match_all($regex,$tmpData[0],$arr);

        $mainbodyDataDiv_0 = [];
        foreach ($arr['ids'] as $k=>$v){
            $mainbodyDataDiv_0[$v] = $arr['values'][$k];
        }

        $mainbodyDataDiv_0['title'] = $title;
        $mainbodyDataDiv_0['moduleId'] = $moduleId;
        $mainbodyDataDiv_0['moduleTemplateId'] = "8995909631771364838";
//        $mainbodyDataDiv_0['sort'] = 0;
        $mainbodyDataDiv_0['id'] = "-1";
        $mainbodyDataDiv_0['createId'] = "0";
//        return $workflowData;

        if(!isset($mainbodyDataDiv_0['contentTemplateId'])){
            $mainbodyDataDiv_0['url'] = $url;
            return $mainbodyDataDiv_0;
        }
//        return self::$workflowData;
//        var_dump($mainbodyDataDiv_0);
//        self::ajaxGet($mainbodyDataDiv_0);
        return self::submitOvertime($mainbodyDataDiv_0,$overTime);
    }

    private static function ajaxGet($data){
        $preId = '-150717638031553211';
        $formId = $data['contentTemplateId'];
        $formMasterId = $data['contentDataId'];
        $moduleId = $data['moduleId'];
        $rightId = $data['rightId'];


        //
        $url = "http://my.com/seeyon/collaboration/collaboration.do?method=tabOffice";
        Http::get($url,self::$cookie);

        //1
        $number = Tools::randNumber(5);
        $url = "http://my.com/seeyon/ajax.do?method=ajaxAction&managerName=dataRelationManager&rnd=$number";
        $formData = ["managerMethod"=>"findRelationDatasByDR", "arguments"=>"[{\"templateId\":\"8995909631771364838\",\"activityId\":\"start\",\"DR\":\"\",\"affairId\":\"-1\",\"projectId\":\"\",\"summaryId\":\"$moduleId\",\"memberId\":\"$preId\",\"senderId\":\"\",\"nodePolicy\":{\"cancel\":true,\"editWorkFlow\":true,\"forward\":true,\"pigeonhole\":true,\"print\":true,\"reMove\":true,\"repeatSend\":true,\"uploadAttachment\":true,\"uploadRelDoc\":true}}]"];
        $ajaxRes1 = Http::post($url,$formData,self::$cookie);
        $tmp = json_decode($ajaxRes1['body'],true);
        $id = $tmp[0]['id'];

        //
        $time = strval(time()*1000);
        $url = "http://my.com/seeyon/extend/js/apps/collaboration/newCollaboration/collaborationSendControlCustom.js?_=$time";
        Http::get($url,self::$cookie);

        //
        $time = strval(time()*1000);
        $url = "http://my.com/seeyon/extend/js/apps/collaboration/newCollaboration/collaborationSendControl.js?_=$time";
        Http::get($url,self::$cookie);

        //2
        $number = Tools::randNumber(5);
        $url = "http://my.com/seeyon/ajax.do?method=ajaxAction&managerName=dataRelationManager&rnd=$number";
        $formData = ["managerMethod"=>"findByDataRelationIds", "arguments"=>"[{\"templateId\":\"8995909631771364838\",\"activityId\":\"start\",\"DR\":\"\",\"affairId\":\"-1\",\"projectId\":\"\",\"summaryId\":\"$moduleId\",\"memberId\":\"$preId\",\"senderId\":\"\",\"nodePolicy\":{\"cancel\":true,\"editWorkFlow\":true,\"forward\":true,\"pigeonhole\":true,\"print\":true,\"reMove\":true,\"repeatSend\":true,\"uploadAttachment\":true,\"uploadRelDoc\":true},\"poIds\":[\"$id\"],\"formMasterId\":\"$formMasterId\",\"pageConditions\":{\"$id\":[]}}]"];
        $ajaxRes2 = Http::post($url,$formData,self::$cookie);

        var_dump(['ajaxRes1'=>$ajaxRes1['body'],'ajaxRes2'=>$ajaxRes2['body']]);
    }

    //formmain字段 加班信息处理
    public static function formmainProcess($data,$overTime)
    {

        $time = strval(time()*1000);
        $field0001 = date('Y-m-d');
        $dateArr = explode(' ',$overTime[0]);
        $timeArr = explode(':',$dateArr[1]);
        $endTime = strtotime($overTime[1]);
        if($timeArr[0] < 9){
            $addTime = strtotime($dateArr[0].' 18:00');
        }else{
            $addTime = strtotime($overTime[0]) + 9*3600;
        }

        #跨天加班，超6点的我也爱莫能助了
        if($endTime < $addTime){
            $endTime += 24*3600;
            $overTime[1] = date('Y-m-d H:i',$endTime);
        }

        if($endTime - $addTime < 2*3600) die();
        $field0005 = date('Y-m-d H:i',$addTime);
        $field0006 = $overTime[1];
        $preId = self::$workflowData['userId'];
        $userName = self::$workflowData['userName'];
        $department = self::$workflowData['department'];
        $departmentId = self::$workflowData['departmentId'];
        $MYId = self::$workflowData['MYId'];
        $formId = $data['contentTemplateId'];
        $formMasterId = $data['contentDataId'];
        $moduleId = $data['moduleId'];
        $rightId = $data['rightId'];

        //提交上班时间 post
        //$url = "http://my.com/seeyon/form/formData.do?method=calculate&formMasterId=$formMasterId&formId=$formId&tableName=&fieldName=field0005&recordId=0&rightId=$rightId&calcAll=false&calcSysRel=false&moduleId=$moduleId&tag=$time";
        $formmain_0171 = ["formmain_0171" => ["field0001"=>$field0001,"field0002_txt"=>$userName,"field0002"=>"Member|$preId","field0003_txt"=>$department,"field0003"=>$departmentId,"field0004"=>$MYId,"field0005"=>$field0005,"field0006"=>"","field0013"=>"","field0012"=>"","field0012_txt"=>"","field0008"=>null,"field0009"=>null,"field0010"=>"","field0010_txt"=>"","field0011"=>"","field0011_0_editAtt"=>"true","field0007"=>""]];
        //感觉这个不需要，下班时间会包含上班时间
//        $str = urlencode(json_encode($formmain_0171));
//        $queryStr = "_json_params=$str";
//        $response1 = Http::post($url,$queryStr,self::$cookie);

        //* 提交下班时间 *
        $time = strval(time()*1000);
        $url = "http://my.com/seeyon/form/formData.do?method=calculate&formMasterId=$formMasterId&formId=$formId&tableName=&fieldName=field0006&recordId=0&rightId=$rightId&calcAll=false&calcSysRel=false&moduleId=$moduleId&tag=$time";
        $formmain_0171['formmain_0171']['field0006']=$field0006;
        $str = urlencode(json_encode($formmain_0171));
        $queryStr = "_json_params=$str";
        $response2 = Http::post($url,$queryStr,self::$cookie);

        //_json_params: {"formmain_0171":{"field0001":"2018-06-25","field0002_txt":"段绪强","field0002":"Member|-150717638031553211","field0003_txt":"技术组","field0003":"Department|-3462005611125780640","field0004":"MY01359","field0005":"2018-06-13 09:37","field0006":"2018-06-13 21:56","field0013":"","field0012":"","field0012_txt":"","field0008":null,"field0009":null,"field0010":"","field0010_txt":"","field0011":"","field0011_0_editAtt":"true","field0007":""},"attachmentInputs":[]}

        //提交加班时间
        $time = strval(time()*1000);
        $url = "http://my.com/seeyon/hrforge.do?method=getTotalTime&formId=$formId&startDate=".urlencode($field0005)."&endDate=".urlencode($field0006)."&perId=$preId&random=$time&ext_attr_1=";
        $response3 = Http::post($url,[],self::$cookie);
        if($response3['body']){
            $tmp2Body = json_decode($response3['body'],true);
            if(!$tmp2Body || !$tmp2Body['otHour']){
                \PhalApi\DI()->logger->info('加班表结果', $response3);
                die();
            }
        }else{
            \PhalApi\DI()->logger->info('加班表结果', $response3);
            die();
        }

        // checkSessioMasterDataExists(感觉是多余请求)
//        $number = Tools::randNumber(5);
//        $url = "http://my.com/seeyon/ajax.do?method=ajaxAction&managerName=formManager&rnd=$number";
//        $formData = ["managerMethod"=>"checkSessioMasterDataExists", "arguments"=>"[\"$formMasterId\"]"];
//        $wfRes1 = Http::post($url,$formData,self::$cookie);

        //感觉是多余请求
//        $time = strval(time()*1000);
//        $number = Tools::randNumber(16);
//        $number .= '0.';
//        $url = "http://my.com/seeyon/hrforge.do?method=getWidget&formAppMainId=$formId&invoiceId=&random=$number&_=$time";
//        Http::get($url,self::$cookie);


        //验证表单数据 post
        //[{ invoice:908, form:'5618906908784544726', changeTypeName:'平日加班', old_begin_date:'2018-06-13 09:37', old_end_date:'2018-06-13 21:56', personCode:'-150717638031553211', beginDate:'2018-06-13 09:37', endDate:'2018-06-13 21:56', changeType:'0', overtime_method:'0' }]
        $url = "http://my.com/seeyon/hrforge.do?method=verifyFormData";
        $jsonArray = [
            ["invoice"=>908, "form"=>$formId, "changeTypeName"=>'平日加班', "old_begin_date"=>$field0005, "old_end_date"=>$field0006, "personCode"=>$preId, "beginDate"=>$field0005, "endDate"=>$field0006, "changeType"=>'0', "overtime_method"=>'0']
        ];
        $str = urlencode(json_encode($jsonArray));
        $queryStr = "jsonArray=$str";
        $response4 = Http::post($url,$queryStr,self::$cookie);
//        if($response3['body']){
//            $tmp3Body = json_decode($response3['body'],true);
//            if(!$tmp3Body || $tmp3Body['result']) return $response3;
//        }else{
//            return $response3;
//        }


        //验证完成开始处理协同
        // 1
        $number = Tools::randNumber(5);
        $id = self::$workflowData['moduleId'];
        $url = "http://my.com/seeyon/ajax.do?method=ajaxAction&managerName=colManager&rnd=$number";
        $formData = ["managerMethod"=>"checkAffairAndLock4NewCol", "arguments"=>"[\"$id\",\"true\"]"];
        $wfRes1 = Http::post($url,$formData,self::$cookie);
//        if($response4['body']){
//            $tmp3Body = json_decode($response3['body'],true);
//            if(!$tmp3Body || $tmp3Body['result']) return $response3;
//        }else{
//            return $response3;
//        }

        // 2
        $number = Tools::randNumber(5);
        $url = "http://my.com/seeyon/ajax.do?method=ajaxAction&managerName=colManager&rnd=$number";
        $formData = ["managerMethod"=>"checkTemplate", "arguments"=>"[{\"templateId\":\"8995909631771364838\",\"formAppId\":\"$formId\",\"formParentId\":\"\",\"isSystem\":true}]"];
        $wfRes2 = Http::post($url,$formData,self::$cookie);

        $formmain_0171 = ["field0001"=>$field0001,"field0002_txt"=>$userName,"field0002"=>"Member|$preId","field0003_txt"=>$department,"field0003"=>$departmentId,"field0004"=>$MYId,"field0005"=>$field0005,"field0006"=>$field0006,"field0013"=>"$tmp2Body[otHour]","field0012"=>"5922426024077706626","field0012_txt"=>"平日加班","field0008"=>"1","field0009"=>null,"field0010"=>"1426838061068861559","field0010_txt"=>"倒休","field0011"=>"","field0011_0_editAtt"=>"true","field0007"=>$overTime[2]];
        $log = ['$response2'=>$response2['body'],'$response3'=>$response3['body'],'$response4'=>$response4['body'],'$wfRes1'=>$wfRes1['body'],'$wfRes2'=>$wfRes2['body']];
        \PhalApi\DI()->logger->info('加班表结果', $log);
//        die();
        return $formmain_0171;
    }

    /**
     * 提交加班信息
     * @author dxq1994@gmail.com
     * @version v2018/6/21 上午10:56 初版
     */
    private static function submitOvertime($data,$overTime)
    {
        $preId = self::$workflowData['userId'];
        $formId = $data['contentTemplateId'];
        $formMasterId = $data['contentDataId'];
        $moduleId = $data['moduleId'];
        $rightId = $data['rightId'];
        $url = 'http://my.com/seeyon/content/content.do?method=saveOrUpdate&onlyGenerateSn=false&notSaveDB=true&optType=undefined';
        $body = [
            "_currentDiv"=>["_currentDiv"=>"0"],

            "mainbodyDataDiv_0"=>$data,

            "formmain_0171"=>self::formmainProcess($data,$overTime),

            "attachmentInputs"=>[]
        ];
        $str = urlencode(json_encode($body));
        $queryStr = "_json_params=$str";
        $response1 = Http::post($url,$queryStr,self::$cookie);

        //数据保存完成后，协同处理
        // 3
        $number = Tools::randNumber(5);
        $matchRequestToken = self::$workflowData['matchRequestToken'];
        $url = "http://my.com/seeyon/ajax.do?method=ajaxAction&managerName=WFAjax&rnd=$number";
        $formData = ["managerMethod"=>"removeWorkflowMatchResultCache", "arguments"=>"[\"$matchRequestToken\"]"];
        $wfRes3 = Http::post($url,$formData,self::$cookie);

        // 4 协同处理
        $number = Tools::randNumber(5);
        $url = "http://my.com/seeyon/ajax.do?method=ajaxAction&managerName=WFAjax&rnd=$number";
        $accountId = self::$workflowData['accountId'];
        $processTemplateId = self::$workflowData['processTemplateId'];
        $formData = ["managerMethod"=>"transBeforeInvokeWorkFlow", "arguments"=>"[{\"appName\":\"collaboration\",\"processXml\":\"\",\"processId\":\"\",\"caseId\":\"-1\",\"currentActivityId\":\"-1\",\"currentWorkitemId\":\"-1\",\"currentUserId\":\"$preId\",\"currentAccountId\":\"$accountId\",\"formData\":\"$formMasterId\",\"mastrid\":\"$formMasterId\",\"debugMode\":false,\"processTemplateId\":\"$processTemplateId\",\"matchRequestToken\":\"$matchRequestToken\",\"isValidate\":\"true\"},{\"allNotSelectNodes\":[],\"allSelectNodes\":[],\"allSelectInformNodes\":[],\"pop\":false,\"token\":\"\",\"last\":\"false\",\"alreadyChecked\":\"false\"}]"];
        $wfRes4 = Http::post($url,$formData,self::$cookie);
        $wfRes4Body = json_decode($wfRes4['body'],true);


        //保存
        $url = 'http://my.com/seeyon/content/content.do?method=saveOrUpdate&onlyGenerateSn=false&optType=send';
        $body["workflow_definition"]=[];
        $str = urlencode(json_encode($body));
        $queryStr = "_json_params=$str";
        $response2 = Http::post($url,$queryStr,self::$cookie);
        $tmpRes = json_decode($response2['body'],true);

        // 5 发起协同
        //{"allNotSelectNodes":["14831867751460","144057455593213"],"allSelectInformNodes":[],"allSelectNodes":["14853119049690"],"alreadyChecked":"true","backgroundPop":true,"canSubmit":"true","cannotSubmitMsg":"","caseId":"-1","circleNodes":[],"condtionMatchMap":{},"condtionMatchMapKeyList":[],"currentSelectInformNodes":[],"currentSelectNodes":["14853119049690"],"dynamicFormMasterIds":"","hasSubProcess":false,"hst":"-1","hstv":"0","humenNodeMatchAlertMsg":null,"invalidateActivityMap":{},"invalidateActivityMapStr":"","isBackgroundPop":true,"isInSpecialStepBackStatus":"false","isNeedSelectBranch":false,"isPop":false,"last":"false","matchRequestToken":null,"matchResultMsg":"后面的节点可以被流转到达，并能激活产生待办，不需要选人或选分支。","needSelectBranch":false,"pop":false,"processId":"","sortPopIds":null,"subProcessMatchMap":null,"toReGo":false,"token":"WORKFLOW","workItemId":"-1"}
        $url = 'http://my.com/seeyon/collaboration/collaboration.do?method=send&from=&reqFrom=';
        $attachment_id = self::$workflowData['attrData']['id'];
        $descriptionId = self::$workflowData['attrData']['description'];
        $attrCreatedate = self::$workflowData['attrData']['createdate'];
        $attrFilename = self::$workflowData['attrData']['filename'];
        $sendDate = self::$workflowData['sendDate'];
        $nodeId = $wfRes4Body['allSelectNodes'][0];
        $nodeId1 = $wfRes4Body['allNotSelectNodes'][0];
        $nodeId2 = $wfRes4Body['allNotSelectNodes'][1];
        $body=[
            "workflow_definition"=>["process_desc_by"=>"","process_xml"=>"","readyObjectJSON"=>"","workflow_data_flag"=>"WORKFLOW_SEEYON","process_info"=>"","process_info_selectvalue"=>"","process_subsetting"=>"","moduleType"=>"1","workflow_newflow_input"=>"","process_rulecontent"=>"","workflow_node_peoples_input"=>"","workflow_node_condition_input"=>"{\"matchRequestToken\":\"$matchRequestToken\",\"condition\":[{\"nodeId\":\"$nodeId\",\"isDelete\":\"false\"},{\"nodeId\":\"$nodeId1\",\"isDelete\":\"true\"},{\"nodeId\":\"$nodeId2\",\"isDelete\":\"true\"}]}","processId"=>"","caseId"=>"-1","subObjectId"=>"-1","currentNodeId"=>"-1","process_message_data"=>"","processChangeMessage"=>"","process_event"=>"","toReGo"=>"false","dynamicFormMasterIds"=>""],

            "_currentDiv"=>[],"mainbodyDataDiv_0"=>[],"formmain_0171"=>[],"attachmentInputs"=>[],

            "assDocDomain"=>["attachment_id"=>$attachment_id,"attachment_reference"=>"8995909631771364838","attachment_subReference"=>"Doc1","attachment_category"=>"1","attachment_type"=>"2","attachment_filename"=>$attrFilename,"attachment_mimeType"=>"km","attachment_createDate"=>$attrCreatedate,"attachment_size"=>"0","attachment_fileUrl"=>$descriptionId,"attachment_description"=>$descriptionId,"attachment_needClone"=>"true","attachment_extReference"=>"","attachment_extSubReference"=>""],"attFileDomain"=>[],"colMainData"=>["temformParentId"=>"","resend"=>"false","newBusiness"=>"1","id"=>$moduleId,"parentSummaryId"=>"","attachmentArchiveId"=>"","tId"=>"8995909631771364838","curTemId"=>"8995909631771364838","resentTime"=>"","archiveId"=>"","prevArchiveId"=>"","currentNodesInfo"=>"","tembodyType"=>"20","formtitle"=>"HR03 加班申请表","saveAsTempleteSubject"=>"","phaseId"=>"","caseId"=>"","currentaffairId"=>"","createDate"=>$sendDate,"useForSaveTemplate"=>"no","oldProcessId"=>"","temCanSupervise"=>"true","standardDuration"=>"0","forwardMember"=>"","saveAsFlag"=>"","transtoColl"=>"0","bzmenuId"=>"","newflowType"=>"","contentViewState"=>"1","isOpenWindow"=>"true","canTrackWorkFlow"=>"","bodyType"=>"20","formRecordid"=>$tmpRes['contentAll']['contentDataId'],"formAppid"=>$tmpRes['contentAll']['contentTemplateId'],"formOperationId"=>$rightId,"formParentid"=>"","contentSaveId"=>$tmpRes['contentAll']['id'],"contentDataId"=>$tmpRes['contentAll']['contentDataId'],"contentTemplateId"=>$tmpRes['contentAll']['contentTemplateId'],"contentRightId"=>$rightId,"contentZWID"=>$tmpRes['contentAll']['id'],"contentSwitchId"=>"","DR"=>"","advancePigeonhole"=>"","contentIdUseDelete"=>"0","subject"=>$data['title'],"importantLevel"=>"1","myProject"=>"","selectProjectId"=>"-1","projectId"=>"-1","process_info"=>"部门部门主管(协同)、部门部门主管(协同)、部门部门主管(协同)、空节点、部门部门分管领导(协同)、人事行政经理（广州）(审批)、人力资源主管(审批)、部门部门分管领导(协同)、服务中心-部门主管(协同)、客服部-部门主管(协同)","isTemplateHasPigeonholePath"=>"false","colPigeonhole"=>"1","canTrack"=>"1","radioall"=>"0","radiopart"=>null,"zdgzry"=>"","zdgzryName"=>"","canForward"=>"1","canArchive"=>"1","canEditAttachment"=>null,"canEdit"=>null,"canModify"=>"1","canMergeDeal"=>"1","canAnyMerge"=>"1","unCancelledVisor"=>"","supervisorIds"=>"","detailId"=>"","supervisorNames"=>"","deadLine"=>"0","deadLineDateTime"=>"","deadLineDateTimeHidden"=>"","awakeDate"=>"","advanceRemind"=>"0","title"=>"","canAutostopflow"=>null],"comment_deal"=>["id"=>"","pid"=>"0","clevel"=>"1","path"=>"00","moduleType"=>"1","moduleId"=>$moduleId,"extAtt1"=>"","relateInfo"=>"","ctype"=>"-1","content_coll"=>""]
        ];
        $str = urlencode(json_encode($body));
        $queryStr = "_json_params=$str";
        $wfRes5 = Http::post($url,$queryStr,self::$cookie);

       $log = ['sub$response1'=>$response1['body'],'$wfRes3'=>$wfRes3['body'],'$wfRes4'=>$wfRes4['body'],'sub$response2'=>$response2['body'],'$wfRes5'=>$wfRes5['body']];
        \PhalApi\DI()->logger->info('最终结果', $log);
    }

}
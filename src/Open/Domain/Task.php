<?php
/**
 * Task 队列任务
 * 默认左进右出模式
 * @author dxq1994@gmail.com
 * @version
 * v2018/8/16 上午10:08 初版
 */

namespace src\Open\Domain;


use Common\Tool\Cache;
use Common\Tool\Http;
use Common\Tool\OSS;

class Task
{
    static $cookie;


    private static function taskSavePdf()
    {
        $rid = \PhalApi\DI()->cache->rPop('Task:HomePdf');
        if(!$rid){
            \PhalApi\DI()->logger->error('redis error');
            die();
        }
        $table = 'my';
        $response = \PhalApi\DI()->notorm->$table->where('my_id', $rid)->fetchOne('pdf');
        if(isset($response['pdf']) && $response['pdf']){
            die();
        }
        $host = 'https://my.com/db/bij5pyjs2';
        $ticket = '9_bpaxx3kxp_b3mbg3_ddmu_a_-b_b95vqmevi23ycvuguuz4az2sh4g949nbuvyszkmk3xvhdwptq5u_6838z4';
        self::$cookie  = [
            "Cookie: TICKET_my.com=$ticket",
            'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.87 Safari/537.36'
        ];
        //pdf
        $url = "$host?a=dr&rid=$rid";
        $response = Http::get($url,self::$cookie);
        if(!$response){
            \PhalApi\DI()->logger->error('request error html', json_encode(['url'=>$url,'error'=>$response],256));
            return resFormat(1,L('request error'));
        }

        
        $regex = '/<a href="(.+?\.pdf)".*>.+Profile.+?<\/a>/';
        $tmpData = [];
        preg_match($regex,$response,$tmpData);
        if(!$tmpData[1]){
            Cache::add($url,'pdf_regex_error.log');
            return resFormat(1,L('request error'));
        }
        $url = 'https://my.com'.$tmpData[1];
        $response = Http::get($url,self::$cookie);
        if(!$response){
            \PhalApi\DI()->logger->error('pdf_get_html_error',['url'=>$url,'error'=>$response]);
            return resFormat(1,L('request error'));
        }else{
            \PhalApi\DI()->logger->info('get pdf success', ['url'=>$url]);
        }
        $path = API_ROOT."/runtime/tmpPdf/";
        Cache::checkDir($path);
        $path .= "$rid.pdf";
        file_put_contents($path,$response);
        $oss = new OSS();
        $data = $oss->upload($path);
        $pdfUrl = '';
        if($data && isset($data['data']['fileurl'])){
            $pdfUrl =  $data['data']['fileurl'];
        }
        if(!$pdfUrl) {
            Cache::add(['url'=>$url,'my_id'=>$rid,'error'=>$data],'pdf上传失败.log');
        }else{
            $upData = [
                'pdf'=>$pdfUrl,
                'pdf_source'=>1
            ];
            \PhalApi\DI()->logger->info('pdf上传成功', ['my_id'=>$rid,'pdfUrl'=>$url,'pdf'=>$pdfUrl]);
            $response = \PhalApi\DI()->notorm->$table->where('my_id', $rid)->update($upData);
            if(!$response){
                Cache::add(['my_id'=>$rid,'pdfUrl'=>$pdfUrl],'pdf_add_mysql_error.log');
            }else{
                unlink($path);
                Cache::add(['my_id'=>$rid,'pdfUrl'=>$pdfUrl],'pdf_add_mysql_success.log');
            }
        }

        //url写入数据库
        //ALTER TABLE my ADD `pdf` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '住家pdf';
        //ALTER TABLE my ADD `pdf_source` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1:3W,2:TP';
        //ALTER TABLE my CHANGE `outer_id` `my_id` INT(10) NOT NULL DEFAULT 0 COMMENT 'my_id';
    }

    private static function quikbase(){
//        $r = \PhalApi\DI()->cache->lPush('Task:ListTest',"100");
//        $r = \PhalApi\DI()->cache->lPush('Task:ListTest',111);
        $r = \PhalApi\DI()->cache->rPop('Task:ListTest');
        var_dump($r);
        die();

        $ticket = '9_bpaxx3kxp_b3mbg3_ddmu_a_-b_b95vqmevi23ycvuguuz4az2sh4g949nbuvyszkmk3xvhdwptq5u_6838z4';
        $host = 'https://my.com/db/bij5pyjs2';
        self::$current = 0;
        $max = 72;
        self::$cookie  = [
            "Cookie: TICKET_my.com=$ticket",
            'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.87 Safari/537.36'
        ];
        //获取列表数据
//        $url = $host.'?from=myqb';
        $listData = [];
        for ($i=56;$i<$max;$i++){
            self::$current = $i;
            $rl = Tools::randNumber(1).Tools::randString(2);
            $number = 50*$i;
            $url = "$host?a=QBI_GenView&qid=-1214761&qskip=$number&qrppg=50&qtbtx=__none&encoding=utf-8&charset=windows-1252&rl=$rl&forTableHomePg=1";
//        $response = Http::get($url,self::$cookie);
            $response = Http::post($url,'<qdbapi></qdbapi>',self::$cookie);

            if(!$response){
                \PhalApi\DI()->logger->error('request error', ['url'=>$url,'error'=>$response]);
                return resFormat(1,L('request error'));
            }
            $tmpData = [];
            $regex = '/id=rid([0-9]+?)&gt;/';
            preg_match_all($regex,$response['body'],$tmpData);
            unset($response);
            Cache::add(count($tmpData[1]),'home_count.data');
            Cache::add($tmpData[1],'home.data');
            $listData = array_merge($listData,$tmpData[1]);
            sleep(2);
//            return self::$listData;
        }
        self::$listData = $listData;
        Cache::add(count(self::$listData),'list_home_count.data');
        Cache::set(self::$listData,'home_list.data');
        die();
        $rl = Tools::randNumber(1).Tools::randString(2);

        $number = 50*self::$current;
        $url = "https://my.com/db/bij5pyjs2?a=QBI_GenView&qid=-1214761&qskip=$number&qrppg=50&qtbtx=__none&encoding=utf-8&charset=windows-1252&rl=$rl&forTableHomePg=1";

        $response = Http::post($url,'<qdbapi></qdbapi>',self::$cookie);
        if(!$response)  return resFormat(1,L('request error'));
        $tmpData = [];
        $regex = '/id=rid([0-9]+?)&gt;/';
        preg_match_all($regex,$response['body'],$tmpData);
        unset($response);
        self::$listData = $tmpData[1];
        return self::$listData;
        self::saveAndup();

    }

}
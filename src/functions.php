<?php
/**
 * 公共方法
 * 不推荐在这里写，建议写公共类库Common\Tool下
 */


/**
 * 格式化输出api的data字段结构
 * @author dxq1994@gmail.com
 * @version v2018/5/16 下午7:27 初版
 * @param $code
 * @param string $msg
 * @param array $info
 * @return array
 */
function resFormat($code, $msg='', $info=[]) {
    if(!is_numeric($code)) {
        $info = $code;
        return [
            'code'=>0,
            'msg'=>$msg,
            'count'=>count($info),
            'info'=>$info
        ];
    }else{
        $code = 500+$code;
        if($code >= 600){
            $code = 500;
        }
        return [
            'code'=>$code,
            'msg'=>$msg,
            'count'=>count($info),
            'info'=>$info
        ];
    }

}


/**
 * 格式化输出api的page字段结构
 * @author dxq1994@gmail.com
 * @version v2018/5/18 上午9:24 初版
 * @param int $page
 * @param int $total
 * @param int $size
 * @return array
 */
function pageFormat($page=0, $total=0, $size=PAGE_SIZE) {
    $count_page = ceil($total/$size);
    $next = $page + 1;
    return [
        'count_page'=>$count_page,
        'current_page'=>$page,
        'next_page'=> $next > $count_page ? $count_page : $next,
        'total'=>$total,
    ];
}
<?php
namespace Common\Lib\Response;

/**
 * 小妞的 JSON响应类
 */

class MobileJsonResponse extends \PhalApi\Response\JsonResponse {

    protected function formatResult($result) {
        if(isset($result['info']) && empty($result['info']))
        {
            return json_encode($result, 16);//将[]转换成{}
        }
        return json_encode($result, $this->options);
    }

    /**
     * 重写响应json格式
     * @author dxq1994@gmail.com
     * @version v2018/6/7 下午6:08 初版
     * @return array
     */
    public function getResult() {

        $responseData = null;
        if($this->msg && count($this->data) == 0){
            $responseData['msg']  = $this->msg;
            $responseData['code'] = $this->ret;
            $responseData['info'] = array();
        }else{
            $responseData = $this->data;
        }

        if (!empty($this->debug)) {
            $responseData['debug'] = $this->debug;
        }

        return $responseData;
    }
    
}

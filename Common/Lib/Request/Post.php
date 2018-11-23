<?php
namespace Common\Lib\Request;
/**
 *
 * Post 只接受post请求
 * @author dxq1994@gmail.com
 * @version
 * v2018/5/16 下午2:44 初版
 */
class Post extends \PhalApi\Request {
    // 不需要默认json接收，可以post接受的服务
    private $post_request_service = array(
        'user.uploadLog',
    );
    /**
     * 重定义genData，只接收POST传值
     *
     * @access        public
     * @param        array      $data
     * @return        array
     */
    public function genData($data) {
        if (!isset($data) || !is_array($data)) {
            $array = explode(',', strtolower(implode(',', $this->post_request_service)));
            if(isset($_POST['service']) && in_array(strtolower($_POST['service']), $array)){
                // 改成只接收POST
                return $_POST;
            }else{
                $params = json_decode(file_get_contents("php://input"), true);
                if (empty($params)) {
                    return ;
                }
                // 过滤一维数组中的字符串
                return array_map(create_function('$v','return is_string($v) ? trim($v) : $v;'), $params);
            }
        }
    }

}

<?php
namespace Common\Lib\Filter;
use Common\Lib\MyException;
use Common\Lib\Auth\JWT;
/**
 *
 * VerifyToken 接口拦截器
 * @package Common\Lib\Filter
 * @author dxq1994@gmail.com
 * @version
 * v2018/5/16 下午2:41 初版
 */
class VerifyToken implements \PhalApi\Filter
{
    private $token = '';

    public function check()
    {
        $service = strtolower(\PhalApi\DI()->request->get('service'));
        if (!in_array($service, $array)) {
            // 取得token并解析，判断是否能解析，用户是否过期
            $token = \PhalApi\DI()->request->get('token');
            if($this->token === $token) return true;
            $userId = \PhalApi\DI()->request->get('uid');

            try {
                $tokenObj = JWT::decode($token, SECRET_KEY, ['HS256']);
            } catch (\Exception $e) {

                throw new MyException('您的账号异常，请重新登录', INVALID_TOKEN);
            }
            if ($tokenObj->uid != $userId) {
                throw new MyException('您的账号异常，请重新登录', INVALID_TOKEN);
            } else {
                if ($tokenObj->exp < time()) {
                    if($service !== "user.user.renewal"){
                        throw new MyException("您的账号信息已过期，请重新登录", EXPIRED_TOKEN);
                    }
                }else{
                    if($service == "user.user.renewal"){
                        define('TOKEN_IS_EXPIRED',true);
                    }else{
                        // 验证有效
                        $source = \PhalApi\DI()->request->get('source');
                        if(!$source) $source = 'pc';
                        $redisToken = \PhalApi\DI()->cache->get(TOKEN_KEY .$userId.':'.$source);

                        if (strcmp($redisToken, $token) != 0) {
                            throw new MyException('您的账号异常，请重新登录', INVALID_TOKEN);
                        }
                    }
                }
            }


            // 验证操作权限

        }
    }


}

<?php
/**
 * 请在下面放置任何您需要的应用配置
 *
 * @license     http://www.phalapi.net/license GPL 协议
 * @link        http://www.phalapi.net/
 * @author dogstar <chanzonghuang@gmail.com> 2017-07-13
 */

return array(

    /**
     * 应用接口层的统一参数
     */
    'apiCommonRules' => array(
        //'sign' => array('name' => 'sign', 'require' => true),
    ),

    /**
     * 接口服务白名单，格式：接口服务类名.接口服务方法名
     *
     * 示例：
     * - *.*         通配，全部接口服务，慎用！
     * - Site.*      Api_Default接口类的全部方法
     * - *.Index     全部接口类的Index方法
     * - Site.Index  指定某个接口服务，即Api_Default::Index()
     */
    'service_whitelist' => array(
        'Site.Index',
        'Mobile.*',
        'Guest.*',
    ),
    /**
     * 扩展类库 - 快速路由配置
     */
    'FastRoute' => array(
        /**
         * 格式：array($method, $routePattern, $handler)
         *
         * @param string/array $method 允许的HTTP请求方式，可以为：GET/POST/HEAD/DELETE 等
         * @param string $routePattern 路由的正则表达式
         * @param string $handler 对应PhalApi中接口服务名称，即：?service=$handler
         */
        'routes' => array(
            array('OPTION', 'web/user/videoLogin', 'Web.User.VideoLogin'),
            array('GET', 'web/user/videoLogin', 'Web.User.VideoLogin'),
            array('PUT', '/examples/curd/get/{id:\d}', 'Examples_CURD.Get'),
            array('PATCH', '/examples/curd/get/{id:\d}', 'Examples_CURD.Get'),
            array('POST', '/examples/curd/get/{id:\d}', 'Examples_CURD.Get'),
            array('DELETE', '/examples/curd/get/{id:\d}', 'Examples_CURD.Get'),
        ),
    ),
);

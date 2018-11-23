<?php
/**
 * 统一初始化
 */
//调试模式
define('DEBUG',false);
//请求时间记录
$requsetStartTime = microtime(true);
// 定义项目路径
defined('API_ROOT') || define('API_ROOT', dirname(__FILE__) . '/..');
//引入自定义常量
require_once API_ROOT . '/config/costVar.php';
// 引入composer
require_once API_ROOT . '/vendor/autoload.php';

// 时区设置
date_default_timezone_set('Asia/Shanghai');

// 引入DI服务
include API_ROOT . '/config/'.APP_NAME.'/di.php';

/*********项目服务在config/项目名/di.php配置*********/
/*********此处为全局服务*********/

// 调试模式
$di->debug = !empty($_GET['debug']) ? true : DEBUG;
if ($di->debug) {
    //记录接口相应时间
    new \Common\Tool\ResponseTimeLog($requsetStartTime);
    // 启动追踪器
    $di->tracer->mark();
//    error_reporting(E_ALL);
    error_reporting(E_ERROR | E_PARSE | E_COMPILE_ERROR | E_RECOVERABLE_ERROR);
    ini_set('display_errors', 'On');
}

//缓存 - redis
$di->cache = function() {
    //根据redis是否加载
    if(extension_loaded('redis')){
        $redis = new \Common\Lib\Cache\MyRedis(\PhalApi\DI()->config->get('sys.redis'));
    }else{
        //什么？没有？那就去装扩展
        throw new RedisException("not is redis extension");
    }
    return $redis;
};

// 翻译语言包设定
\PhalApi\SL('zh_cn');
if($di->request->get('lan') == 'en')
{
    \PhalApi\SL('en');
}


# 重写路由
$di->request = new Common\Lib\Request();

# 重写响应
$di->response = new \Common\Lib\Response\MobileJsonResponse(256);

// 数据库
$di->notorm = new \PhalApi\Database\NotORMDatabase($di->config->get('dbs'), $di->debug);

# 支持JsonP的返回
# 支持JsonP的返回
$httpOrigin = $_SERVER['HTTP_ORIGIN'];
if(strstr($httpOrigin,'.my.com'))
{
    header("Access-Control-Allow-Origin: $httpOrigin");
    header('Access-Control-Allow-Methods: *');
    header('Access-Control-Allow-Credentials: true');
}
if (!empty($_GET['callback'])) {
    $di->response = new \PhalApi\Response\JsonpResponse($_GET['callback']);
}





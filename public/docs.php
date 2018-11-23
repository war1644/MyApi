<?php
/**
 * PhalApi在线接口列表文档 - 自动生成
 *
 * - 对Api_系列的接口，进行罗列
 * - 按service进行字典排序
 * - 支持多级目录扫描
 *
 * <br>使用示例：<br>
 * ```
 * <?php
 * // 左侧菜单说明
 * class Demo extends Api {
 *      /**
 *       * 接口服务名称
 *       * @desc 更多说明
 *       * /
 *      public function index() {
 *      }
 * }
 * ```
 * @license     http://www.phalapi.net/license GPL 协议
 * @link        http://www.phalapi.net/
 * @author      xiaoxunzhao     2015-10-25
 * @modify      Aevit           2014-10-29
 * @modify      shwy            2017-03-02
 * @modify      dogstar         2017-06-17
 */
//报错开关
ini_set("display_errors", "On");
//报错等级 0 关闭 -1 全开
error_reporting(-1);

// 定义项目路径
defined('API_ROOT') || define('API_ROOT', dirname(__FILE__) . '/..');
require_once API_ROOT . '/config/costVar.php';
// 引入composer
require_once API_ROOT . '/vendor/autoload.php';

// 时区设置
date_default_timezone_set('Asia/Shanghai');

use PhalApi\Config\FileConfig;

/** ---------------- 基本注册 必要服务组件 ---------------- **/

$di = \PhalApi\DI();

// 配置
$di->config = new FileConfig(API_ROOT . '/config');

//\PhalApi\SL('zh_cn');

$projectName = '接口文档中心';

if (!empty($_GET['detail'])) {
    $apiDesc = new \Common\Lib\Helper\ApiDesc($projectName);
    //自定义文档渲染模版
    $apiDesc->render(API_ROOT.'/Common/Lib/Helper/api_desc_tpl.php');
} else {
    $apiList = new \PhalApi\Helper\ApiList($projectName);
    $apiList->render(API_ROOT.'/Common/Lib/Helper/api_list_tpl.php');
}


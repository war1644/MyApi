<?php
/**
 * DI依赖注入配置文件
 * 
 * @license     http://www.phalapi.net/license GPL 协议
 * @link        http://www.phalapi.net/
 * @author      dogstar <chanzonghuang@gmail.com> 2017-07-13
 */

use PhalApi\Config\FileConfig;
use PhalApi\Logger;
use PhalApi\Logger\FileLogger;
/** ---------------- 基本注册 必要服务组件 ---------------- **/

$di = \PhalApi\DI();
// 配置
$di->config = new FileConfig(API_ROOT . '/config/'.APP_NAME);

// 日记纪录
$di->logger = new FileLogger(API_ROOT . '/runtime', Logger::LOG_LEVEL_DEBUG | Logger::LOG_LEVEL_INFO | Logger::LOG_LEVEL_ERROR);
<?php
/**
 * 任务队列
 * 统一访问入口
 */
$_REQUEST['service'] = $argv[1];
define("APP_NAME","open");
require_once dirname(__FILE__) . '/../init.php';

$pai = new \PhalApi\PhalApi();
$pai->response()->output();


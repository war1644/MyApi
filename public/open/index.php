<?php
/**
 * 统一访问入口
 */
define("APP_NAME","open");
require_once dirname(__FILE__) . '/../init.php';

$pai = new \PhalApi\PhalApi();
$pai->response()->output();


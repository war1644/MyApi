<?php
/**
 * MyException
 * @author dxq1994@gmail.com
 * @version
 * v2018/9/19 上午9:45 初版
 */

namespace Common\Lib;


class MyException extends \PhalApi\Exception
{
    public function __construct($msg, $code = 0) {
        parent::__construct(
            \PhalApi\T($msg), 400 + $code
        );
    }
}
<?php
namespace Common\Lib\Auth;

class ExpiredException extends \PhalApi\Exception {

    public function __construct($msg, $code = 3) {
        parent::__construct(
            \PhalApi\T($msg), 400 + $code
        );
    }
}

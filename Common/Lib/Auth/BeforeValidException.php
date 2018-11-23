<?php
namespace Common\Lib\Auth;

class BeforeValidException extends \PhalApi\Exception {

    public function __construct($message, $code = 0) {
        parent::__construct(
            \PhalApi\T('Before Valid: {message}', array('message' => $message)), 400 + $code
        );
    }
}
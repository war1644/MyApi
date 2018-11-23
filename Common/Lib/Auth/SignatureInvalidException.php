<?php
namespace Common\Lib\Auth;

class SignatureInvalidException extends \PhalApi\Exception {

    public function __construct($message, $code = 0) {
        parent::__construct(
            \PhalApi\T('Signature Invalid: {message}', array('message' => $message)), 400 + $code
        );
    }
}
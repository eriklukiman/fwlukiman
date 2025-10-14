<?php

namespace Lukiman\Cores\Exception;

class WithStatusCodeException extends Base {
    private int $httpCode;

    public function __construct(string $message, $httpCode = 500, $errorCode = 400)
    {
        parent::__construct($message, $errorCode);
        $this->httpCode = $httpCode;
    }

    public function getHttpCode() {
        return $this->httpCode;
    }
}
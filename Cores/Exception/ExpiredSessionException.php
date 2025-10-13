<?php

namespace Lukiman\Cores\Exception;

class ExpiredSessionException extends Base {
    public const HTTP_CODE = 401;
}
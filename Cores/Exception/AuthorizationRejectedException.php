<?php

namespace Lukiman\Cores\Exception;

class AuthorizationRejectedException extends Base {
    public const HTTP_CODE = 401;
}
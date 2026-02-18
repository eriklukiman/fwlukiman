<?php

namespace Lukiman\Cores\Exception;

/**
 * Exception thrown when authorization is rejected.
 *
 * This exception sets the HTTP status code to 401 (Unauthorized).
 *
 * @package Cores\Exception
 */
class AuthorizationRejectedException extends Base {
    protected int $httpCode = 401;
}
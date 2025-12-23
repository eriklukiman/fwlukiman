<?php

namespace Lukiman\Cores\Exception;

/**
 * Exception thrown when a user's session has expired.
 *
 * This exception sets the HTTP response code to 401 (Unauthorized).
 *
 * @package Cores\Exception
 */
class ExpiredSessionException extends Base {
    protected int $httpCode = 401;
}
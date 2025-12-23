<?php

namespace Lukiman\Cores\Exception;

/**
 * Exception class representing a server error (HTTP 500).
 *
 * This exception should be thrown when an unexpected server-side error occurs.
 *
 * @package Cores\Exception
 */
class ServerErrorException extends Base {
    protected int $httpCode = 500;
}
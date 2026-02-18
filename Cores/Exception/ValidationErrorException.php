<?php

namespace Lukiman\Cores\Exception;

/**
 * Exception thrown for validation errors.
 *
 * This exception is intended for general use when returning validation errors.
 * It sets the HTTP status code to 422 (Unprocessable Entity).
 *
 * @package Cores\Exception
 */
class ValidationErrorException extends Base {
    protected int $httpCode = 422;
}
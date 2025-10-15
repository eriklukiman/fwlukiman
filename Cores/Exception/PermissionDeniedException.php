<?php

namespace Lukiman\Cores\Exception;

/**
 * Exception thrown when a user does not have the required permissions to access a resource.
 *
 * Sets the HTTP response code to 403 (Forbidden).
 */
class PermissionDeniedException extends Base {
    protected int $httpCode = 403;
}
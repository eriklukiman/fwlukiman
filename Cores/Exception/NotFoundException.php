<?php

namespace Lukiman\Cores\Exception;


/**
 * Exception thrown when a requested resource or entity is not found.
 *
 * This exception can be used to indicate that an operation failed because
 * the target item does not exist in the system.
 *
 * Typical usage includes handling missing database records, files, or other
 * resources that are expected to be present.
 */
class NotFoundException extends Base {
    protected int $httpCode = 404;
}
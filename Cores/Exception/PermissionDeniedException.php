<?php

namespace Lukiman\Cores\Exception;

class PermissionDeniedException extends Base {
    public const HTTP_CODE = 403;
}
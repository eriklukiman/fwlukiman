<?php
namespace Lukiman\Cores\Data\Transfer\Object;

class Base {
    use \Lukiman\Cores\Traits\DataTransferObject;

    protected function __construct(array $fields = []) {
        $this->instantiate($fields);
    }
}

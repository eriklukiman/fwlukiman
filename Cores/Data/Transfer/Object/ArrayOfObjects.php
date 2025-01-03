<?php
namespace Lukiman\Cores\Data\Transfer\Object;

class ArrayOfObjects extends \ArrayObject {
    use \Lukiman\Cores\Traits\DataTransferObject;

    protected String $className;

    protected function __construct(String $className, array $fields = []) {
        parent::__construct();
        $this->className = $className;
        $this->instantiate($fields);
    }

    public function offsetSet(mixed $key, mixed $val) : void {
        if ($val instanceof $this->className) {
            parent::offsetSet($key, $val);
        } else
            throw new \InvalidArgumentException('Object must be a type of ' . $this->className);
    }

    private function instantiate(array $fields = []) : void {
        foreach($fields as $k => $obj) {
            $this->offsetSet($k, $this->className::create($obj));
        }
    }

    public function print() : String {
        $vars = '';
        foreach($this as $k => $v) {
            $vars .= '[' . $k . '] => ' . (is_null($v) ? 'NULL' : $v->print()) . ', ';
        }
        return '{ ' . substr($vars, 0, -2) . ' }';
    }
}

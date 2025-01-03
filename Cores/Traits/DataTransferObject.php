<?php
namespace Lukiman\Cores\Traits;

trait DataTransferObject {
    private function instantiate(array $fields = []) : void {
        $properties = $this->getProperties();
        foreach($properties as $k => $v) {
            if (isset($fields[$k])) {
                if ($v == 'DateTime') $fields[$k] = new \DateTime($fields[$k]);
                else if (is_a($v, '\ArrayObject', true)) $fields[$k] = $v::create($fields[$k]);
                $this->{$k} = $fields[$k];
            }
        }
    }

    public function print() : String {
        $properties = $this->getProperties();
        $vars = '';
        foreach ($properties as $k => $v) {
            $val = $this->$k ?? null;
            if (!is_null($val)) {
                if ($v == 'DateTime') $val = $this->{$k}->format('Y-m-d H:i:sP');
                else if (is_a($v, '\ArrayObject', true)) $val = $this->{$k}->print();
            }
            $vars .= $k . ' : ' . ($val ?? 'NULL') . ', ';
        }
        return '{ ' . substr($vars, 0, -2) . ' }';
    }

    public function toArray() : array {
        $properties = $this->getProperties();
        $vars = [];
        foreach ($properties as $k => $v) {
            $vars[$k] = $this->$k ?? null;
        }
        return $vars;
    }

    private function getProperties() : array {
        $reflect = new \ReflectionClass($this);
        $properties = $reflect->getProperties();
        $retArr = [];
        foreach ($properties as $v) {
            $retArr[$v->getName()] = $v->getType()->getName();
        }
        return $retArr;
    }
}

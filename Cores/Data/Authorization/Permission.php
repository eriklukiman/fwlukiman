<?php
namespace Lukiman\Cores\Data\Authorization;

use \Lukiman\Cores\Data\Base as Base;

class Permission extends Base {
	private String $name;
	private array $operation;
	
	const defaultName = '_general_';
	
	public function __construct(String $lName = '', array $lOperation = array()) {
		if (empty($lName)) $lName = self::defaultName;
		$this->name = strtolower($lName);
		$this->setOperation($lOperation);
	}
	
	public function getName() : String {
		return $this->name;
	}

	public function setName(String $name = '') : self {
		if (empty($name)) $name = self::defaultName;
		$this->name = strtolower($name);
		return $this;
	}

	public function getOperation() : array {
		return $this->operation;
	}

	public function setOperation(array $operation) : self {
		$used = array();
		foreach($operation as $v) {
			$used[] = strtolower($v);
		}
		$this->operation = $used;
		return $this;
	}
	
	public function add(String $op) : self {
		$op = strtolower($op);
		if (!$this->isAuthorized($op)) {
			$this->operation[] = $op;
		}
		return $this;
	}
	
	public function remove(String $op) : self {
		$op = strtolower($op);
		if ($this->isAuthorized($op)) {
			$key = array_search($op, $this->operation);
			unset($this->operation[$key]);
		}
		return $this;
	}
	
	public function isAuthorized(String $operation) : bool {
		$operation = strtolower($operation);
		return in_array($operation, $this->operation);
	}
	
	public function __call($_name, $_arguments) {
		$prefix  = substr($_name, 0, 3);
		$operation = strtolower(substr($_name, 3));
		
		if ($prefix === "can") {
			return $this->isAuthorized($operation);
		} else {
			parent::__call($_name, $_arguments);
		}
	
	}
}
<?php
namespace Lukiman\Cores\Data\Authorization;

use \Lukiman\Cores\Data\Base as Base;

class Permission extends Base {
	private String $name;
	private array $operations;
	
	const defaultName = '_general_';
	
	public function __construct(String $lName = '', array $operations = array()) {
		if (empty($lName)) $lName = self::defaultName;
		$this->name = strtolower($lName);
		$this->setOperations($operations);
	}
	
	public function getName() : String {
		return $this->name;
	}

	public function setName(String $name = '') : self {
		if (empty($name)) $name = self::defaultName;
		$this->name = strtolower($name);
		return $this;
	}

	public function getOperations() : array {
		return $this->operations;
	}

	public function setOperations(array $operations) : self {
		$used = array();
		foreach($operations as $operation) {
			$used[] = strtolower($operation);
		}
		$this->operations = $used;
		return $this;
	}
	
	public function add(String $op) : self {
		$op = strtolower($op);
		if (!$this->isAuthorized($op)) {
			$this->operations[] = $op;
		}
		return $this;
	}
	
	public function remove(String $op) : self {
		$op = strtolower($op);
		if ($this->isAuthorized($op)) {
			$key = array_search($op, $this->operations);
			unset($this->operations[$key]);
		}
		return $this;
	}
	
	public function isAuthorized(String $operation) : bool {
		$operation = strtolower($operation);
		return in_array($operation, $this->operations);
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
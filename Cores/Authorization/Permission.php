<?php
namespace Lukiman\Cores\Authorization;

use \Lukiman\Cores\Data\Authorization\Permission as PermissionData;

class Permission {
	private PermissionData $permission;
	
	private String $prefix = 'can';
	
	public function __construct(String $name = '', array $operations = array()) {
		$this->permission = new PermissionData($name, $operations);
	}
	
	public function getName() : String {
		return $this->permission->getName();
	}

	public function setName(String $name = '') : self {
		$this->permission->setName($name);
		return $this;
	}

	public function getOperations() : array {
		return $this->permission->getOperations();
	}

	public function setOperations(array $operations) : self {
		$this->permission->setOperations($operations);
		return $this;
	}
	
	public function getPrefix() : String {
		return $this->prefix;
	}
	
	public function setPrefix(String $prefix) : self {
		$this->prefix = $prefix;
		return $this;
	}
	
	public function add(String $op) : self {
		$op = strtolower($op);
		$ops = $this->getOperations();
		if (!$this->isAuthorized($op)) {
			$ops[] = $op;
		}
		$this->setOperations($ops);
		return $this;
	}
	
	public function remove(String $op) : self {
		$op = strtolower($op);
		$ops = $this->getOperations();
		if ($this->isAuthorized($op)) {
			$key = array_search($op, $ops);
			unset($ops[$key]);
		}
		$this->setOperations($ops);
		return $this;
	}
	
	public function isAuthorized(String $operation) : bool {
		$operation = strtolower($operation);
		return in_array($operation, $this->getOperations());
	}
	
	public function __call($_name, $_arguments) {
		$prefix  = substr($_name, 0, 3);
		$operation = strtolower(substr($_name, 3));
		
		if ($prefix === $this->prefix) {
			return $this->isAuthorized($operation);
		} else {
			parent::__call($_name, $_arguments);
		}
	
	}
}
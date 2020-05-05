<?php
namespace Lukiman\Cores\Data\Authorization;

use \Lukiman\Cores\Data\Base as Base;

class Permission extends Base {
	private String $name;
	private array $operations;
	
	const defaultName = '_general_';
	
	public function __construct(String $name = '', array $operations = array()) {
		if (empty($name)) $name = self::defaultName;
		$this->name = strtolower($name);
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
}
<?php
namespace Lukiman\Cores\Data\Authorization;

// use \Lukiman\Cores\Data\Authorization\Permission;
use \Lukiman\Cores\Data\Base as Base;

class Role extends Base {
	private String $name;
	private array $permissions;
	
	const defaultName = '_general_';
	
	public function __construct(String $name = '', array $permissions = array()) {
		if (empty($name)) $name = self::defaultName;
		$this->name = strtolower($name);
		$this->permissions = $permissions;
	}
	
	public function getName() : String {
		return $this->name;
	}

	public function setName(String $name = '') : self {
		if (empty($name)) $name = self::defaultName;
		$this->name = strtolower($name);
		return $this;
	}

	public function getPermissions() : array {
		return $this->permissions;
	}

	public function setPermissions(array $permissions = array()) {
		$this->permissions = $permissions;
		return $this;
	}
}
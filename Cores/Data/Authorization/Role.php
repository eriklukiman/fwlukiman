<?php
namespace Lukiman\Cores\Data\Authorization;

use \Lukiman\Cores\Data\Authorization\Permission;
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
	
	public function combine(self $role) : self {
		$permissions = $role->getPermissions();
		foreach($permissions as $permission) {
			$this->add($permission);
		}
		return $this;
	}
	
	public function isModuleExist(String $name) : bool {
		$name = strtolower($name);
		return array_key_exists($name, $this->permissions);
	}
	
	public function add(Permission $detail) : self {
		if (!$this->isModuleExist($detail->getName())) {
			$this->permissions[$detail->getName()] = $detail;
		} else {
			$this->permissions[$detail->getName()]->setOperation(array_unique(array_merge($this->permissions[$detail->getName()]->getOperation(), $detail->getOperation()))); 
		}
		return $this;
	}
	
	public function remove(Permission $detail) : self {
		$this->removeByKey($detail->getName());
		return $this;
	}
	
	public function removeByKey(String $key) : self {
		$key = strtolower($key);
		unset($this->permissions[$key]);
		return $this;
	}
	
	public function getModule(String $name) : Permission {
		if($this->isModuleExist($name)) {
			return $this->permissions[strtolower($name)];
		} else {
			return new Permission($name);
		}
	}
	
	public function __call($_name, $_arguments) {
		$key = 'module';
		$keyLen = strlen($key);
		$prefix  = substr($_name, 0, $keyLen);
		$module = substr($_name, $keyLen);
		
		if ($prefix === $key) {
			return $this->getModule($module);
		} else {
			parent::__call($_name, $_arguments);
		}
	
	}
}
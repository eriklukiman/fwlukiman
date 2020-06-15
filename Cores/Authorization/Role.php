<?php
namespace Lukiman\Cores\Authorization;

use \Lukiman\Cores\Authorization\Permission;
use \Lukiman\Cores\Data\Authorization\Role as RoleData;
use \Lukiman\Cores\Exception\Base as ExceptionBase;

class Role {
	private RoleData $role;
	
	private String $prefix = 'module';
	
	public function __construct(String $name = '', array $permissions = array()) {
		$this->role = new RoleData($name, $permissions);
	}
	
	public function getName() : String {
		return $this->role->getName();
	}

	public function setName(String $name = '') : self {
		$this->role->setName($name);
		return $this;
	}

	public function getPermissions() : array {
		return $this->role->getPermissions();
	}

	public function setPermissions(array $permissions = array()) {
		$this->role->setPermissions($permissions);
		return $this;
	}
	
	public function getPrefix() : String {
		return $this->prefix;
	}
	
	public function setPrefix(String $prefix) : self {
		$this->prefix = $prefix;
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
		return array_key_exists($name, $this->getPermissions());
	}
	
	public function add(Permission $permission) : self {
		$permissions = $this->getPermissions();
		if (!$this->isModuleExist($permission->getName())) {
			$permissions[$permission->getName()] = $permission;
		} else {
			$permissions[$permission->getName()]->setOperations(array_unique(array_merge($permissions[$permission->getName()]->getOperations(), $permission->getOperations()))); 
		}
		$this->setPermissions($permissions);
		return $this;
	}
	
	public function remove(Permission $permission) : self {
		$this->removeByKey($permission->getName());
		return $this;
	}
	
	public function removeByKey(String $key) : self {
		$key = strtolower($key);
		$permissions = $this->getPermissions();
		unset($permissions[$key]);
		$this->setPermissions($permissions);
		return $this;
	}
	
	public function getModule(String $name) : Permission {
		if($this->isModuleExist($name)) {
			$permissions = $this->getPermissions();
			return $permissions[strtolower($name)];
		} else {
			return new Permission($name);
		}
	}
	
	public function __call($_name, $_arguments) {
		$key = $this->prefix;
		$keyLen = strlen($key);
		$prefix  = substr($_name, 0, $keyLen);
		$module = substr($_name, $keyLen);
		
		if ($prefix === $key) {
			return $this->getModule($module);
		} else {
			throw new ExceptionBase("No method with name " . $_name);
		}
	
	}
}
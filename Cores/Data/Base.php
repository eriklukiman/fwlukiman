<?php
namespace Lukiman\Cores\Data;

class Base {
	
	public function __construct() {
		$this->initialize($this);
		//echo 'bbb';
		// print_r(get_class_vars(get_class($this)));
		// print_r(get_object_vars($this));
	}
	
	public function __call($_name, $_arguments) {
		$action  = substr($_name, 0, 3);
		$varName = lcfirst(substr($_name, 3));

		if (property_exists($this, $varName)) {
			if (($action === "get") AND isset($this->{$varName})) {
				return $this->{$varName};
			} else {
				return null;
			}
			if ($action === "set") $this->{$varName} = $_arguments[0];
		} else {
			echo 'error in invoking __call function';
			return null;
		}
	}
}
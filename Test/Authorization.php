<?php
namespace Lukiman\Test;

use Assert\Assertion;
use Assert\AssertionFailedException;

use \Lukiman\Cores\Authorization\Role;
use \Lukiman\Cores\Authorization\Permission;

class Authorization extends General {
	
	public function do_SimpleAuth() {
		echo '<pre>';
		$auth = new Permission("master", ['read', 'MoDify']);
		$auth1 = new Permission("master", ['write', 'modify']);
		$auth2 = new Permission("print_report", ['read', 'view']);
		$auth3 = new Permission();
		$auth4 = new Permission('master_barang', ['add', 'delete']);
		// var_dump($auth->canModify());
		// var_dump($auth->getname());
		// var_dump($auth->isAuthorized('read'));
		// $auth->add('modify1');
		// $auth->remove('modify');
		// var_dump($auth);
		// $a =  [1,2, 7];
		// $b = [3, 1];
		// var_dump(array_unique(array_merge($a,$b)));
		
		$authL = new Role();
		$role2 = new Role('admin');
		$authL->add($auth)->add($auth2)->add($auth3);
		// $authL->add($auth1)->remove($auth3);
		$role2->add($auth1)->remove($auth3)->add($auth4);
		$a = $authL->moduleMaster();
		var_dump($a);
		try {
			// Assertion::false(true, "not false");
			var_dump($authL->moduleMaster()->canModify());
			Assertion::true($authL->moduleMaster()->canModify());
			var_dump($authL->moduleMaster()->canView());
			Assertion::false($authL->moduleMaster()->canView());
			var_dump($authL->moduleMaster1()->canModify());
			Assertion::false($authL->moduleMaster1()->canModify());
			var_dump($authL->moduleMaster1()->canView());
			Assertion::true($authL->moduleMaster1()->canView());
			// var_dump($authL->modulePrint_Report());
			var_dump($authL->modulePrint_Report()->canView());
			Assertion::true($authL->modulePrint_Report()->canView());
		}  catch(AssertionFailedException $e) {
			// error handling
			$det = $e->getTrace();
			echo "\n[ERROR] " . $e->getMessage() . " In file " . $det[1]['file'] . ' line ' . $det[1]['line'] . PHP_EOL . PHP_EOL;
			// var_dump($e->getValue());
			// echo "\n[ERROR] File " . $det[1]['file'] . ' line ' . $det[1]['line'] . PHP_EOL . PHP_EOL;
			return;
			// print_r($e);
			// $e->getValue(); // the value that caused the failure
			// $e->getConstraints(); // the additional constraints of the assertion.
		}
		// $authL->remove($auth1);
		$authL->combine($role2);
		print_r($role2);
		print_r($authL);

	}
	
	
}

<?php
namespace Lukiman\Test;

use Assert\Assertion;
use Assert\AssertionFailedException;

use \Lukiman\Cores\Authentication as mAuthentication;

class Authentication extends Base {
	
	public function do_SimpleAuth() {
		// $config = ['provider' => 'google'];
		// $auth = new Authentication($config);
		$auth = new mAuthentication();
		$token = '';
		$get = $this->getValueFromParameter('get');
		if (!empty($get['token'])) $token = $get['token'];
		
		// $a = new AuthData();
		// echo $a->getName();
		// $a->setName('test123');
		// $a->setEmail('ddd');
		// var_dump($a);
		// echo $a->getName();
		
		$auth->authWithToken($token);
	
		$cred = $auth->getCredentials();
		print_r($cred);
		if (!empty($cred)) print_r($cred->getUserName());
		// var_dump($auth);
		// $auth->revokeAuthentication();
		return ($auth->isAuthenticated() ? 'OK' : 'Failed');

	}
	
}

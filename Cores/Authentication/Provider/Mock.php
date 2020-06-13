<?php
namespace Lukiman\Cores\Authentication\Provider;

use \Lukiman\Cores\Exception\Base as ExceptionBase;
use \Lukiman\Cores\Interfaces\Authentication as IAuthentication;
use \Lukiman\Cores\Data\Authentication as AuthData;

class Mock extends Base implements IAuthentication {
	
	public function __construct(?array $config = null) {
		$authData = new AuthData();
		$authData->setUserName('test@test.com');
		$authData->setEmail('test@test.com');
		$authData->setExpired(strtotime("+1 year"));
		$this->credentials = $authData;
	}
	
	public function authWithUserPassword(String $username, String $password) : bool {
		return true;
	}

	public function authWithToken(String $token) : bool {
		return true;
	}
	
	public function isAuthenticated() : bool {
		return true;
	}
}

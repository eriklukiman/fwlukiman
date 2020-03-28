<?php
namespace Lukiman\Cores\Authentication\Provider;

use \Lukiman\Cores\Exception\Base as ExceptionBase;
use \Lukiman\Cores\Interfaces\Authentication as IAuthentication;
use \Lukiman\Cores\Data\Authentication as AuthData;

abstract class Base implements IAuthentication {
	protected ?AuthData $credentials;
	
	public function authWithUserPassword(String $username, String $password) : bool {
		return false;
	}
	
	public function authWithToken(String $token) : bool {
		return false;
	}
	
	public function isAuthenticated() : bool {
		if (!empty($this->credentials) AND !empty($this->credentials->getExpired()) AND ($this->credentials->getExpired() > time())) {
			return true;
		} else {
			$this->revokeAuthentication();
			return false;
		}
	}
	
	public function revokeAuthentication() : bool {
		$this->credentials = null;
		return true;
	}
	
	public function getCredentials() : ?AuthData {
		if ($this->isAuthenticated()) {
			return $this->credentials;
		} else {
			$this->revokeAuthentication();
			return null;
		}
	}
	
	protected function convertToData(?array $input) : AuthData {
		return new AuthData();
	}
	
	protected function setCredentials(?AuthData $data) : void {
		$this->credentials = $data;
	}
}

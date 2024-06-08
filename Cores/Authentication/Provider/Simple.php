<?php
namespace Lukiman\Cores\Authentication\Provider;

use \Lukiman\Cores\Exception\Base as ExceptionBase;
use \Lukiman\Cores\Interfaces\Authentication as IAuthentication;
use \Lukiman\Cores\Data\Authentication as AuthData;
use \Lukiman\Cores\Model;

abstract class Simple extends Base implements IAuthentication {
	protected int $ttl = 3600; //in seconds
	
	public function __construct(?array $config = null) {
		if (!empty($config) AND !empty($config['ttl'])) {
			$this->ttl = $config['ttl'];
		}
	}
	
	public function authWithUserPassword(String $username, String $password) : bool {
		if (empty($username) OR empty($password)) return false;
		$credentialData = $this->getCredentialData($username);
		if (empty($credentialData) OR ($credentialData->getUserName() != $username)) return false;
		if (!$this->verifyPassword($password, $credentialData->getPassword())) return false;

		$authData = new AuthData();
		$authData->setId($credentialData->getId());
		$authData->setUserName($credentialData->getUserName());
		$authData->setEmail($credentialData->getEmail() ?? '');
		$authData->setName($credentialData->getName() ?? '');
		$authData->setPicture($credentialData->getPicture() ?? '');
		$authData->setExpired(strtotime("+" . $this->ttl . " second"));
		return $this->grantAuthentication($authData);
	}

	abstract protected function getCredentialData(String $username) : AuthData;

	public function hashPassword(String $password) : String {
		return password_hash($password, PASSWORD_DEFAULT);
	}

	protected function verifyPassword(String $password, String $hash) : bool {
		return password_verify($password, $hash);
	}
	
}

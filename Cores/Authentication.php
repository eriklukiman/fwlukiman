<?php
namespace Lukiman\Cores;

use \Lukiman\Cores\Data\Authentication as AuthData;

class Authentication implements Interfaces\Authentication {
	static protected array $config;
	protected Interfaces\Authentication $instance;

	public function __construct (?array $config = null) {
		if (empty($config)) {
			if (empty(static::$config)) {
				static::$config = Loader::Config('Authentication');
			}
			$config = static::$config;
		}
		$this->instance = Authentication\Factory::instantiate($config);
		return $this;
	}
	
	public function authWithUserPassword(String $username, String $password) : bool {
		return $this->instance->authWithToken($username, $password);
	}
	
	public function authWithToken(String $token) : bool {
		return $this->instance->authWithToken($token);
	}
	
	public function isAuthenticated() : bool {
		return $this->instance->isAuthenticated();
	}
	
	public function revokeAuthentication() : bool {
		return $this->instance->revokeAuthentication();
	}
	
	public function getCredentials() : ?AuthData {
		return $this->instance->getCredentials();
	}

	public function extendAuthentication(int $ttl) : bool {
		return $this->instance->extendAuthentication($ttl);
	}

	public function grantAuthentication(AuthData $data) : bool {
		return $this->instance->grantAuthentication($data);
	}
}
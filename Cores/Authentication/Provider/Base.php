<?php
namespace Lukiman\Cores\Authentication\Provider;

use \Lukiman\Cores\Exception\Base as ExceptionBase;
use \Lukiman\Cores\Interfaces\Authentication as IAuthentication;
use \Lukiman\Cores\Data\Authentication as AuthData;

abstract class Base implements IAuthentication {
	protected ?AuthData $credentials;
	protected int $ttl;

	public function __construct(?array $config = null) {
    	if (!empty($config) AND !empty($config['ttl'])) {
    		$this->ttl = intval($config['ttl']);
    	} else {
            $this->ttl = 24 * 60 * 60; //default 1 day
        }
	}

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

	public function grantAuthentication(AuthData $data) : bool {
		$this->credentials = $data;
		return true;
	}

	public function revokeAuthentication() : bool {
		$this->credentials = null;
		return true;
	}

	public function extendAuthentication(int $ttl) : bool {
		if ($this->isAuthenticated()) {
			$this->credentials->setExpired(strtotime("+" . $ttl . " second"));
			return true;
		} else {
			$this->revokeAuthentication();
			return false;
		}
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

	protected function calculateLeastExpiryTimestamp(?int $expired_ts, int $base_ts) : int {
		if (is_null($expired_ts) OR empty($expired_ts)) return ($base_ts + $this->ttl);
		return min($expired_ts, ($base_ts + $this->ttl));
	}
}

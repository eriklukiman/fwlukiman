<?php
namespace Lukiman\Cores;

use \Lukiman\Cores\Request;
use \Lukiman\Cores\Authentication;
use \Lukiman\Cores\Cache;
use \Lukiman\Cores\Session;
use \Lukiman\Cores\Authorization\Role;

class Security {
	public static function login(String $token) : array {
		$auth = new Authentication();
		
		$auth->authWithToken($token);
	
		$cred = $auth->getCredentials();
		// print_r($cred);
		$sessionId = Session::generate();
		
		//authenticated and check db user exist & active
		if ($auth->isAuthenticated() AND static::isUserExistAndActive($cred->getUserName()) ) {
			
			//get authorization
			$roles = static::getAuthorizations($cred->getUserName());

			
			//save cred & authorization to cache with key session_id
			$cache = Cache::getInstance();
			$cacheKey = $sessionId;

			$entry = ['credential' => $cred, 'authorization' => $roles];
			
			$additionalInfos = static::getAdditionalInfos($cred->getUserName());
			if (!empty($additionalInfos)) $entry += $additionalInfos;

			$cache->set($cacheKey, $entry, SESSION_LENGTH);

			return [
				'status'	=> true,
				'message'	=> "OK",
				'sessionId'	=> $sessionId,
			];
		} else {
			return [
				'status'	=> false,
				'message'	=> "Failed",
			];
		}
	}
	
	public static function getStatus(Request $request) : array {
		$session = static::getSession($request);

		if (!empty($session)) {
			return ['session' => $session];
		} else {
			return ['status' => false, 'message' => "Invalid Session!"];
		}
		
	}
	
	public static function logout(Request $request) : String {
		$sessionId = static::getSessionId($request);
		$cache = Cache::getInstance();
		$cache->delete($sessionId);
		return "OK";
	}
	
	protected static function getSessionId(Request $request, String $authenticationHeader = 'Authentication') : String {
		$sessionId = '';
		$headers = $request->getHeaders();
		if (empty($headers['Cookie'])) {
			$headers['Cookie'] = $request->getSimpleCookies();
		}
		if (!empty($headers['Cookie'])) {
			foreach($headers['Cookie'] as $cookies) {
				$cookie = explode(";", $cookies);
				foreach($cookie as $curCookie) {
					$curCookie = trim($curCookie);
					if (substr($curCookie, 0, strlen(COOKIE_NAME) + 1) == (COOKIE_NAME . '=')) {
						$sessionId = substr($curCookie, strlen(COOKIE_NAME) + 1);
						break;
					}
				}
			}
		}
		if (empty($sessionId) AND !empty($request->getRequest()->getHeader($authenticationHeader))) {
			$sessionId = $request->getRequest()->getHeader($authenticationHeader);
			if (is_array($sessionId)) $sessionId = $sessionId[0];
			if (strtolower(substr($sessionId, 0, 7)) == 'bearer ') {
				$sessionId = substr($sessionId, 7);
				$sessionId = trim($sessionId);
			}
		}
		return $sessionId;
	}

	public static function getSession(Request $request) : ?array {
		$sessionId = static::getSessionId($request);
		$cache = Cache::getInstance();
		$content = [];
		if (!empty($sessionId)) $content = $cache->get($sessionId);
		if (empty($content)) return null;
		return $content;
	}

	protected static function isUserExistAndActive(String $userId) : bool {
		return true;
	}

	protected static function getAuthorizations(String $userId) : Role {
		return new Role('Base');
	}

	protected static function getAdditionalInfos(String $userId) : array {
		return [];
	}
}

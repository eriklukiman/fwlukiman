<?php
namespace Lukiman\Cores;

use \Lukiman\Cores\Request;
use \Lukiman\Cores\Authentication;
use \Lukiman\Cores\Interfaces\Authentication as IAuthentication;
use \Lukiman\Cores\Cache;
use \Lukiman\Cores\Session;
use \Lukiman\Cores\RefreshToken;
use \Lukiman\Cores\Authorization\Role;
use \Lukiman\Cores\Data\Authentication as AuthData;

class Security {
	public static function loginWithToken(String $token, ?array $config = null) : array {
		$auth = new Authentication($config);
		$auth->authWithToken($token);
		return static::proceedAuthentication($auth, $config);
	}

	public static function login(String $token, ?array $config = null) : array {
		return static::loginWithToken($token, $config);
	}

	public static function loginWithUserPassword(String $username, String $password, ?array $config = null) : array {
		$auth = new Authentication($config);
		$auth->authWithUserPassword($username, $password);
		return static::proceedAuthentication($auth, $config);
	}

	public static function refreshLogin(String $refreshToken, ?array $config = null) : array {
		$payload = RefreshToken::parse($refreshToken, $config);
		if (empty($payload)) {
			return [
				'status'	=> false,
				'message'	=> "Failed",
			];
		}

		$cred = static::createCredentialFromRefreshPayload($payload);
		if (empty($cred) OR empty($cred->getId()) OR !static::isUserExistAndActive($cred->getId())) {
			return [
				'status'	=> false,
				'message'	=> "Failed",
			];
		}

		$auth = new Authentication($config);
		$auth->grantAuthentication($cred);
		if (!$auth->isAuthenticated()) {
			return [
				'status'	=> false,
				'message'	=> "Failed",
			];
		}

		RefreshToken::revokeByPayload($payload);
		static::revokeSessionById($payload['relatedSession'] ?? '');

		return static::createSessionForCredential($auth->getCredentials(), $config);
	}

	public static function getRefreshTokenFromRequest(Request $request) : String {
		return RefreshToken::getFromRequest($request, static::getAuthenticationConfig());
	}

	public static function proceedAuthentication(IAuthentication $auth, ?array $config = null) : array {
		$cred = $auth->getCredentials();
		if (!$auth->isAuthenticated() OR empty($cred) OR empty($cred->getId()) OR !static::isUserExistAndActive($cred->getId())) {
			return [
				'status'	=> false,
				'message'	=> "Failed",
			];
		}

		return static::createSessionForCredential($cred, $config);
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
		$refreshToken = static::getRefreshTokenFromRequest($request);
		if (!empty($refreshToken)) {
			$payload = RefreshToken::parse($refreshToken, static::getAuthenticationConfig());
			if (!empty($payload)) {
				RefreshToken::revokeByPayload($payload);
			}
		}
		RefreshToken::revokeBySessionId($sessionId);
		static::revokeSessionById($sessionId);
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
			if (strtolower(substr($sessionId, 0, 6)) == 'bearer') $sessionId = substr($sessionId, 6);
			$sessionId = trim($sessionId);
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

	protected static function createSessionForCredential(AuthData $cred, ?array $config = null) : array {
		$sessionId = Session::generate();
		$roles = static::getAuthorizations($cred->getId());
		$cache = Cache::getInstance();
		$entry = ['credential' => $cred, 'authorization' => $roles];

		$additionalInfos = static::getAdditionalInfos($cred->getId());
		if (!empty($additionalInfos)) $entry += $additionalInfos;

		$ttl = SESSION_LENGTH;
		if (!empty($cred->getExpired())) $ttl = max(1, ($cred->getExpired() - time()));
		$cache->set($sessionId, $entry, $ttl);

		return [
			'status'		=> true,
			'message'		=> "OK",
			'sessionId'		=> $sessionId,
			'refreshToken'	=> RefreshToken::create($cred, $sessionId, $config),
		];
	}

	protected static function createCredentialFromRefreshPayload(array $payload) : ?AuthData {
		if (empty($payload['id'])) return null;

		$cred = new AuthData();
		$cred->setId($payload['id']);
		$cred->setCreated(time());
		$cred->setExpired(time() + SESSION_LENGTH);
		return $cred;
	}

	protected static function getAuthenticationConfig(?array $config = null) : array {
		if (!empty($config)) return $config;
		return Loader::Config('Authentication');
	}

	protected static function revokeSessionById(?String $sessionId) : void {
		if (empty($sessionId)) return;
		$cache = Cache::getInstance();
		$cache->delete($sessionId);
	}
}

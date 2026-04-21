<?php
namespace Lukiman\Cores;

use \Lukiman\Cores\Request;
use \Lukiman\Cores\Session;
use \Lukiman\Cores\Cache;
use \Lukiman\Cores\Data\Authentication as AuthData;

class RefreshToken {
	public static function create(AuthData $cred, String $sessionId, ?array $config = null) : String {
		$config = static::getConfig($config);
		$now = time();
		$ttl = static::getTTL($config);
		$nonce = Session::generate(24);
		$header = [
			'typ'	=> 'LukimanRefreshToken',
			'alg'	=> 'HS256',
		];

		$payload = [
			'id'				=> $cred->getId(),
			'relatedSession'	=> $sessionId,
			'authProvider'		=> $cred->getAuthProvider(),
			'iat'				=> $now,
			'exp'				=> $now + $ttl,
			'nonce'				=> $nonce,
		];

		$encodedHeader = static::base64UrlEncode(json_encode($header));
		$encodedPayload = static::base64UrlEncode(json_encode($payload));
		$signature = hash_hmac(
			'sha256',
			$encodedHeader . '.' . $encodedPayload,
			static::getSecret($config),
			true
		);

		$token = $encodedHeader . '.' . $encodedPayload . '.' . static::base64UrlEncode($signature);
		static::activate($payload, $ttl);
		return $token;
	}

	public static function parse(String $token, ?array $config = null) : ?array {
		$config = static::getConfig($config);
		$parts = explode('.', $token);
		if (count($parts) != 3) return null;

		[$encodedHeader, $encodedPayload, $encodedSignature] = $parts;
		$expectedSignature = static::base64UrlEncode(hash_hmac(
			'sha256',
			$encodedHeader . '.' . $encodedPayload,
			static::getSecret($config),
			true
		));

		if (!hash_equals($expectedSignature, $encodedSignature)) {
			return null;
		}

		$payload = json_decode(static::base64UrlDecode($encodedPayload), true);
		if (empty($payload) OR !is_array($payload)) {
			return null;
		}

		if (empty($payload['id']) OR empty($payload['iat']) OR empty($payload['exp']) OR empty($payload['nonce'])) {
			return null;
		}

		$now = time();
		if (($payload['iat'] > $now) OR ($payload['exp'] < $now)) {
			return null;
		}

		if (!static::isActive($payload)) {
			return null;
		}

		return $payload;
	}

	public static function getFromRequest(Request $request, ?array $config = null) : String {
		$headerName = static::getHeader($config);
		$refreshToken = $request->getRequest()->getHeader($headerName);
		if (is_array($refreshToken)) $refreshToken = $refreshToken[0] ?? '';
		return trim((string) $refreshToken);
	}

	public static function revokeByPayload(array $payload) : void {
		if (empty($payload['nonce'])) return;
		$cache = Cache::getInstance();
		$cache->delete(static::getCacheKey($payload));
		if (!empty($payload['relatedSession'])) {
			$cache->delete(static::getSessionCacheKey($payload['relatedSession']));
		}
	}

	public static function revokeBySessionId(?String $sessionId) : void {
		if (empty($sessionId)) return;
		$cache = Cache::getInstance();
		$key = static::getSessionCacheKey($sessionId);
		$nonce = $cache->get($key);
		if (!empty($nonce)) {
			$cache->delete('refresh-token:' . $nonce);
		}
		$cache->delete($key);
	}

	protected static function activate(array $payload, int $ttl) : void {
		if (empty($payload['nonce']) OR empty($payload['relatedSession'])) return;
		$cache = Cache::getInstance();
		$cache->set(static::getCacheKey($payload), 1, max(1, $ttl));
		$cache->set(static::getSessionCacheKey($payload['relatedSession']), $payload['nonce'], max(1, $ttl));
	}

	protected static function isActive(array $payload) : bool {
		if (empty($payload['nonce'])) return false;
		$cache = Cache::getInstance();
		return !empty($cache->get(static::getCacheKey($payload)));
	}

	protected static function getCacheKey(array $payload) : String {
		return 'refresh-token:' . $payload['nonce'];
	}

	protected static function getSessionCacheKey(String $sessionId) : String {
		return 'refresh-token-session:' . $sessionId;
	}

	protected static function getConfig(?array $config = null) : array {
		if (!empty($config)) return $config;
		return Loader::Config('Authentication');
	}

	protected static function getTTL(?array $config = null) : int {
		$config = static::getConfig($config);
		if (!empty($config['refreshTokenTTL'])) return intval($config['refreshTokenTTL']);
		return 2592000;
	}

	protected static function getHeader(?array $config = null) : String {
		$config = static::getConfig($config);
		if (!empty($config['refreshTokenHeader'])) return $config['refreshTokenHeader'];
		return 'Refresh-Token';
	}

	protected static function getSecret(?array $config = null) : String {
		$config = static::getConfig($config);
		if (!empty($config['refreshTokenSecret'])) return $config['refreshTokenSecret'];

		$rootPath = defined('LUKIMAN_ROOT_PATH') ? LUKIMAN_ROOT_PATH : __DIR__;
		$namespace = defined('LUKIMAN_NAMESPACE_PREFIX') ? LUKIMAN_NAMESPACE_PREFIX : __NAMESPACE__;
		return hash('sha256', $namespace . '|' . $rootPath . '|refresh-token');
	}

	protected static function base64UrlEncode(String $value) : String {
		return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
	}

	protected static function base64UrlDecode(String $value) : String {
		$remainder = strlen($value) % 4;
		if ($remainder > 0) {
			$value .= str_repeat('=', 4 - $remainder);
		}
		return base64_decode(strtr($value, '-_', '+/'));
	}
}

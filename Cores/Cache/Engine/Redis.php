<?php
namespace Lukiman\Cores\Cache\Engine;

use \Lukiman\Cores\Exception\Base as ExceptionBase;
use \Lukiman\Cores\Interfaces\Cache as ICache;

class Redis extends Base implements ICache {
	private \Swoole\Coroutine\Redis $cache;
	
	public function __construct(array $config) {
		$this->cache = new \Swoole\Coroutine\Redis();
		$this->cache->connect($config['host'], $config['port']);
	}
	
	public static function allowSingleton() {
		return false;
	}
	
	public function get(String $id) {
		return $this->cache->get($id);
	}
	
	public function set(String $id, $value, ?int $ttl = null) {
		return $this->cache->set($id, $value, $ttl);
	}
}
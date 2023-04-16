<?php
namespace Lukiman\Cores\Cache\Engine;

use \Lukiman\Cores\Exception\Base as ExceptionBase;
use \Lukiman\Cores\Interfaces\Cache as ICache;

class Memcache extends Base implements ICache {
	private \Memcache $cache;
	
	public function __construct(array $config) {
		$this->cache = memcache_connect($config['host'], $config['port']);
	}
	
	public static function allowSingleton() {
		return true;
	}
	
	public function get(String $id) {
		return $this->cache->get($id);
	}
	
	public function set(String $id, $value, ?int $ttl = 0) {
		return $this->cache->set($id, $value, false, $ttl);
	}
	
	public function delete(String $id) {
		return $this->cache->delete($id);
	}
}
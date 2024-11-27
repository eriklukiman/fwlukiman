<?php
namespace Lukiman\Cores\Cache\Engine;

use \Lukiman\Cores\Interfaces\Cache as ICache;

class Memcache extends Base implements ICache {
	private \Memcache $cache;

	public function __construct(array $config) {
		$this->cache = memcache_connect($config['host'], $config['port']);
	}

	public static function allowSingleton() : bool {
		return true;
	}

	public function get(String $id) : mixed {
		return $this->cache->get($this->getPrefix() . $id);
	}

	public function set(String $id, mixed $value, ?int $ttl = 0) : bool {
		return $this->cache->set($this->getPrefix() . $id, $value, false, $ttl);
	}

	public function delete(String $id) : bool {
		return $this->cache->delete($this->getPrefix() . $id);
	}
}

<?php
namespace Lukiman\Cores\Cache\Engine;

use \Lukiman\Cores\Interfaces\Cache as ICache;

class Redis extends Base implements ICache {
	private \Swoole\Coroutine\Redis $cache;

	public function __construct(array $config) {
		$this->cache = new \Swoole\Coroutine\Redis();
		$this->cache->connect($config['host'], $config['port']);
	}

	public static function allowSingleton() : bool {
		return false;
	}

	public function get(String $id) : mixed {
		return $this->cache->get($this->getPrefix() . $id);
	}

	public function set(String $id, mixed $value, ?int $ttl = null) : bool {
		return $this->cache->set($this->getPrefix() . $id, $value, $ttl);
	}

	public function delete(String $id) : bool {
		return $this->cache->del($this->getPrefix() . $id);
	}
}

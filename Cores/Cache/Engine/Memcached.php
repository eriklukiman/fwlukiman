<?php
namespace Lukiman\Cores\Cache\Engine;

use \Lukiman\Cores\Interfaces\Cache as ICache;

class Memcached extends Base implements ICache {
	private $cache;
	
	public function __construct(array $config) {
		$mem_var = new \Memcached();
		$mem_var->addServer($config['host'], $config['port']);
		$this->cache = $mem_var;
	}
	
	public static function allowSingleton() {
		return true;
	}
	
	public function get(String $id) {
		return $this->cache->get($this->getPrefix() . $id);
	}
	
	public function set(String $id, $value, ?int $ttl = 0) {
		return $this->cache->set($this->getPrefix() . $id, $value, 0);
	}
	
	public function delete(String $id) {
		return $this->cache->delete($this->getPrefix() . $id);
	}
}
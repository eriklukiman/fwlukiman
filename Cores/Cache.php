<?php
namespace Lukiman\Cores;

class Cache implements Interfaces\Cache {
	static protected array $config;
	static protected Cache $instance;
	
	protected Interfaces\Cache $cache;
	
	public function __construct (?array $config) {
		if (empty($config)) {
			if (empty(static::$config)) {
				static::$config = Loader::Config('Cache');
			}
			$config = static::$config;
		}
		$this->cache = Cache\Factory::instantiate($config);
		return $this;
	}
	
	public static function getInstance(?array $config = null) {
		if (empty($config)) {
			if (empty(static::$config)) {
				static::$config = Loader::Config('Cache');
			}
			$config = static::$config;
		}

		if (Cache\Factory::allowSingleton($config)) {
			if(empty(static::$instance)) {
				static::$instance = new static($config);
			}
			return static::$instance;
		} else {
			return new static($config);
		}
	}
	
	public static function setConfig(array $config) : void {
		static::$config = $config;
	}
	
	public function get(String $id) {
		return $this->cache->get($id);
	}
	
	public function set(String $id, $value, ?int $ttl = null) {
		return $this->cache->set($id, $value, $ttl);
	}
	
	public function delete(String $id) {
		return $this->cache->delete($id);
	}
}
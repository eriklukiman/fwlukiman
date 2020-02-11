<?php
namespace Lukiman\Cores\Cache\Base;

use \Lukiman\Cores\Interfaces\Cache as ICache;

class Apc extends Base implements ICache {
	
	public function __construct(array $config) {
	}
	
	public static function allowSingleton() {
		return true;
	}
	
	public function get(String $id) {
		return apc_fetch($id);
	}
	
	public function set(String $id, $value, ?int $ttl = 0) {
		return apc_store($id, $value, $ttl);
	}

}
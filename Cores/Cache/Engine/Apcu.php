<?php
namespace Lukiman\Cores\Cache\Engine;

use \Lukiman\Cores\Interfaces\Cache as ICache;

class Apcu extends Base implements ICache {
	
	public function __construct(array $config) {
	}
	
	public static function allowSingleton() {
		return true;
	}
	
	public function get(String $id) {
		return apcu_fetch($id);
	}
	
	public function set(String $id, $value, ?int $ttl = 0) {
		return apcu_store($id, $value, $ttl);
	}

	public function delete(String $id) {
		return apcu_delete($id);
	}
}
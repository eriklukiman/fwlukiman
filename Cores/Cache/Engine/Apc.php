<?php
namespace Lukiman\Cores\Cache\Engine;

use \Lukiman\Cores\Interfaces\Cache as ICache;

class Apc extends Base implements ICache {

	public function __construct(array $config) {
	}

	public static function allowSingleton() : bool {
		return true;
	}

	public function get(String $id) : mixed {
		return apc_fetch($this->getPrefix() . $id);
	}

	public function set(String $id, mixed $value, ?int $ttl = 0) : bool {
		return apc_store($this->getPrefix() . $id, $value, $ttl);
	}

	public function delete(String $id) : bool {
		return apc_delete($this->getPrefix() . $id);
	}
}

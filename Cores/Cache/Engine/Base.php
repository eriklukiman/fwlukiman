<?php
namespace Lukiman\Cores\Cache\Engine;

use \Lukiman\Cores\Exception\Base as ExceptionBase;
use \Lukiman\Cores\Interfaces\Cache as ICache;

abstract class Base implements ICache {
	abstract public static function allowSingleton();
	
	abstract public function get(String $id);
	
	abstract public function set(String $id, $value, ?int $ttl);
	
	abstract public function delete(String $id);
}

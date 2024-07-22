<?php
namespace Lukiman\Cores\Cache\Engine;

use Lukiman\Cores\Loader;
use \Lukiman\Cores\Exception\Base as ExceptionBase;
use \Lukiman\Cores\Interfaces\Cache as ICache;

abstract class Base implements ICache {
	abstract public static function allowSingleton();
	
	abstract public function get(String $id);
	
	abstract public function set(String $id, $value, ?int $ttl);
	
	abstract public function delete(String $id);

	/**
	 * Get prefix for Cache key
	 *
	 * @return string "namespace_" | "config_" 
	 * */
	public function getPrefix(): string {
		// check if there is an existing config prefix then use it
		$prefix = Loader::Config('Cache')['prefix'] ?? '';

		// automatically use namespace prefix if config prefix is empty
		if (empty($prefix) && defined('LUKIMAN_NAMESPACE_PREFIX')) {
			$prefix = LUKIMAN_NAMESPACE_PREFIX;
		}

		if (!empty($prefix)) {
			$prefix .= '_';
		}

		return strtolower($prefix);
	}
}

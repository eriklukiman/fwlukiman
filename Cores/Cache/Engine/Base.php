<?php
namespace Lukiman\Cores\Cache\Engine;

use Lukiman\Cores\Loader;
use \Lukiman\Cores\Interfaces\Cache as ICache;

abstract class Base implements ICache {
	abstract public static function allowSingleton() : bool;

	abstract public function get(String $id) : mixed;

	abstract public function set(String $id, mixed $value, ?int $ttl) : bool;

	abstract public function delete(String $id) : bool;

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

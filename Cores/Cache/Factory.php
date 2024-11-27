<?php
namespace Lukiman\Cores\Cache;

use \Lukiman\Cores\Interfaces\Cache;

class Factory {
	private static String $path = '\\Lukiman\\Cores\\Cache\\Engine\\';

	public static function instantiate(array $config) : Cache {
		$class = static::$path . ucfirst(strtolower($config['engine']));
		return new $class($config);
	}

	public static function allowSingleton(array $config) : bool {
		$class = static::$path . ucfirst(strtolower($config['engine']));
		return $class::allowSingleton();
	}
}

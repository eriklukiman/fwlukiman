<?php
namespace Lukiman\Cores\Cache;

use \Lukiman\Cores\Exception\Base as ExceptionBase;
use \Lukiman\Cores\Interfaces\Cache as ICache;

class Factory {
	private static String $path = '\\Lukiman\\Cores\\Cache\\Engine\\';
	
	public static function instantiate(array $config) {
		$class = static::$path . ucfirst(strtolower($config['engine']));
		return new $class($config);
	}
	
	public static function allowSingleton(array $config) {
		$class = static::$path . ucfirst(strtolower($config['engine']));
		return $class::allowSingleton();
	}
}

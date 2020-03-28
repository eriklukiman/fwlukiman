<?php
namespace Lukiman\Cores\Authentication;

use \Lukiman\Cores\Exception\Base as ExceptionBase;

class Factory {
	private static String $path = '\\Lukiman\\Cores\\Authentication\\Provider\\';
	
	public static function instantiate(array $config) {
		$class = static::$path . ucfirst(strtolower($config['provider']));
		return new $class($config);
	}
}

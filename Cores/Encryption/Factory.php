<?php
namespace Lukiman\Cores\Encryption;

use \Lukiman\Cores\Exception\Base as ExceptionBase;

class Factory {
	private static String $path = '\\Lukiman\\Cores\\Encryption\\Engine\\';
	private static String $defaultEngine = 'Openssl';
	
	public static function instantiate(array $config) {
		//if (empty($config['engine'])) $config['engine'] = static::$defaultEngine;
		$class = static::$path . ucfirst(strtolower($config['engine']));
		return new $class($config);
	}
	
	public static function allowSingleton(array $config) {
		$class = static::$path . ucfirst(strtolower($config['engine']));
		return $class::allowSingleton();
	}
}

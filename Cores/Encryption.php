<?php
namespace Lukiman\Cores;

class Encryption implements Interfaces\Encryption {
	static protected array $config;

	protected Interfaces\Encryption $instance;

	public function __construct (?array $config = null) {
		if (empty($config)) {
			if (empty(static::$config)) {
				static::$config = Loader::Config('Encryption');
			}
			$config = static::$config;
		}
		$this->instance = Encryption\Factory::instantiate($config);
		return $this;
	}

	public static function getInstance(?array $config = null) : self {
		if (empty($config)) {
			if (empty(static::$config)) {
				static::$config = Loader::Config('Encryption');
			}
			$config = static::$config;
		}

        return new static($config);
	}

	public static function setConfig(array $config) : void {
		static::$config = $config;
	}

    public function encrypt(String $str) : String {
        return $this->instance->encrypt($str);
    }

    public function decrypt(String $str) : String {
        return $this->instance->decrypt($str);
    }

}

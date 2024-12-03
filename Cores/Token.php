<?php
namespace Lukiman\Cores;

class Token {
	static protected array $config;
    static protected int $defaultTimeout = 60; // in second
    protected int $timeout; //in second
    protected int $shift; //in second
	static protected Token $instance;

	public function __construct (?array $config) {
		if (empty($config)) {
			if (empty(static::$config)) {
				static::$config = Loader::Config('Token');
			}
			$config = static::$config;
		}
        if (empty($config)) $config['timeout'] = static::$defaultTimeout;
        $this->timeout = $config['timeout'];
        $this->shift = $config['shift'];
        return $this;
	}

	public static function getInstance(?array $config = null) : self {
		if (empty($config)) {
			if (empty(static::$config)) {
				static::$config = Loader::Config('Token');
			}
			$config = static::$config;
		}

        if(empty(static::$instance)) {
            static::$instance = new static($config);
        }
        return static::$instance;
}

    public static function isValid(String $token) : bool {
        static::getInstance();
        $now = static::generate();
        $valid_length = strlen($now);
        if (strlen($token) != $valid_length) return false;
        $diff = $now - intval($token);
        return (($diff >= 0) AND ($diff <= static::$instance->timeout));
    }

    public static function generate() : String {
        static::getInstance();
        return time() + static::$instance->shift;
    }
}

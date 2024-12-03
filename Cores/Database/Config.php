<?php
namespace Lukiman\Cores\Database;

class Config {
	protected $engine = 'mysql';
	protected $driver = 'pdo'; //pdo, swoole
	protected $host = 'localhost';
	protected $port = 3306;
	protected $user;
	protected $password;
	protected $database;
	protected $timeout = 2;
	protected $options = [];

	public function __construct(array $conf) {
		if (isset($conf['engine'])) $this->engine = $conf['engine'];
		if (isset($conf['driver'])) $this->driver = $conf['driver'];
		if (isset($conf['host'])) $this->host = $conf['host'];
		if (isset($conf['port'])) $this->port = $conf['port'];
		if (isset($conf['user'])) $this->user = $conf['user'];
		if (isset($conf['password'])) $this->password = $conf['password'];
		if (isset($conf['database'])) $this->database = $conf['database'];
		if (isset($conf['timeout'])) $this->timeout = $conf['timeout'];
		if (isset($conf['options'])) $this->options = $conf['options'];
	}

	public function __get(String $key) : mixed {
        return $this->{$key};
    }

    public function __set(String $key, mixed $value) : void {
        $this->{$key} = $value;
    }
}

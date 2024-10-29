<?php
namespace Lukiman\Cores\Database\Driver;

use \Lukiman\Cores\Interfaces\Database\{Basic, Transaction};
use \Lukiman\Cores\Database\Config;
use \Lukiman\Cores\Loader;
use \Lukiman\Cores\Exception\Base as ExceptionBase;

class PDO extends \PDO implements Basic, Transaction {
	protected static $_instance;
	protected static $_lastSetting = 'default';
	protected static $_databaseSetting = null;
	protected $_inTransaction = false;

	protected static $_maxConnection = 20;
	protected static $_createdConnection = null;

	public function __construct($dbType = 'mysql', $host = 'localhost', $user = '', $password = '', $dbName = '', $port = '', $options = array()) {
		if (empty($port) AND ($dbType == 'mysql')) $port = 3306;
		$dsn = $dbType . ':host=' . $host . ';dbname=' . $dbName;
		if (!empty($port)) $dsn .= ';port=' . $port;
		if (!isset($options[PDO::ATTR_DEFAULT_FETCH_MODE])) $options[PDO::ATTR_DEFAULT_FETCH_MODE] = PDO::FETCH_OBJ;
		try {
			parent::__construct($dsn, $user, $password, $options);
		} catch (\Exception $e) {
			if ($e instanceof \PDOException)
				throw new ExceptionBase($e->getMessage());
			die(__CLASS__ . ' : ' . $e->getMessage());
		}
		return $this;
	}

	public static function getInstance(?Config $config = null) : Object {
		if(empty(static::$_instance)) {
			$usedSetting = $config;
			if (is_null($usedSetting)) {
				if (empty(static::$_databaseSetting)) {
					static::$_databaseSetting = new Config(Loader::Config('Database'));
				}
				$usedSetting = static::$_databaseSetting;
			}
			if (empty($usedSetting)) throw new ExceptionBase("Failed to initialize DB connection");
			static::$_instance = new static($usedSetting->engine, $usedSetting->host, $usedSetting->user, $usedSetting->password, $usedSetting->database, $usedSetting->port, $usedSetting->options);
		}
		return static::$_instance;
	}

	public function toQuote($string) : String {
		return parent::quote($string);
	}

	public function inTransaction() : bool {
		return $this->_inTransaction;
	}

	public function beginTransaction  () : bool {
		try {
            parent::beginTransaction ();
        } catch (\PDOException $e) {
			return false;
		}
		$this->_inTransaction = true;
		return true;
	}

	public function commit ($timeout = null) : bool {
		parent::commit();
		$this->_inTransaction = false;
		return true;
	}

	public function rollBack ($timeout = null) : bool {
		parent::rollBack();
		$this->_inTransaction = false;
		return true;
	}

	public function releaseConnection() {
	}

	public function close() : bool {
		try {
			$this->query("KILL CONNECTION_ID()");
		} catch (\PDOException $e) {
			//error_log(__CLASS__ . ' : ' . $e->getMessage());
		}
		static::$_instance = null;
		return true;
	}

    public function ping() : bool {
        try {
            $this->query('SELECT 1');
        } catch (\PDOException $e) {
            $this->close();
            return false;
        }
        return true;
    }


	public static function getStats() {
		// echo 'Stack size: ';
		// print_r(self::$_instance[$setting]->length()); echo "\n";
	}

	public static function setConfig(Config $config) {
		static::$_databaseSetting = $config;
	}

	public static function setParameters($instance, $maxConn, $createdConn) {
		// static::$_instance = $instance;
		static::$_maxConnection = $maxConn;
		static::$_createdConnection = $createdConn;
	}
}

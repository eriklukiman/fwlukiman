<?php
namespace Lukiman\Cores\Database\Driver;

use \Lukiman\Cores\Interfaces\Database\{Basic, Transaction};
use \Lukiman\Cores\Database\Config;
use \Lukiman\Cores\Loader;

class PDO extends \PDO implements Basic, Transaction {
	protected static $_instance = array();
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
		} catch (Exception $e) {
			if ($e instanceof PDOException) 
				throw new Exception_Base("Failed to initialize DB connection");
			die(__CLASS__ . ' : ' . $e->getMessage());
		}
		return $this;
	}
	
	public static function getInstance(?Config $config = null) : Object { 
		if(empty(self::$_instance)) {
			if (empty(self::$_databaseSetting)) {
				self::$_databaseSetting = new Config(Loader::Config('Database'));
			}
			$usedSetting = self::$_databaseSetting;
			static::$_instance = new static($usedSetting->engine, $usedSetting->host, $usedSetting->user, $usedSetting->password, $usedSetting->database, $usedSetting->port, $usedSetting->options); 
		}
		return self::$_instance; 
	} 
	
	public function toQuote($string) : String {
		return parent::quote($string);
	}
  
	public function inTransaction() : bool {
		return $this->_inTransaction;
	}
	
	public function beginTransaction  () : void {
		try {
            parent::beginTransaction ();
        } catch (PDOException $e) {}
		$this->_inTransaction = true;
	}
	
	public function commit ($timeout = null) : void {
		parent::commit();
		$this->_inTransaction = false;
	}
	
	public function rollBack ($timeout = null) : void {
		parent::rollBack();
		$this->_inTransaction = false;
	}
	
	public function releaseConnection() {
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

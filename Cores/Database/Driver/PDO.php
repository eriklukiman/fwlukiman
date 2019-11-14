<?php
namespace Lukiman\Cores\Database\Driver;

use \Lukiman\Cores\Interfaces\Database\{Basic, Transaction};
use \Lukiman\Cores\Loader;

class PDO extends \PDO implements Basic, Transaction {
	protected static $_instance = array();
	protected static $_lastSetting = 'default';
	protected static $_databaseSetting = null;
	protected $_inTransaction = false;
	
	public function __construct($dbType = 'mysql', $host = 'localhost', $user = '', $password = '', $dbName = '', $port = '', $options = array()) {
		if (empty($port) AND ($dbType == 'mysql')) $port = 3306;
		$dsn = $dbType . ':host=' . $host . ';dbname=' . $dbName;
		if (!empty($port)) $dsn .= ';port=' . $port;
		if (!isset($options[PDO::ATTR_DEFAULT_FETCH_MODE])) $options[PDO::ATTR_DEFAULT_FETCH_MODE] = PDO::FETCH_OBJ;
		try {
			parent::__construct($dsn, $user, $password, $options);
		} catch (Exception $e) {
			// var_dump($e);
			if ($e instanceof PDOException) 
				// exit('Database Setting is invalid : ' . $e->getMessage());
			//throw new Database_Error($e);
			die(__CLASS__ . ' : ' . $e->getMessage());
		}
		// self::$_instance[self::$_lastSetting] = $this;
		return $this;
	}
	
	public static function activate($setting = 'default') : void {
		if (!empty($setting)) self::$_lastSetting = $setting;
	}
	
	public static function getInstance($setting = 'default') : Object { 
		if (empty($setting)) $setting = self::$_lastSetting;
		
		if(empty(self::$_instance[$setting])) {
			if (empty(self::$_databaseSetting)) {
				self::$_databaseSetting = Loader::Config('Database');
			}
			if (!array_key_exists($setting, self::$_databaseSetting)) $setting = 'default';
			$usedSetting = self::$_databaseSetting[$setting];
			self::$_lastSetting = $setting;
			self::$_instance[$setting] = new self($usedSetting['driver'], $usedSetting['connection']['host'], $usedSetting['connection']['user'], $usedSetting['connection']['password'], $usedSetting['connection']['database'], $usedSetting['connection']['port'], $usedSetting['options']); 
		}
		return self::$_instance[$setting]; 
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
	
}

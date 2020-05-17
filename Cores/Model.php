<?php
namespace Lukiman\Cores;

use \Lukiman\Cores\Database;
use \Lukiman\Cores\Database\Query as Database_Query;
use \Lukiman\Cores\Exception\Base as ExceptionBase;

class Model {
	protected static $_path = 'Models/';
	protected static $_prefixClass = '\\' . LUKIMAN_NAMESPACE_PREFIX . '\\Models\\';
	protected $_table = null;
	protected $_prefix = null;
	protected $_db = null;
	
	public function __construct() {
		// $this->_db = Database::getInstance();
	}
	
	public function getTable() {
		return $this->_table;
	}
	
	public static function getPath() {
		return self::$_path;
	}
	
	public static function getPrefixClass() {
		return self::$_prefixClass;
	}
	
	public function getPrefix() {
		return $this->_prefix;
	}
	
	public static function load($name) {
		// Add the model prefix
		$class = self::$_prefixClass . $name;
		
		$f = self::getPath() . $name . '.php';
		if (!is_readable($f)) $f = str_replace('_', '/', $f);
		if (is_readable($f)) include_once($f);
		// var_dump($class);
		// $class = '\Lukiman\\' . $class; 
		// echo ':'.$class.':';
		if (class_exists($class)) {
			return new $class;
		} else {
			throw new ExceptionBase('Model not found!');
		}
	}
	
	public function getData ($id, array $cols = null) {
		if (is_null($this->_db)) $this->_db = Database::getInstance();
		$q = Database_Query::Select($this->_db, $this->_table);//var_dump($q);
		if (is_array($id)) $q->where($id);
		else $q->where($this->_prefix . 'Id', $id);
		if (!empty($cols)) $q->columns($cols);
		
		// return $q->execute()->next();
		$data = $q->execute();
		$db->releaseConnection();
		return $data;
	}
	
	public function getServerTimestamp() {
		$q = $this->query('SELECT NOW() AS time ');
		$this->releaseConnection();
		foreach($q as $v) return $v->time;
	}
}

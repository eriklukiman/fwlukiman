<?php
namespace Lukiman\Cores;

use \Lukiman\Cores\Database;
use \Lukiman\Cores\Database\Query as Database_Query;

class Model {
	protected static $_path = 'Models/';
	protected static $_prefixClass = '\\Lukiman\\Models\\';
	protected $_table = null;
	protected $_prefix = null;
	
	public static function getPath() {
		return self::$_path;
	}
	
	public static function getPrefix() {
		return self::$_prefixClass;
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
		return new $class;
	}
	
	public function getData ($id, array $cols = null) {
		$q = Database_Query::Select($this->_table);//var_dump($q);
		if (is_array($id)) $q->where($id);
		else $q->where($this->_prefix . 'Id', $id);
		if (!empty($cols)) $q->columns($cols);
		
		return $q->execute()->next();
	}

	public function getServerTimestamp() {
		$q = Database::getInstance()->query('SELECT NOW() AS time ');
		foreach($q as $v) return $v->time;
	}
}

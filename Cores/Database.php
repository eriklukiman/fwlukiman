<?php
namespace Lukiman\Cores;

use \Lukiman\Cores\Interfaces\Database\{Basic, Transaction, Operation};

class Database extends Database\Base implements Basic, Transaction, Operation {
	/*protected $db;
	
	public function __construct() {
		$param = func_get_args();
		if (empty($param) AND !empty(self::$_instance)) return self::$_instance;
		
		call_user_func_array(array('parent', '__construct'), $param);
		return $this;
	}
	
    public function __call($method_name, $args) {
       echo 'Calling method ',$method_name,'<br />';
       return call_user_func_array(array($this->db, $method_name), $args);
    }
	
	public static function activate(String $setting = 'default') : void {
		
	}
	
	public static function getInstance(String $setting = 'default') : Object {
		return self::db->getInstance($setting);
	}
	
	public function toQuote($string) : String {
		
	}

	public static function Insert (Database $db, String $table, array $arrValues) : int {
		
	}
	
	public static function Update (Database $db, String $table, array $arrValues, String $where, String $join) : int {
		
	}
	
	public static function Delete (Database $db, String $table, String $where, String $limit) : int {
		
	}
	
	public static function Select (Database $db, String $table, String $arrColumn, String $where, array $bindVars, String $join, String $order, String $group, String $having, String $limit, bool $isGrid) : Object {
		
	}
	
	public static function generateWhere (String $where, $db) : String {
		
	}
	
	public function inTransaction() : bool {
		
	}
	
	public function beginTransaction() : void {
		
	}
	
	public function commit (int $timeout) : void {
		
	}
	
	public function rollBack (int $timeout) : void {
		
	}*/

	
}

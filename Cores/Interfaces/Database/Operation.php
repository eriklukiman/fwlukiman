<?php
namespace Lukiman\Cores\Interfaces\Database;

use \Lukiman\Cores\Database;

interface Operation {
	public static function Insert (Database $db, String $table, array $arrValues) : int;
	
	public static function Update (Database $db, String $table, array $arrValues, String $where, String $join) : int;
	
	public static function Delete (Database $db, String $table, String $where, String $limit) : int;
	
	public static function Select (Database $db, String $table, String $arrColumn, String $where, array $bindVars, String $join, String $order, String $group, String $having, String $limit, bool $isGrid) /*: Object*/;
	
	public static function generateWhere (String $where, $db) : String;
}

<?php
namespace Lukiman\Cores\Interfaces\Database;

interface Operation {
	public static function Insert (String $table, array $arrValues) : int;
	
	public static function Update (String $table, array $arrValues, String $where, String $join) : int;
	
	public static function Delete (String $table, String $where, String $limit) : int;
	
	public static function Select (String $table, String $arrColumn, String $where, String $join, String $order, String $group, String $having, String $limit, bool $isGrid) : Object;
	
	public static function generateWhere (String $where, $db) : String;
}

<?php
namespace Lukiman\Cores\Interfaces\Database;

use \Lukiman\Cores\Database;

interface Operation {
	public static function Insert (Database $db, String $table, array $arrValues) : int|bool;

	public static function Update (Database $db, String $table, array $arrValues, array|String $where, array $bindVars, String $join) : int;

	public static function Delete (Database $db, String $table, array|String $where, array $bindVars, null|int|array $limit) : int;

	public static function Select (Database $db, String $table, array|String $arrColumn, array|String $where, array $bindVars, String $join, String $order, String $group, String $having, null|array|int $limit, bool $isGrid) : mixed;

	public static function generateWhere (array|String $where, ?Database $db) : String;
}

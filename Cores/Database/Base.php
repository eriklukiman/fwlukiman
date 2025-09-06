<?php
namespace Lukiman\Cores\Database;

use \Lukiman\Cores\Exception\Base as ExceptionBase;
use \Lukiman\Cores\Database;
use \Lukiman\Cores\Database\Driver\PDO;
use \Lukiman\Cores\Database\Driver\Swoole;
use \Lukiman\Cores\Interfaces\Database\{Basic, Transaction, Operation};

class Base extends PDO /*Swoole*/ implements Basic, Transaction, Operation {

	public function __construct() {
		$param = func_get_args();
		if (empty($param) AND !empty(self::$_instance)) return self::$_instance;

		call_user_func_array(array(parent::class, '__construct'), $param);
		return $this;
	}

	public static function Insert (Database $db, String $table = '', array $arrValues = []) : int|bool {
		$startTrans = false;
		if (!$db->inTransaction()) {
			$db->beginTransaction ();
			$startTrans = true;
		}
		$commit = true;
		$arrData = array();
		$bindVars = array();

		$fields = '';
		$values = '';
		if (is_array($arrValues)) {
			$keys = array_keys($arrValues);
			foreach ($arrValues AS $key => $val) {
				$_var = self::generateRandomVariable();
				if (is_array($val) AND isset($val['exp'])) {
					$bindVars[$_var] = $val['exp'];
					$arrData[] = $_var;
                } else if($val === null) {
                    $arrData[] = 'null';
                } else {
					if ($val == 'NOW()') $arrData[] = 'NOW()';
					else {
						$bindVars[$_var] = $val;
						$arrData[] = $_var;
					}
				}
			}
			$fields = implode(', ', $keys);
			$values = implode(', ', $arrData);
		} else {
			$commit = false;
		}

		$q = $db->prepare('INSERT INTO ' . $table . ' (' . $fields . ') VALUES (' . $values . ')' );
		foreach ($bindVars as $kVar => $vVar) {
			$q->bindValue($kVar, $vVar);
		}
		$q->execute();
		$insertId = $db->lastInsertId();

		if ($q->rowCount() != 1) $commit = false;

		if ($startTrans) {
			if ($commit) $db->commit();
			else $db->rollBack();
		}
		if ($q->errorCode() != 0) {
			$err = $q->errorInfo();
			throw new ExceptionBase($err[0] . ' (' . $err[1] . ') ' . $err[2], $err[1]);
		}
		if (!$commit) return false;
		if (!empty($insertId)) return $insertId;
		return true;
	}

	public static function Update (Database $db, String $table = '', array $arrValues = [], array|String $where = ' TRUE ', array $bindVars = [], String $join = '') : int {
		$startTrans = false;
		if (!$db->inTransaction()) {
			$db->beginTransaction ();
			$startTrans = true;
		}
		$commit = true;
		$arrSetValues = array();

		if (is_array($arrValues)) {
			foreach ($arrValues as $key => $val) {
				$_var = self::generateRandomVariable();
				if (is_array($val)) {
					$bindVars[$_var] = $val['exp'];
					$arrSetValues[] = $key . ' = ' . $_var;
                } else if($val === null) {
                    $arrSetValues[] = $key . ' = null';
				} else {
					if ($val == 'NOW()') $arrSetValues[] = $key . ' = NOW()';
					else {
						$bindVars[$_var] = $val;
						$arrSetValues[] = $key . ' = ' . $_var;
					}
				}
			}
		} else {
			if (empty($arrValues)) $commit = false;
			else {
				$_var = self::generateRandomVariable();
				$bindVars[$_var] = $arrValues;
				$arrSetValues[] = $_var;
			}
		}

		$q = $db->prepare('UPDATE ' . $table . ($join == '' ? '' : $join) . ' SET ' . implode(', ', $arrSetValues) . ' WHERE ' . self::generateWhere($where, $db) );
		foreach ($bindVars as $kVar => $vVar) {
			$q->bindValue($kVar, $vVar);
		}
		$q->execute();

		if ($q->errorCode() != 0) $commit = false;
		$affectedRows = $q->rowCount();

		if ($startTrans) {
			if ($commit) $db->commit();
			else $db->rollBack();
		}
		if ($q->errorCode() != 0) {
			$err = $q->errorInfo();
			throw new ExceptionBase($err[0] . ' (' . $err[1] . ') ' . $err[2], $err[1]);
		}
		if ($commit AND $affectedRows) return $affectedRows;
		else return false;
	}

	public static function Delete (Database $db, String $table = '', array|String $where = '', array $bindVars = [], null|int|array $limit = null) : int {
		$useLimit = '';
		if (!empty($limit)) {
			if (!is_array($limit)) $useLimit .= ' LIMIT :_usedLimit ';
		}

		$startTrans = false;
		if (!$db->inTransaction()) {
			$db->beginTransaction ();
			$startTrans = true;
		}
		$commit = true;
		$q  = $db->prepare('DELETE FROM ' . $table . ' WHERE ' . self::generateWhere($where, $db) . $useLimit);
		foreach ($bindVars as $kVar => $vVar) {
			$q->bindValue($kVar, $vVar);
		}
		if (!empty($limit)) {
			$q->bindValue(':_usedLimit', (int) $limit, PDO::PARAM_INT);
		}
		$q->execute();

		if ($q->errorCode() != 0) $commit = false;
		$affectedRows = $q->rowCount();

		if ($startTrans) {
			if ($commit) $db->commit();
			else $db->rollBack();
		}
		if ($q->errorCode() != 0) {
			$err = $q->errorInfo();
			throw new ExceptionBase($err[0] . ' (' . $err[1] . ') ' . $err[2], $err[1]);
		}
		if ($commit AND $affectedRows) return $affectedRows;
		else return 0;
	}

	public static function Select (Database $db, String $table = '', array|String $arrColumn = '*', array|String $where = ' TRUE ', array $bindVars = [], String $join = '', String $order = '', String $group = '', String $having = '', null|array|int $limit = null, bool $isGrid = false) : mixed {
		$arrData = array();

		if (!empty($arrColumn) AND is_array($arrColumn)) {
			foreach ($arrColumn as $key => $val) {
				if (is_array($val)) {
					$arrData[] = $val['exp'] . (!empty($val['as']) ? (' AS ' . $val['as']) : '');
				} else {
					if (is_int($key)) $arrData[] = $val;
					else $arrData[] = $key;
				}
			}
		} else {
			if (!empty($arrColumn) )$arrData[] = $arrColumn;
			else $arrData[] = '*';
		}
		$groupBy = '';
		$orderBy = '';
		$useHaving = '';
		$useLimit = '';
		if (!empty($group)) $groupBy .= ' GROUP BY ' . $group;
		if (!empty($order)) $orderBy .= ' ORDER BY ' . $order;
		if (!empty($having)) $useHaving .= ' HAVING ' . $having;
		if (!empty($limit)) {
			if (is_array($limit) AND isset($limit[1])) $useLimit .= ' LIMIT :_usedLimit0, :_usedLimit1 ';
			else $useLimit .= ' LIMIT :_usedLimit0 ';
		}

		$q = $db->prepare('SELECT ' . ($isGrid ? ' SQL_CALC_FOUND_ROWS ' : '') . implode(', ', $arrData) . ' FROM ' . $table . ($join == '' ? '' : $join) . ' WHERE ' . self::generateWhere($where, $db) . $groupBy . $orderBy . $useHaving . $useLimit );

		if ($q === false) {
			var_dump($db);
			return new \stdClass();
		}
		foreach ($bindVars as $kVar => $vVar) {
			$q->bindValue($kVar, $vVar);
		}

		if (!empty($limit)) {
			if ($q === false) {
				print_r($db);
				throw new ExceptionBase('Database connection error!');
			}

			$q->bindValue(':_usedLimit0', (int) $limit[0], PDO::PARAM_INT);
			if (isset($limit[1])) $q->bindValue(':_usedLimit1', (int) $limit[1], PDO::PARAM_INT);
		}
		try {
			$q->execute();
		} catch (ExceptionBase $e) {
			if ($e instanceof ExceptionBase) die(__CLASS__ . ' : ' . $e->getMessage());
		} catch (\Exception $e) {
		    if ($e instanceof \Exception) throw new ExceptionBase($e->getMessage());
			die(__CLASS__ . ' : ' . $e->getMessage());
		} finally {
			if (!$isGrid) $db->releaseConnection();
		}

		if ($q->errorCode() != 0) {
			$err = $q->errorInfo();
			throw new ExceptionBase($err[0] . '(' . $err[1] . '): ' . $err[2], $err[1]);
		}
		return $q;

	}

	public static function generateWhere (array|String $where = ' TRUE ', ?Database $db = null) : String {
		$dbSupplied = true;
		if (is_null($db)) {
			$db = self::getInstance('');
			$dbSupplied = false;
		}
		$retVal = ' TRUE ';
		if (is_array($where)) {
			foreach($where as $k => $v) {
				if (is_array($v)) {
					foreach ($v as $kV => $vV) $v[$kV] = $db->toQuote($vV);
					$retVal .= ' AND ' . $k . ' IN (' . implode(', ', $v) . ' ) ' ;
				} else {
					$retVal .= ' AND ' . $k . ' = ' . $db->toQuote($v);
				}
			}
			if (!$dbSupplied) $db->releaseConnection();
			return $retVal;
		}
		if (!$dbSupplied) $db->releaseConnection();
		return $where;
	}

	public static function generateRandomVariable(int $length = 10) : String {
		return ':' . self::generateRandomString($length);
	}

	public static function generateRandomString(int $length = 10) : String {
		$ret = bin2hex(random_bytes(ceil($length / 2)));
		return substr($ret, 0, $length);
	}
}

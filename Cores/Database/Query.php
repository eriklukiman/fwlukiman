<?php
namespace Lukiman\Cores\Database;

use \Lukiman\Cores\Database;
use \Lukiman\Cores\Exception\Base as ExceptionBase;
use \Lukiman\Cores\Database\Query\{Grid, Insert, Delete, Update, Select};

class Query {
	protected $_table;
	protected $_columns = array();
	protected $_values = array();
	protected $_data = array();
	protected $_where = array();
	protected $_bindVars = array();
	protected $_db = null;

	//static
	public static function Insert (String $table = '') : Insert {
		return new Insert($table);
	}

	public static function Delete (String $table = '') : Delete {
		return new Delete($table);
	}

	public static function Update (String $table = '') : Update {
		return new Update($table);
	}

	public static function Select (String $table = '') : Select {
		return new Select($table);
	}

	public static function Grid (String $table = '') : Grid {
		return new Grid($table);
	}

	//non-static
	public function __construct(String $table = '', ?Database $db = null) {
		if (!is_null($db)) $this->_db = $db;
		if (!empty($table)) $this->table($table);
	}

	public function setDb(Database $db) : void {
		$this->_db = $this->getValidDb($db);
	}

	public function getBindedVars() : mixed {
		return $this->_bindVars;
	}

	public function table(String $table) : self {
		$this->_table = $table;
		return $this;
	}

	public function data(array $data) : self {
		$this->_data = $data;
		$this->_columns = array_keys($data);
		$this->_values = array_values($data);
		return $this;
	}

	public function columns(array $columns) : self {
		$this->_columns = $columns;
		return $this;
	}

	public function values(array $values) : self {
		if (!is_array($this->_values)) {
			die('INSERT INTO ... SELECT statements cannot be combined with INSERT INTO ... VALUES');
		}
		// Get all of the passed values
		$values = func_get_args();
		$this->_values = array_merge($this->_values, $values);
		return $this;
	}

	public function buildWhere(String $where, mixed $value = null, String $operator = '=') : String {
		if ((trim($operator) == 'IN') OR (trim($operator) == 'NOT IN')) {
			if (!empty($value)) {
				if (is_array($value)) {
					$_inVar = [];
					foreach ($value as $kIn => $vIn) {
						$_var = Database::generateRandomVariable();
						$_inVar[] = $_var;
						$this->_bindVars[$_var] = $vIn;
					}
					$where .= ' ' . $operator . ' ( ' . implode(', ', $_inVar) . ' ) ';
				} else {
					$_var = Database::generateRandomVariable();
					$this->_bindVars[$_var] = $value;
					$where .= ' ' . $operator . ' ( ' . $_var . ' ) ';
				}
			} else {
				$where .= ' = "" ';
			}
		} else if ($value !== null) {
			$_var = Database::generateRandomVariable();
			$this->_bindVars[$_var] = $value;
			$where .= ' ' . $operator . ' ' . $_var;
		} else if (is_null($value) AND in_array(strtoupper(trim($operator)), ['IS', 'IS NOT'])) {
			$where .= ' ' . $operator . ' NULL';
		}
		return $where;
	}

	public function orWhere(String|array $where, mixed $value = null, ?String $operator = '=') : self {
		return $this->where($where, $value, $operator, 'OR');
	}

	public function where(String|array $where, mixed $value = null, ?String $operator = '=', String $condition = 'AND') : self {
		if (is_null($operator)) $operator = '=';
		$condition = strtoupper(trim($condition));
		if (!in_array($condition, ['AND', 'OR'])) $condition = 'AND';

		$usedWhere = [];
		if (is_string($where)) {
			$usedWhere[] = $this->buildWhere($where, $value, $operator);
		} else {
			foreach ($where as $k => $v) {
				if (is_array($v)) {
					if(count($v) == 2) $usedWhere[] = $this->buildWhere($k, $v[1], $v[0]);
					else $usedWhere[] = $this->buildWhere($v[0], $v[2], $v[1]);
				} else {
					$usedWhere[] = $this->buildWhere($k, $v, $operator);
				}
			}
		}
		if ($condition != 'AND') {
			$usedWhere = ' ( ' . Database::generateWhere($usedWhere, null, $condition) . ' ) ';
		}
		
		if (!empty($this->_where)) {
			if (is_array($usedWhere)) {
				if (is_array($this->_where)) $this->_where = array_merge($this->_where, $usedWhere);
				else $this->_where .= " {$condition} " . Database::generateWhere($usedWhere, null, $condition);
			} else {
				if (is_array($this->_where)) $this->_where = Database::generateWhere($this->_where, null, $condition) . " {$condition} " . $usedWhere;
				else $this->_where .= " {$condition} " . $usedWhere;
			}
		} else $this->_where = $usedWhere;

		return $this;
	}

	public function reset() : self {
		$this->_table = NULL;

		$this->_columns = array();
		$this->_values  = array();
		$this->_data  = array();
		$this->_where  = array();

		return $this;
	}

	public function resetWhere() : self {
		$this->_where = array();
		$this->_bindVars = array();
		return $this;
	}

	protected function combine () : self {
		if (!empty($this->_columns) AND !empty($this->_values)) $this->_data = array_combine($this->_columns, $this->_values);
		return $this;
	}

	public function execute(?Database $db = null) : mixed {
		$db = $this->getValidDb($db);
		if (is_null($db)) throw new ExceptionBase('Database connection error!');

		return $this;
	}

	protected function getValidDb(?Database $db = null) : Database {
		if (is_null($db)) $db = $this->_db;
		return $db;
	}
}

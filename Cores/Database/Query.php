<?php
namespace Lukiman\Cores\Database;

use \Lukiman\Cores\Database;
use \Lukiman\Cores\Database\Query\{Grid, Insert, Delete, Update, Select};

class Query {
	protected $_table;
	protected $_columns = array();
	protected $_values = array();
	protected $_data = array();
	protected $_where = array();
	protected $_bindVars = array();
	
	//static
	public static function Insert ($table = '') {
		return new Insert($table);
	}
	
	public static function Delete ($table = '') {
		return new Delete($table);
	}
	
	public static function Update ($table = '') {
		return new Update($table);
	}

	public static function Select ($table = '') {
		return new Select($table);
	}

	public static function Grid ($table = '') {
		return new Grid($table);
	}

	//non-static
	public function __construct($table = '') {
		if (!empty($table)) $this->table($table);
	}
	
	public function getBindedVars() {
		return $this->_bindVars;
	}
	
	public function table($table) {
		$this->_table = $table;
		return $this;
	}

	public function data(array $data) {
		$this->_data = $data;
		$this->_columns = array_keys($data);
		$this->_values = array_values($data);
		return $this;
	}
	
	public function columns(array $columns) {
		$this->_columns = $columns;
		return $this;
	}
	
	public function values(array $values) {
		if (!is_array($this->_values)) {
			die('INSERT INTO ... SELECT statements cannot be combined with INSERT INTO ... VALUES');
		}
		// Get all of the passed values
		$values = func_get_args();
		$this->_values = array_merge($this->_values, $values);
		return $this;
	}
	
	public function where($where, $value = null, $operator = '=') {
		if ((trim($operator) == 'IN') OR (trim($operator) == 'NOT IN')) {
			$_var = Database::generateRandomVariable();
			$this->_bindVars[$_var] = $value;
			$where .= ' ' . $operator . ' ' . $_var;
		} else if ($value !== null) {
			$_var = Database::generateRandomVariable();
			$this->_bindVars[$_var] = $value;
			$where .= ' ' . $operator . ' ' . $_var; //Database::getInstance('')->toQuote($value);
		}
		if (!empty($this->_where)) {
			if (is_array($where)) {
				if (is_array($this->_where)) $this->_where = array_merge($this->_where, $where);
				else $this->_where .= ' AND ' . Database::generateWhere($where);
				/*foreach($where as $kW => $vW) {
					$_var = Database::generateRandomVariable();
					$this->_bindVars[$_var] = $value;
					$this->_where .= ' ' . $operator . ' ' . $_var;
				}*/
			} else {
				if (is_array($this->_where)) $this->_where = Database::generateWhere($this->_where) . ' AND ' . $where;
				else $this->_where .= ' AND ' . $where;
			}
		} else $this->_where = $where;
		
		return $this;
	}

	public function reset() {
		$this->_table = NULL;

		$this->_columns = array();
		$this->_values  = array();
		$this->_data  = array();
		$this->_where  = array();

		return $this;
	}
	
	public function resetWhere() {
		$this->_where = array();
		$this->_bindVars = array();
		return $this;
	}
	
	protected function combine () {
		if (!empty($this->_columns) AND !empty($this->_values)) $this->_data = array_combine($this->_columns, $this->_values);
		return $this;
	}
	
	public function execute() { }
	
}

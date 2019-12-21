<?php
namespace Lukiman\Cores\Database\Query;

use \Lukiman\Cores\Database;
use \Lukiman\Cores\Database\Query as Database_Query;

class Select extends Database_Query {
	protected $_dbStatement = null;
	protected $_join = array();
	protected $_orderBy = '';
	protected $_groupBy = '';
	protected $_useHaving = '';
	protected $_useLimit = '';
	protected $_rowCount = 0;
	
	public function execute(Database $db = null) {
		parent::execute($db);
		// if (is_null($db)) $db = $this->_db;
		$db = $this->getValidDb($db);
		if (is_array($this->_orderBy)) $this->_orderBy = implode(' , ', $this->_orderBy);
		if (is_array($this->_groupBy)) $this->_groupBy = implode(' , ', $this->_groupBy);
		if (is_array($this->_useHaving)) $this->_useHaving = implode(' , ', $this->_useHaving);
		
		if (is_array($this->_join)) $this->_join = implode(' ', $this->_join);
		// Database::activate($setting);
		$this->_dbStatement = Database::Select($db, $this->_table, $this->_columns, $this->_where, $this->_bindVars, $this->_join, $this->_orderBy, $this->_groupBy, $this->_useHaving, $this->_useLimit);
		$this->_rowCount = $this->_dbStatement->rowCount();
		return $this;
	}
	
	public function count() {
		// return $this->_dbStatement->rowCount();
		return $this->_rowCount;
	}
	
	public function next() {
		if (empty($this->_dbStatement)) $this->execute();
		return $this->_dbStatement->fetch();
	}
	
	public function leftJoin ($join, $on) {
		return $this->join($join, $on, 'left');
	}
	
	public function rightJoin ($join, $on) {
		return $this->join($join, $on, 'right');
	}
	
	public function join($join, $on, $type = '') {
		$__join = ' JOIN ';
		$_on = $on;
		if (is_array($on)) $_on = implode (' AND ', $on);
		if ($type == 'left') $__join = ' LEFT ' . $__join;
		else if ($type == 'right') $__join = ' RIGHT ' . $__join;
		$this->_join[] = $__join . $join . ' ON ( ' . $on . ' ) ';
		return $this;
	}
	
	public function sort($order, $type = null) {
		return $this->order($order, $type);
	}
	
	public function order($order, $type = null) {
		$validType = array('ASC', 'DESC');
		if (!in_array(strtoupper($type), $validType)) $type = null;
		if ($type !== null) $order .= ' ' . $type;
		if (!empty($this->_orderBy)) {
			if (is_array($order)) {
				if (is_array($this->_orderBy)) $this->_orderBy = array_merge($this->_orderBy, $order);
				else $this->_orderBy .= ' , ' . implode(' , ', $order);
			} else {
				if (is_array($this->_orderBy)) $this->_orderBy = implode(' , ', $this->_orderBy) . ' , ' . $order;
				else $this->_orderBy .= ' , ' . $order;
			}
		} else $this->_orderBy = $order;
		
		return $this;
	}
	
	public function group($group) {
		if (is_array($group)) $this->_groupBy = array_merge($group);
		else $this->_groupBy[] = $group;
		return $this;
	}
	
	public function having($having) {
		if (is_array($having)) $this->_useHaving = array_merge($having);
		else $this->_useHaving[] = $having;
		return $this;
	}
	
	public function limit($limit, $limit1 = null) {
		if (!empty($limit1) AND !is_array($limit)) $limit = array($limit, $limit1);
		if (is_array($limit)) $this->_useLimit = $limit;
		else {
			$_tmp = explode(',', $limit);
			$this->_useLimit[0] = trim($_tmp[0]);
			if (!empty($_tmp[1])) $this->_useLimit[1] = trim($_tmp[1]);
		}
		return $this;
	}
	
	public function reset() {
		parent::reset();
		$this->_dbStatement = null;
		$this->_join = '';
		$this->_groupBy = '';
		$this->_orderBy = '';
		$this->_useHaving = '';
		$this->_useLimit = '';
		$this->_rowCount = 0;
		return $this;
	}
	
	public function showQuery() {
		return $this->_dbStatement->queryString;
	}
	
	public function fetchAll($type = 'default') {
		if (empty($this->_dbStatement)) $this->execute();
		$ret = array();
		while($v = $this->_dbStatement->fetch()) {
			if ($type == 'array') $v = (array) $v;
			$ret[] = $v;
		}
		return $ret;
	}
}

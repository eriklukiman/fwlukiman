<?php
namespace Lukiman\Cores\Database\Query;

use \Lukiman\Cores\Database;
use \Lukiman\Cores\Database\Query as Database_Query;

class Delete extends Database_Query {
	protected $_useLimit = '';
	
	public function execute($setting = 'default') {
		Database::activate($setting);
		return Database::Delete($this->_table, $this->_where, $this->_bindVars, $this->_useLimit);
	}
	
	public function limit($limit = 1) {
		if (is_array($limit)) return $this;
		else $this->_useLimit = $limit;
		
		return $this;
	}
	
}

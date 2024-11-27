<?php
namespace Lukiman\Cores\Database\Query;

use \Lukiman\Cores\Database;
use \Lukiman\Cores\Database\Query as Database_Query;

class Delete extends Database_Query {
	protected $_useLimit = '';

	public function execute(?Database $db = null) : int {
		$db = $this->getValidDb($db);
		return Database::Delete($db, $this->_table, $this->_where, $this->_bindVars, $this->_useLimit);
	}

	public function limit(int $limit = 1) : self {
		if (is_array($limit)) return $this;
		else $this->_useLimit = $limit;

		return $this;
	}

}

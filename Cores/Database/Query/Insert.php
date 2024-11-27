<?php
namespace Lukiman\Cores\Database\Query;

use \Lukiman\Cores\Database;
use \Lukiman\Cores\Database\Query as Database_Query;

class Insert extends Database_Query {
	public function execute(?Database $db = null) : int|bool {
		parent::execute($db);
		$db = $this->getValidDb($db);
		if (empty($this->_data)) $this->combine();
		return Database::Insert($db, $this->_table, $this->_data);
	}
}

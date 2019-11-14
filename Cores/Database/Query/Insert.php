<?php
namespace Lukiman\Cores\Database\Query;

use \Lukiman\Cores\Database;
use \Lukiman\Cores\Database\Query as Database_Query;

class Insert extends Database_Query {
	public function execute($setting = 'default') {
		Database::activate($setting);
		if (empty($this->_data)) $this->combine();
		return Database::Insert($this->_table, $this->_data);
	}
}

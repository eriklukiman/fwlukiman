<?php
namespace Lukiman\Cores\Database\Query;

use \Lukiman\Cores\Database;
use \Lukiman\Cores\Database\Query as Database_Query;

class Update extends Database_Query {
	public function execute($setting = 'default') {
		Database::activate($setting);
		if (empty($this->_data)) $this->combine();
		return Database::Update($this->_table, $this->_data, $this->_where, $this->_bindVars);
	}
}

<?php
namespace Lukiman\Cores\Interfaces\Database;

interface Transaction {
	public function inTransaction() : bool;
	
	public function beginTransaction() : bool;
	
	public function commit (int $timeout) : bool;
	
	public function rollBack (int $timeout) : bool;
}

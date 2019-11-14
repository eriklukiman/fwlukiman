<?php
namespace Lukiman\Cores\Interfaces\Database;

interface Transaction {
	public function inTransaction() : bool;
	
	public function beginTransaction() : void;
	
	public function commit (int $timeout) : void;
	
	public function rollBack (int $timeout) : void;
}

<?php
namespace Lukiman\Cores\Interfaces;

interface Cache {
	public function set(String $id, $value, ?int $ttl);
	
	public function get(String $id);
}
<?php
namespace Lukiman\Cores\Interfaces;

interface Cache {
	public function set(String $id, mixed $value, ?int $ttl) : bool;

	public function get(String $id) : mixed;

	public function delete(String $id) : bool;
}

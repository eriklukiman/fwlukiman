<?php
namespace Lukiman\Cores;

class Session {

	public static function generate(int $length = 64) : String {
		return substr(bin2hex(random_bytes(ceil($length / 2))), 0, $length);
	}
}

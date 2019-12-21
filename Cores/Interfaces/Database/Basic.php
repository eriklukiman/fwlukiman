<?php
namespace Lukiman\Cores\Interfaces\Database;

use \Lukiman\Cores\Database\Config;

interface Basic {
	// public static function activate(String $setting) : void ;
	
	public static function getInstance(?Config $config) : Object;
	
	public function toQuote($string) : String;
}

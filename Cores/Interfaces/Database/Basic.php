<?php
namespace Lukiman\Cores\Interfaces\Database;

interface Basic {
	public static function activate(String $setting) : void ;
	
	public static function getInstance(String $setting) : Object;
	
	public function toQuote($string) : String;
}

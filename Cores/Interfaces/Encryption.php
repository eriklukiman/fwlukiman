<?php
namespace Lukiman\Cores\Interfaces;

interface Encryption {
	public function encrypt(String $str) : String;
	
	public function decrypt(String $str) : String;
}
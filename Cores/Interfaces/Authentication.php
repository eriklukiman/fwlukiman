<?php
namespace Lukiman\Cores\Interfaces;

use \Lukiman\Cores\Data\Authentication as AuthData;

interface Authentication {
	public function authWithUserPassword(String $username, String $password) : bool;
	
	public function authWithToken(String $token) : bool;
	
	public function isAuthenticated() : bool;
	
	public function revokeAuthentication() : bool;
	
	public function getCredentials() : ?AuthData;
}
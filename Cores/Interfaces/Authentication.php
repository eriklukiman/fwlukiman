<?php
namespace Lukiman\Cores\Interfaces;

use \Lukiman\Cores\Data\Authentication as AuthData;

interface Authentication {
	public function authWithUserPassword(String $username, String $password) : bool;
	
	public function authWithToken(String $token) : bool;
	
	public function isAuthenticated() : bool;
	
	public function grantAuthentication(AuthData $data) : bool;

	public function revokeAuthentication() : bool;

	public function extendAuthentication(int $ttl) : bool;
	
	public function getCredentials() : ?AuthData;
}
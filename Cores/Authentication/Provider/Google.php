<?php
namespace Lukiman\Cores\Authentication\Provider;

use \Lukiman\Cores\Exception\Base as ExceptionBase;
use \Lukiman\Cores\Interfaces\Authentication as IAuthentication;
use \Lukiman\Cores\Data\Authentication as AuthData;

class Google extends Base implements IAuthentication {
	private static String $queryURL = 'https://oauth2.googleapis.com/tokeninfo?id_token=';
	private String $applicationID;
	private bool $authInProgress = false;
	
	public function __construct(?array $config = null) {
		parent::__construct($config);
		if (!empty($config) AND !empty($config['applicationID'])) {
			$this->applicationID = $config['applicationID'];
		} else {
			throw new ExceptionBase('No applicationID defined');
		}
	}
	
	public function authWithToken(String $token) : bool {
		$this->authInProgress = true;
		$usedURL = static::$queryURL . $token;
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $usedURL);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		$result = curl_exec($curl);
		if (!empty($result)) {
			$result  = json_decode($result, true);
		}
		curl_close($curl);

		$this->authInProgress = false;
		$this->setCredentials($this->convertToData($result));

		return $this->isAuthenticated();
	}
	
	protected function convertToData(?array $input) : AuthData {
		$authData = new AuthData();
		if (!empty($input) AND !empty($input['email_verified']) AND ($input['email_verified'] == 'true') AND !empty($input['aud']) AND ($input['aud'] == $this->applicationID) AND ($input['exp'] > time())) {
			// $authData->setUserName($input['sub'] . '@' . $input['iss']);
			$authData->setUserName($input['email']);
			$authData->setEmail($input['email']);
			$authData->setName($input['name']);
			$authData->setPicture($input['picture']);
			$authData->setExpired($this->calculateLeastExpiryTimestamp(intval($input['exp']), intval($input['iat'])));
			$authData->setCreated(intval($input['iat']));
			$authData->setAuthProvider($input['iss']);
		}
		return $authData;
	}
}

<?php
namespace Lukiman\Cores\Authentication\Provider;

use \Lukiman\Cores\Exception\Base as ExceptionBase;
use \Lukiman\Cores\Interfaces\Authentication as IAuthentication;
use \Lukiman\Cores\Data\Authentication as AuthData;

class Okta extends Base implements IAuthentication {
	private static String $verifyPath = '/v1/introspect';
	private String $baseURL;
	private String $clientID;
	private String $clientSecret;
	private bool $authInProgress = false;
	
	public function __construct(?array $config = null) {
		if (!empty($config) AND !empty($config['baseURL'])) {
			$this->baseURL = $config['baseURL'];
		} else {
			throw new ExceptionBase('No baseURL defined');
		}
		if (!empty($config) AND !empty($config['clientID'])) {
			$this->clientID = $config['clientID'];
		} else {
			throw new ExceptionBase('No clientID defined');
		}
		if (!empty($config) AND !empty($config['clientSecret'])) {
			$this->clientSecret = $config['clientSecret'];
		} else {
			throw new ExceptionBase('No clientSecret defined');
		}
	}
	
	public function authWithToken(String $token) : bool {
		$this->authInProgress = true;
		$usedURL = $this->baseURL . static::$verifyPath;
		$params = [
			'token'	=> $token,
			'client_id'	=> $this->clientID,
			'client_secret'	=> $this->clientSecret,
		];
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $usedURL);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
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
		if (!empty($input) AND !empty($input['active']) AND ($input['active'] == 'true') AND !empty($input['client_id']) AND ($input['client_id'] == $this->clientID) AND ($input['exp'] > time())) {
			// $authData->setUserName($input['sub'] . '@' . $input['iss']);
			$authData->setUserName($input['username']);
			$authData->setEmail($input['username']);
			$authData->setName($input['username']);
			$authData->setExpired(intval($input['exp']));
			$authData->setCreated(intval($input['iat']));
			$authData->setAuthProvider($input['iss']);
		}
		return $authData;
	}
	
	public function revokeAuthentication() : bool {
		parent::revokeAuthentication();
		
		return true;
	}
}

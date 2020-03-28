<?php
namespace Lukiman\Cores\Data;

class Authentication extends Base {
	private ?String $userName;
	private ?String $email;
	private ?String $name;
	private ?String $picture;
	private ?int $expired;
	private ?int $created;
	private ?String $authProvider;
	
	public function __construct() {
		$props = get_class_vars(get_class($this));

		foreach($props as $k => $v) {
			if (empty($this->{$k})) $this->{$k} = null;
		}
	}
	
	public function getUserName() : ?String {
		return $this->userName;
	}

	public function setUserName(String $userName) {
		$this->userName = $userName;
		return $this;
	}

	public function getEmail() : ?String {
		return $this->email;
	}

	public function setEmail(String $email) {
		$this->email = $email;
		return $this;
	}

	public function getName() : ?String {
		return $this->name;
	}

	public function setName(String $name) {
		$this->name = $name;
		return $this;
	}

	public function getPicture() : ?String {
		return $this->picture;
	}

	public function setPicture(String $picture) {
		$this->picture = $picture;
		return $this;
	}

	public function getExpired() : ?int {
		return $this->expired;
	}

	public function setExpired(int $expired) {
		$this->expired = $expired;
		return $this;
	}

	public function getCreated() : ?int {
		return $this->created;
	}

	public function setCreated(int $created) {
		$this->created = $created;
		return $this;
	}

	public function getAuthProvider() : ?String {
		return $this->authProvider;
	}

	public function setAuthProvider(String $authProvider) {
		$this->authProvider = $authProvider;
		return $this;
	}
}
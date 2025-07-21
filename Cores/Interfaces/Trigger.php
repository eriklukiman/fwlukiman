<?php
namespace Lukiman\Cores\Interfaces;

interface Trigger {
	public function get(String $url, String|array $params) : void;
	
	public function post(String $url, String|array $params) : void;

	public function put(String $url, String|array $params) : void;

	public function patch(String $url, String|array $params) : void;

	public function delete(String $url, String|array $params) : void;
	
  public function addHeaders(array $headers, bool $isOverwrite = false) : void;
}

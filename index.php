<?php
require_once('vendor/autoload.php');
require_once('includes/preLoading.php');
require_once('includes/const.php');
session_start();

use \Lukiman\Cores\Controller;
use \Lukiman\Cores\Exception\Base as ExceptionBase;

function exception_error_handler($errno, $errstr, $errfile, $errline ) {
	echo 'abc';
    throw new ExceptionBase($errstr);
}
set_error_handler("exception_error_handler", E_ALL);


$fullPath = (!empty($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : (!empty($_SERVER['REQUEST_URI']) ? strtok($_SERVER['REQUEST_URI'], '?') : ''));

if (!empty($fullPath)) {
	$startTime = microtime(true);
	$path = explode('/', $fullPath);
	if (empty($path[0])) array_shift($path);
	if (end($path) == '') array_pop($path);
	
	$_path = $path;
	foreach ($path as $k => $v) {
		$path[$k] = preg_replace_callback('/(\_[a-z])/', function ($word) {
			return strtoupper($word[1]);
		}, ucwords(strtolower($v)));
	}
	$class = implode('\\', $path);
	
	$retVal = null;
	$action = '';
	$_param = '';
	$params = array();
	while (!Controller::exists($class) AND !empty($class)) {
		if (!empty($action)) array_unshift($params, $_param);
		$action = array_pop($path);
		$_param = array_pop($_path);
		$class = implode('\\', $path);
	}
	
	try {
		if (empty($class)) {
			// if (!headers_sent()) header('HTTP/1.0 404 Not Found');
			throw new ExceptionBase('Handler not found!'); //error
		}
		Controller\Base::set_action($action);
	
		$ctrl = Controller::load($class);
		$retVal = $ctrl->execute($action, $params);
		if (!headers_sent()) {
			$headers = $ctrl->getHeaders();
			foreach($headers as $k => $v) header($k . ': ' . $v);
		}
		echo $retVal;
	} catch (ExceptionBase $e) {
		if (!headers_sent()) header('HTTP/1.0 404 Not Found');
		echo $e->getMessage();
	}
	
	echo "\nDuration: " . (microtime(true) - $startTime);
}

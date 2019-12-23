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



if (!empty($_SERVER['PATH_INFO'])) {
	$startTime = microtime(true);
	$pathOri = $_SERVER['PATH_INFO'];
	$path = explode('/', $_SERVER['PATH_INFO']);
	if (empty($path[0])) array_shift($path);
	if (end($path) == '') array_pop($path);
	
	$_path = $path;
	foreach ($path as $k => $v) $path[$k] = ucwords(strtolower($v));
	$class = implode('\\', $path);
	
	$retVal = null;
	$action = '';
	$_param = '';
	$params = array();
	while (!Controller::exists($class) AND !empty($class)) {
		// echo Controller::exists($class);
		if (!empty($action)) array_unshift($params, $_param);
		$action = array_pop($path);
		$_param = array_pop($_path);
		// echo '---';var_dump($class);
		$class = implode('\\', $path);
		// var_dump($class);echo '+++';
	}
	
	try {
		if (empty($class)) {
			// if (!headers_sent()) header('HTTP/1.0 404 Not Found');
			throw new ExceptionBase('Handler not found!'); //error
		}
		Controller\Base::set_action($action);
	// var_dump($class);
	
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
	// var_dump($retVal);
	// echo $retVal;	
	
	echo "\nDuration: " . (microtime(true) - $startTime);
}


/*function exception_error_handler($severity, $message, $file, $line) {
    echo 'bbb';
	if (!(error_reporting() & $severity)) {
        // This error code is not included in error_reporting
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
}*/
// set_error_handler("exception_error_handler");

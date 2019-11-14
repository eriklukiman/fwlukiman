<?php
require_once('../vendor/autoload.php');
require_once('../includes/preLoading.php');
require_once('../includes/const.php');
// session_start();

use \Lukiman\Cores\Controller;
use \Lukiman\Cores\Database;
use \Lukiman\Cores\Exception\Base as ExceptionBase;

$serviceStartTime = new \Swoole\Atomic();
$port = 33000;

// $http = new swoole_http_server("127.0.0.1", $port);
$http = new \Swoole\HTTP\Server("127.0.0.1", $port);


$http->on("start", function ($server) use ($port, &$serviceStartTime) {
    echo "Swoole http server is started at http://127.0.0.1:$port\n";
	$serviceStartTime->set(time());
});



/*$http->on("request1", function ($request, $response) use ($port, $serviceStartTime) {
	$responseStartTime = microtime(true);
	$path = $request->server['request_uri'];
	if ($path == '/favicon.ico') {
		$response->header("Content-Type", "text/plain");
		$response->end("OK\n");
		// echo "\nNo excec Duration: " . (microtime(true) - $responseStartTime);
	} else if ($path == '/whoami/') {
		$response->header("Content-Type", "text/plain");
		$response->end("Service run at port $port for " . (time() - $serviceStartTime->get()) . " seconds.\n");
	} else {
		// print_r($request);
		
		$pathOri = $path;
		$path = explode('/', $path);
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
			$retVal = Controller::load($class)->execute($action, $params);
			// echo $retVal;	

			$response->header("Content-Type", "application/json");
			$response->end($retVal);
			// echo "\nDuration: " . (microtime(true) - $responseStartTime);
		} catch (ExceptionBase $e) {
			// if (!headers_sent()) header('HTTP/1.0 404 Not Found');
			$response->status(404);
			echo $e->getMessage();
			$response->end($e->getMessage());
		}
	}
	echo "Duration: " . (microtime(true) - $responseStartTime) . "\n";
});*/

$http->on("request", 'requestHandler');

function requestHandler (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
	$responseStartTime = microtime(true);
	$fullPath = $request->server['request_uri'];
	if ($fullPath == '/favicon.ico') {
		$response->header("Content-Type", "text/plain");
		$response->end("OK\n");
		// echo "\nNo excec Duration: " . (microtime(true) - $responseStartTime);
	} else if ($fullPath == '/whoami/') {
		$response->header("Content-Type", "text/plain");
		$response->end(getServerStatus());
	} else {
		// print_r($request);
		
		$pathOri = $fullPath;
		$path = explode('/', $fullPath);
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
			$retVal = Controller::load($class)->execute($action, $params);
			// echo $retVal;	

			$response->header("Content-Type", "application/json");
			$response->end($retVal);
			// echo "\nDuration: " . (microtime(true) - $responseStartTime);
		} catch (ExceptionBase $e) {
			$response->status(404);
			$response->end($e->getMessage());
			echo 'Error: ' . $e->getMessage();
		}
	}
	echo "$fullPath (" . \Swoole\Coroutine::getuid() . ") :" . (microtime(true) - $responseStartTime) . ' ' . Database::getstats() . "\n";
};

$http->start();

function getServerStatus() {
	return "Service run at port {$GLOBALS['port']} for " . (time() - $GLOBALS['serviceStartTime']->get()) . " seconds.\n";
}

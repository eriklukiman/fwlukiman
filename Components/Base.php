<?php
// namespace Lukiman\Components;

require_once('../vendor/autoload.php');
require_once('../includes/preLoading.php');
require_once('../includes/const.php');
// session_start();

use \Lukiman\Cores\Controller;
use \Lukiman\Cores\Database;
use \Lukiman\Cores\Database\Config;
use \Lukiman\Cores\Request;
use \Lukiman\Cores\Exception\Base as ExceptionBase;
use \Nyholm\Psr7\Factory\Psr17Factory;
use \Psr\Http\Message\ServerRequestInterface;
use \Lukiman\Cores\Loader;

/**
* Running Configuration
* Port:33000
**/
class Base {
	protected static int $port = 80;
	protected string $logDest = 'console';
	protected \Swoole\Http\Server $http;
	protected $serverRequestFactory;
	
	public function __construct() {
		$this->http = new \swoole_http_server("127.0.0.1", static::$port);
		
		$psr17Factory = new Psr17Factory();
		$this->serverRequestFactory = new \Ilex\SwoolePsr7\SwooleServerRequestConverter(
			$psr17Factory,
			$psr17Factory,
			$psr17Factory,
			$psr17Factory
		);

		//database setting variables
		$dbMaxConnection = 1;
		$dbVariables = array(
			'createdConnection'	=> new \Swoole\Atomic(0),
			'instances'			=> new \Swoole\Coroutine\Channel($dbMaxConnection),
			// 'instances'			=> new \SplQueue(),
		);
		Database::setParameters($dbVariables['instances'], $dbMaxConnection, $dbVariables['createdConnection']);
		$dbConfig = new Config(Loader::Config('Swoole_Database'));
		Database::setConfig($dbConfig);
		// Database::populateConnectionPool($dbConfig);
		
		//Exception variables
		ExceptionBase::setCountVarContainer(new \Swoole\Atomic(0));

	}
	
	protected function logs($message, $dest = null) : void {
		if (empty($dest)) $dest = $this->logDest;
		if ($dest == 'console') {
			echo $message;
		}
	}
	
	public function onStartHandler($server) {
			$this->logs("Swoole [ver." . SWOOLE_VERSION . "] http server is started on http://127.0.0.1:" . static::$port . " at " .  date('Y-m-d H:i:sP') . "\n");
		}
	
	public function onRequestHandler(\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
		$responseStartTime = microtime(true);
		$fullPath = $request->server['request_uri'];
		if ($fullPath == '/favicon.ico') {
			$response->header("Content-Type", "text/plain");
			$response->end("OK\n");
		} else if ($fullPath == '/whoami/' OR $fullPath == '/whoami') {
			$response->header("Content-Type", "text/plain");
			$response->end($this->getServerStatus());
			$this->logs($this->getStats($fullPath, $responseStartTime));
		} else {
			$psr7Request = $this->serverRequestFactory->createFromSwoole($request);
			
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
				if (!empty($action)) array_unshift($params, $_param);
				$action = array_pop($path);
				$_param = array_pop($_path);
				$class = implode('\\', $path);
			}
			
			try {
				if (empty($class)) {
					throw new ExceptionBase('Handler not found!'); //error
				}
				Controller\Base::set_action($action);
				$convertedReq = new Request($psr7Request);
				$ctrl = Controller::load($class);
				$retVal = $ctrl->execute($action, $params, $convertedReq);

				$resHeaders = $ctrl->getHeaders();
				foreach($resHeaders as $k => $v) $response->header($k, $v);
				$response->end($retVal);
			} catch (ExceptionBase $e) {
				$response->status(404);
				$response->end($e->getMessage());
				$this->logs('Error: ' . $e->getMessage());
			}
			$this->logs($this->getStats($fullPath, $responseStartTime));
		}
	}

	public function run() {
		$this->http->on("start", [$this, 'onStartHandler']);
		$this->http->on("request", [$this, 'onRequestHandler']);
		$this->http->start();
	}
	
	protected function getStats(string $fullPath, float $responseStartTime) {
		return "$fullPath (" . \Swoole\Coroutine::getuid() . ") : " . (microtime(true) - $responseStartTime) . ' seconds ' . Database::getstats() . "\n";
	}

	protected function getServerStatus() {
		$stats =  $this->http->stats();
		return "Service run at port " . static::$port . " for " . $this->formatUpTime(time() - $stats['start_time']) . "\n" . ExceptionBase::getStats() . "\n" . print_r($stats, true);
	}

	protected function formatUpTime(int $time) : string {
		 $formatedTime = date('z-G-i:s', $time);
		 $arrTime = explode('-', $formatedTime);
		 $arrTime[1] -= date('G', 0); // adjustment for timezone differences
		 $retVal = "{$arrTime[1]}:{$arrTime[2]}";
		 if (!empty($arrTime[0])) $retVal = $arrTime[0] . ' day(s) ' . $retVal;
		 return $retVal;
	}
	
	public static function setPort(int $port) : void {
		static::$port = $port;
	}
	
	public static function parsePort(string $comment) : int {
		preg_match('/port\:(\d+)/i', $comment, $result);
		if (!empty($result[1])) return $result[1];
		else return static::$port;
	}
}

$className = basename($argv[0], '.php');
$runMethod = !empty($argv[1]) ? $argv[1] : 'run';
$refl = new \ReflectionClass($className);
$portDoc = $refl->getDocComment();
$className::setPort($className::parsePort($portDoc));
// $obj = new "\\Lukiman\\Components\\" . $className();
$obj = new $className();
$obj->$runMethod();

// $http = new \Swoole\HTTP\Server("127.0.0.1", $port, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
// $ssl_dir = '/home/erik/ssl/ca/intermediate/';
/*$ssl_dir = '/etc/ssl';
$http->set([
    'ssl_cert_file' => $ssl_dir . '/certs/ssl-cert-snakeoil.pem',
    'ssl_key_file' => $ssl_dir . '/ssl-cert-snakeoil.key',
    'open_http2_protocol' => true,
]);*/

// $http->on("WorkerStop", function ($server, $workerId) {
	// Process::close($workerId);
// } );


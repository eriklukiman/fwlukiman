<?php
namespace Lukiman\Cores\Controller;

use \Lukiman\Cores\Request;
use \Lukiman\Cores\Exception\Base as ExceptionBase;

class Base {
	protected static String $_path = 'Modules/';
	protected static String $_prefixClass = 'Modules\\';
	protected mixed $result = '';
	protected ?array $headers = [];

    protected $request ;

    protected static $action ;

    public function __construct () {
    }

    private static function Include_File(String $name, String $pathPrefix = '') : void {
		$f = self::getPath($pathPrefix) . $name . '.php';
		$f = str_replace('\\', '/', $f);
		if (!is_readable($f)) $f = str_replace('_', '/', $f);
		if (is_readable($f)) include_once($f);
	}

	public static function load(String $name, String $prefix = '') : self|String {
		if (empty($name)) return new self();

		$class = self::getPrefix($prefix) . $name;
		self::Include_File($name, $prefix);
		$class = '\\' . LUKIMAN_NAMESPACE_PREFIX . '\\' . $class;

		return new $class;
	}

	public static function exists (String $name, String $prefix = '') : bool {
		self::Include_File($name, $prefix);
		return class_exists('\\' . LUKIMAN_NAMESPACE_PREFIX . '\\' . self::getPrefix($prefix) . $name);
	}

	public static function getPrefix(String $usedPrefix = '') : String {
		if (!empty($usedPrefix)) return $usedPrefix . (substr($usedPrefix,-1) == '\\' ? '' : '\\');
		else return self::$_prefixClass;
	}

	public static function getPath(String $usedPathPrefix = '') : String {
		if (!empty($usedPathPrefix)) return '';
		else return self::$_path;
	}

	public function execute (String $action = 'Index', ?array $params = null, ?Request $request = null) : mixed {
		if (empty($action) OR ($action == 'Publics')) $action = 'Index';
		$doAction = 'do_' . $action;

		if (!method_exists($this, $doAction)) {
			if ($action != 'Index') array_unshift($params, $action);
			$doAction = 'do_Index';
		}

        $this->catchRequestParams($params, $request);

		if (strcasecmp($this->request->getmethod(), 'options') == 0) {
			return static::optionsHandler($params);
		}

		$this->beforeExecute();
		$retVal = null;

		if(method_exists($this, $doAction)) $retVal = $this->{$doAction}($params) ;
		else {
			// if (!headers_sent()) header('HTTP/1.0 404 Not Found');
			// exit('Method "' . $action . '" not defined!'); //return error
			throw new ExceptionBase('Method "' . $action . '" not defined!'); //return error
		}
		if (!empty($retVal) OR is_array($retVal)) $this->appendResult($retVal);

		$this->afterExecute();
		return $this->getResult();
	}

	protected function appendResult (mixed $res, bool $truncate = false) : void {
		if ($truncate) $this->result = '';
		if (empty($this->result)) $this->result = $res;
		else if (is_array($this->result)) {
			if (is_array($res)) $this->result = array_merge($this->result, $res);
			else $this->result[] = $res;
		} else {
			if (is_array($res)) $this->result = array_merge(array($this->result), $res);
			else $this->result .= $res;
		}
	}

	protected function getResult () : mixed {
		return $this->result;
	}

	public function getHeaders() : ?array {
		return $this->headers;
	}

	public function sendHeaders() : void {
		if (!headers_sent()) {
			$headers = $this->getHeaders();
			foreach($headers as $k => $v) header($k . ': ' . $v);
		}
	}

	protected function setHeaders(?array $headers) : void {
		$this->headers = $headers;
	}

	protected function addHeaders(?array $headers) : void {
		$this->headers = array_merge($this->headers, $headers);
	}

	protected function beforeExecute () : void {}

	protected function afterExecute () : void {}

	protected function getRequest () : mixed {
		return $this->request;
	}

	protected function catchRequestParams(mixed $params = null, mixed $request = null) : void {
		if (!is_null($request)) {
			if (get_class($request) == Request::class) $this->request = $request;
			else $this->request = new Request($request);
		} else $this->request = new Request();
	}

    public static function set_action(String $action = '') : void {
        self::$action      = $action;
    }

	public function do_AvailableFunctions() : array {
		$all = get_class_methods($this);
		$result = array();
		foreach ($all as $v) {
			if ($v == __FUNCTION__) continue;
			if (substr($v, 0, 3) == 'do_') $result[] = substr($v, 3);
		}
		return ['actions' => $result];
	}

	protected function parseValue (mixed $template, mixed $value) : mixed {
        foreach($value as $k => $v) {
			$template = str_replace('{' . $k . '}', $v, $template);
		}

        return $template;
    }

    protected function optionsHandler(?array $param) : void {
    	$this->addHeaders(['Access-Control-Allow-Methods' => 'POST, GET, OPTIONS, DELETE, PUT, PATCH']);
    }

}

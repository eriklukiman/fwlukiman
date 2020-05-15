<?php
namespace Lukiman\Cores\Controller;

use \Lukiman\Cores\Request;
use \Lukiman\Cores\Exception\Base as ExceptionBase;

class Base {
	protected static $_path = 'Modules/';
	protected static $_prefixClass = 'Modules\\';
	protected static $_nameSpacePrefix = 'Lukiman';
	protected $result = '';
	protected $headers = [];
    
    protected $request ;
    
    protected static $action ;
    
    public function __construct () {
    }
    
    private static function Include_File($name, $pathPrefix = '') {
		$f = self::getPath($pathPrefix) . $name . '.php';
		$f = str_replace('\\', '/', $f);
		if (!is_readable($f)) $f = str_replace('_', '/', $f);
		if (is_readable($f)) include_once($f);
	}
	
	public static function load($name, $prefix = '') {
		if (empty($name)) return new self();

		$class = self::getPrefix($prefix) . $name;
		self::Include_File($name, $prefix);
		$class = '\\' . self::getNameSpacePrefix() . '\\' . $class; 

		return new $class;
	}
	
	public static function exists ($name, $prefix = '') {
		self::Include_File($name, $prefix);
		return class_exists('\\' . self::getNameSpacePrefix() . '\\' . self::getPrefix($prefix) . $name);
	}
	
	public static function setNameSpacePrefix($name) {
		self::$_nameSpacePrefix = $name;
	}
	
	public static function getNameSpacePrefix() {
		return self::$_nameSpacePrefix;
	}
	
	public static function getPrefix($usedPrefix = '') {
		if (!empty($usedPrefix)) return $usedPrefix . (substr($usedPrefix,-1) == '\\' ? '' : '\\');
		else return self::$_prefixClass;
	}
	
	public static function getPath($usedPathPrefix = '') {
		if (!empty($usedPathPrefix)) return '';
		else return self::$_path;
	}
	
	public function execute ($action = 'Index', array $params = null, Request $request = null) {
		if (empty($action) OR ($action == 'Publics')) $action = 'Index';
		$doAction = 'do_' . $action;
		
		if (!method_exists($this, $doAction)) {
			if ($action != 'Index') array_unshift($params, $action);
			$doAction = 'do_Index';
		}
		// print_r($request);
		// print_r($params);
        $this->catchRequestParams($params, $request);
        
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
	
	protected function appendResult ($res, $truncate = false) {
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
	
	protected function getResult () {
		return $this->result;
	}
	
	public function getHeaders() {
		return $this->headers;
	}
	
	protected function setHeaders(array $headers) {
		$this->headers = $headers;
	}
	
	protected function addHeaders(array $headers) {
		$this->headers = array_merge($this->headers, $headers);
	}
	
	protected function beforeExecute () {}
	
	protected function afterExecute () {}
	
	protected function getRequest () {
		return $this->request;
	}
	
	protected function catchRequestParams($params = null, $request = null) {
		if (!is_null($request)) {
			if (get_class($request) == Request::class) $this->request = $request;
			else $this->request = new Request($request);
		} else $this->request = new Request();
	}
    
    public static function set_action($action = '') {
        self::$action      = $action;
    }
    
	public function do_AvailableFunctions() {
		$all = get_class_methods($this);
		$result = array();
		foreach ($all as $v) {
			if ($v == __FUNCTION__) continue;
			if (substr($v, 0, 3) == 'do_') $result[] = substr($v, 3);
		}
		return $result;
	}

	protected function parseValue ($template, $value) {
        foreach($value as $k => $v) {
			$template = str_replace('{' . $k . '}', $v, $template);
		}

        return $template;
    }

}

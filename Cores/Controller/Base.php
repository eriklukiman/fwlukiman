<?php
namespace Lukiman\Cores\Controller;

use \Lukiman\Cores\Request;
use \Lukiman\Cores\Exception\Base as ExceptionBase;

class Base {
	protected static $_path = 'Modules/';
	protected static $_prefixClass = 'Modules\\';
	protected $result = '';
    
    /*** @var Request */
    protected $request ;
    
    public static $_action ;
    
    protected $action ;
    
    public function __construct () {
//        $this->catchRequestParams();
        $this->action       = self::$_action;
    }
    
    private static function Include_File($name) {
		$f = self::getPath() . $name . '.php';
		if (!is_readable($f)) $f = str_replace('_', '/', $f);
		if (is_readable($f)) include_once($f);
	}
	
	public static function load($name) {
		if (empty($name)) return new self();
		// var_dump($name);
		$class = self::$_prefixClass . $name;
		self::Include_File($name);
		$class = '\\Lukiman\\' . $class; 
		// if (!self::exists($name)) exit('Error class ' . $name . ' not found');//return false; //error
		// echo ':'.$class.':';
		return new $class;
	}
	
	public static function exists ($name) {
		self::Include_File($name);
		// echo '||' . self::$_prefixClass . $name;
		return class_exists('\\Lukiman\\' . self::$_prefixClass . $name);
	}
	
	public static function getPrefix() {
		return self::$_prefixClass;
	}
	
	public static function getPath() {
		return self::$_path;
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
        self::$_action      = $action;
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

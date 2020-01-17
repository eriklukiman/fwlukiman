<?php
namespace Lukiman\Cores\Exception;

class Base extends \Exception {
	protected static $errorCount = 0;
	
	public function __construct($message, $code = null, $severity = null, $filename = null, $lineno = null) {
		parent::__construct($message, $code);
		static::logException();
	}
    /*protected $severity;
   
    public function __construct($message, $code = null, $severity = null, $filename = null, $lineno = null) {
        
		$this->message = $message;
        $this->code = $code;
        $this->severity = $severity;
        $this->file = $filename;
        $this->line = $lineno;
    }
   
    public function getSeverity() {
        return $this->severity;
    }
	
	public function getMessage1() {
		return parent::getMessage();
	}*/
	
	protected static function logException() {
		if (gettype(static::$errorCount) == "object") {
			static::$errorCount->add(1);
		} else {
			static::$errorCount++;
		}
	}
	
	public static function getStats() {
		return "Total exception(s): " . static::getExceptionCount() . ".";
	}
	
	public static function setCountVarContainer($count) {
		static::$errorCount =  $count;
	}
	
	protected static function getExceptionCount() {
		if (gettype(static::$errorCount) == "object") {
			return static::$errorCount->get();
		} else {
			return static::$errorCount;
		}
	}
}

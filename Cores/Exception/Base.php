<?php
namespace Lukiman\Cores\Exception;

class Base extends \Exception {
	protected static $errorCount = 0;
	
	public function __construct($message, $code = 0, $severity = null, $filename = null, $lineno = null) {
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
	
	protected static function logException() : void {
		if (is_int(static::$errorCount)) {
			static::$errorCount++;
		} else {
			static::$errorCount->add(1);
		}
	}
	
	public static function getStats() : string {
		return "Total exception(s): " . static::getExceptionCount() . ".";
	}
	
	public static function setCountVarContainer($count) : void {
		static::$errorCount =  $count;
	}
	
	protected static function getExceptionCount() : int {
		if (is_int(static::$errorCount)) {
			return static::$errorCount;
		} else {
			return static::$errorCount->get();
		}
	}
}

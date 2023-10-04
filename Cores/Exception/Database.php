<?php
namespace Lukiman\Cores\Exception;

class Database extends Base {
	protected static $errorCount = 0;
	
	public function __construct($message, $code = null, $severity = null, $filename = null, $lineno = null) {
		parent::__construct($message, $code);
		// echo static::$errorCount++;
		// echo 'cccccccccccc';
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
		self::$errorCount++;
		// echo 'gggg';var_dump(static::$errorCount);
		echo static::getStats();
	}
	
	public static function getStats() {
		// var_dump(static);
		var_dump(static::$errorCount);
		return "Total exception(s): " . static::$errorCount . ".";
	}
}

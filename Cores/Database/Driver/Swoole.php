<?php
namespace Lukiman\Cores\Database\Driver;

use \Lukiman\Cores\Interfaces\Database\{Basic, Transaction};
use \Lukiman\Cores\Loader;
use \Lukiman\Cores\Exception\Base;
use \Swoole\Coroutine\MySQL;
use \Swoole\Coroutine\Channel;
use \Swlib\SwPDO;

class Swoole /*extends \Swoole\Coroutine\MySQL*/  implements Basic, Transaction {
	protected static $_instance = array();
	protected static $_free = array();
	protected static $_lastSetting = 'default';
	protected static $_databaseSetting = null;
	protected $_inTransaction = false;
	
	protected static $_maxConnection = 2;
	protected static $_createdConnection = null;
	// protected static $_connection = null;
	
	protected $db = null;
	public $bindMap = [];
	
	public function __construct($dbType = 'mysql', $host = 'localhost', $user = '', $password = '', $dbName = '', $port = '', $options = array()) {
		if (empty($port) AND ($dbType == 'mysql')) $port = 3306;
		$dsn = $dbType . ':host=' . $host . ';dbname=' . $dbName;
		if (!empty($port)) $dsn .= ';port=' . $port;
		if (!isset($options[PDO::ATTR_DEFAULT_FETCH_MODE])) $options[PDO::ATTR_DEFAULT_FETCH_MODE] = PDO::FETCH_OBJ;
		try {
			/*$connection = array(
				'host' 		=> $host,
				'user' 		=> $user,
				'password' 	=> $password,
				'database' 	=> $dbName,
			);*/
			// parent::__construct($dsn, $user, $password , $options);
			// print_r($options);
			/*$options = [
				'mysql:host=127.0.0.1;dbname=event;charset=UTF8',
				'rx',
				'a'
				];*/
			// $db = SwPDO::construct(...$options);
			$this->db = SwPDO::construct(...array($dsn, $user, $password, $options));
			echo "\n++++";
			print_r($this->db->client->errno);echo "=====\n";
			if (!empty($this->db->client->errno)) {
				echo "**********************\n";
				var_dump($this);
				throw new Exception\Base("DB connection error");
			}
			// $db->connect($connection);
			// print_r($this->db);
			
			// print_r($connection);
			
		} catch (\Exception $e) {
			// var_dump($e);
			if ($e instanceof \Exception) 
				// exit('Database Setting is invalid : ' . $e->getMessage());
			//throw new Database_Error($e);
			die(__CLASS__ . ' : ' . $e->getMessage());
		}
		// self::$_instance[self::$_lastSetting] = $this;
		return $this;
	}
	
	public static function activate($setting = 'default') : void {
		if (!empty($setting)) self::$_lastSetting = $setting;
	}
	
	public static function getInstance($setting = 'default') : Object { 
		if (empty($setting)) $setting = self::$_lastSetting;
		
		if (is_null(self::$_createdConnection)) {
			self::$_createdConnection = new \Swoole\Atomic(0);//0;
		}
		// print_r(debug_print_backtrace(null, 2));
		// echo "\n" . \Swoole\Coroutine::getuid() . "\n";
		// if (is_null(self::$_connection)) self::$_connection = new \Swoole\Coroutine\Channel(self::$_maxConnection);
		
		$returnConn = null;
		if(empty(self::$_instance[$setting])) {
			self::$_instance[$setting] = new \Swoole\Coroutine\Channel(self::$_maxConnection);
			// self::$_instance[$setting] = array();
			// self::$_instance[$setting] = new \SplQueue();
		}
		$coId = \Swoole\Coroutine::getuid();
		// echo "getInstance request $coId\n";
		// print_r(debug_print_backtrace(null, 4));
		$coList = array();
		// if (!self::$_instance[$setting]->isEmpty()) $coList = self::$_instance[$setting]->pop();
		// if (!self::$_instance[$setting]->isEmpty()) $coList = self::$_instance[$setting]->pop();
		// if (!self::$_instance[$setting]->isEmpty()) $coList = self::$_instance[$setting]->pop();
		// if(empty(self::$_instance[$setting]) OR empty()) {
		// if(empty(self::$_instance[$setting][$coId])) {
		if(self::$_instance[$setting]->isEmpty() AND self::$_createdConnection->get() <= self::$_maxConnection) {
			// self::$_instance[$setting] = new \Swoole\Coroutine\Channel(1);
			// print_r(debug_print_backtrace(null, 3));
			if (empty(self::$_databaseSetting)) {
				self::$_databaseSetting = Loader::Config('Database');
			}
			if (!array_key_exists($setting, self::$_databaseSetting)) $setting = 'default';
			$usedSetting = self::$_databaseSetting[$setting];
			self::$_lastSetting = $setting;
			$returnConn = new self($usedSetting['driver'], $usedSetting['connection']['host'], $usedSetting['connection']['user'], $usedSetting['connection']['password'], $usedSetting['connection']['database'], $usedSetting['connection']['port'], $usedSetting['options']); 
			// print_r($returnConn);
			echo "\n___ " . self::$_createdConnection->get() .  " ___\n";
			if (empty($returnConn)) {
				$returnConn = self::$_instance[$setting]->pop();
				throw new Exception\Base("Failed to initialize DB connection");
			}
			// $coList[$coId] = $returnConn;
			// self::$_instance[$setting][$coId] = $returnConn;
			// echo self::$_createdConnection . " $coId new Conn\n";
			self::$_createdConnection->add();
			
			// echo "=$coId=\n";
		} else {
			// $returnConn = self::$_instance[$setting]->pop();
			// $returnConn = $coList[$coId];
			// $returnConn = self::$_instance[$setting][$coId];
			$returnConn = self::$_instance[$setting]->pop();
			// echo " popped \n";
			// print_r(debug_print_backtrace(null, 4));
		}
		// $returnConn->releaseConnection();
		// self::$_instance[$setting]->push($coList);
		return $returnConn; 
	} 
	
	public function releaseConnection($setting = 'default') {
		self::$_instance[$setting]->push($this);
		// print_r(self::$_instance[$setting]->count());
		// print_r(self::$_instance[$setting]->length());
		// echo " queued\n";
	}
	
	public function toQuote($value) : String {
		// return (parent::escape($string));
		// return $string;
		$pS = $this->db->prepare(':var1');
		$pS->bindValue(':var1', $value);
		// if (is_string($value)) $value = '"' . $value . '"';
		return $value;
	}
  
	public function inTransaction () : bool {
		return $this->_inTransaction;
	}
	
	public function beginTransaction  () : void {
		try {
            parent::beginTransaction ();
        } catch (Exception $e) {}
		$this->_inTransaction = true;
	}
	
	public function commit ($timeout = null) : void {
		parent::commit();
		$this->_inTransaction = false;
	}
	
	public function rollBack ($timeout = null) : void {
		parent::rollBack();
		$this->_inTransaction = false;
	}
	
	public function bindValue($parameter, $variable, $type = PDO::PARAM_STR) {
        return $this->db->bindValue($parameter, $variable, $type);
	}
	/*public function __call($name, $arguments) {
		// Note: value of $name is case sensitive.
		echo "Calling object method '$name' "	
		 . implode(', ', $arguments). "\n";
	}*/
	
    /*public function bindParam($parameter, &$variable) {
        if (!is_string($parameter) && !is_int($parameter)) {
            return false;
        }
        $parameter = ltrim($parameter, ':');
        $this->bindMap[$parameter] = &$variable;

        return true;
    }*/
	
	/*private function afterExecute() {
        $this->cursor = -1;
        $this->bindMap = [];
    }

    public function execute(array $input_parameters = [], ?float $timeout = null) {
        if (!empty($input_parameters)) {
            foreach ($input_parameters as $key => $value) {
                $this->bindParam($key, $value);
            }
        }
        $input_parameters = [];
        if (!empty($this->statement->bindKeyMap)) {
            foreach ($this->statement->bindKeyMap as $name_key => $num_key) {
                $input_parameters[$num_key] = $this->bindMap[$name_key];
            }
        } else {
            $input_parameters = $this->bindMap;
        }
        $r = $this->statement->execute($input_parameters, $timeout ?? $this->timeout);
        $this->result_set = ($ok = $r !== false) ? $r : [];
        $this->afterExecute();

        return $ok;
    }*/ 
	
	public function prepare(String $statement, array $driver_options = []) {
		return $this->db->prepare($statement, $driver_options);
	}
	
	public function query(string $statement, float $timeout = 1.000) {
		return $this->db->query($statement, $timeout);
		$this->releaseConnection();
	}
	
	/*public static function closeInstance($setting = 'default') {
		$coId = \Swoole\Coroutine::getuid();
		$coList = self::$_instance[$setting]->pop();
		if (!empty($coList[$coId])) {
			$conn = $coList[$coId];
			print_r($conn);
			unset($coList[$coId]);
			$conn->client->close();
		}
		self::$_instance[$setting]->push($coList);
		// return $this->client->close();
	}*/
	
	public static function getStats($setting = 'default') {
		echo 'Stack size: ';
		print_r(self::$_instance[$setting]->length()); echo "\n";
	}
}

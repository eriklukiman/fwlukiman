<?php
namespace Lukiman\Cores\Database\Driver;

use \Lukiman\Cores\Interfaces\Database\{Basic, Transaction};
use \Lukiman\Cores\Database\Config;
use \Lukiman\Cores\Loader;
use \Lukiman\Cores\Exception\Base as ExceptionBase;
use \Swoole\Coroutine\MySQL;
use \Swoole\Coroutine\Channel;
use \Swlib\SwPDO;

class Swoole /*extends \Swoole\Coroutine\MySQL*/  implements Basic, Transaction {
	protected static $_instance = null;
	protected static $_free = array();
	protected static $_databaseSetting = null;
	protected $_inTransaction = false;
	
	protected static $_maxConnection = 20;
	protected static $_createdConnection = null;
	protected static $_popTimeout = 3;
	
	protected $db = null;
	public $bindMap = [];
	
	// protected $isUsed = false;
	
	public function __construct($dbType = 'mysql', $host = 'localhost', $user = '', $password = '', $dbName = '', $port = '', $options = array()) {
		if (empty($port) AND ($dbType == 'mysql')) $port = 3306;
		$dsn = $dbType . ':host=' . $host . ';dbname=' . $dbName;
		if (!empty($port)) $dsn .= ';port=' . $port;
		if (!isset($options[PDO::ATTR_DEFAULT_FETCH_MODE])) $options[PDO::ATTR_DEFAULT_FETCH_MODE] = PDO::FETCH_OBJ;
		try {
			$this->db = SwPDO::construct(...array($dsn, $user, $password, $options));
			if (!empty($this->db->client->errno)) {
				throw new ExceptionBase("DB connection error");
			}
		} catch (\Exception $e) {
			if ($e instanceof \Exception) 
				throw new ExceptionBase("Failed to initialize DB connection");
			die(__CLASS__ . ' : ' . $e->getMessage());

		}
		return $this;
	}
	
	public static function getInstance(?Config $config = null) : Object { 
		// if (is_null(self::$_createdConnection)) {
			// self::$_createdConnection = new \Swoole\Atomic(0);//0;
		// }
		$returnConn = null;
		// if(empty(self::$_instance[$setting])) {
			// self::$_instance[$setting] = new \Swoole\Coroutine\Channel(self::$_maxConnection);
			// self::$_instance[$setting] = array();
			// self::$_instance[$setting] = new \SplQueue();
		// }
		// $coList = array();
		// if (!static::$_createdConnection->get()) static::populateConnectionPool($config);
		echo static::$_instance->isEmpty() .'!'. static::$_createdConnection->get() .'<'. static::$_maxConnection . "\n";
		if(static::$_instance->isEmpty() AND (static::$_createdConnection->get() < static::$_maxConnection)) {
			$usedSetting = $config;
			if (is_null($usedSetting)) {
				if (empty(static::$_databaseSetting)) {
					static::$_databaseSetting = new Config(Loader::Config('Swoole_Database'));
				}
				$usedSetting = static::$_databaseSetting;
			}
			$returnConn = new static($usedSetting->engine, $usedSetting->host, $usedSetting->user, $usedSetting->password, $usedSetting->database, $usedSetting->port, $usedSetting->options); 
			// echo "\n___ " . static::$_createdConnection->get() .  " ___\n";
			if (empty($returnConn)) {
				// $returnConn = self::$_instance->pop();
				throw new ExceptionBase("Failed to initialize DB connection.");
			}
			// self::$_instance[$setting][$coId] = $returnConn;
			static::$_createdConnection->add();
			
		} else {
			// $returnConn = self::$_instance[$setting]->pop();
			// do {
			echo " pop req \n";
			$returnConn = static::$_instance->pop(static::$_popTimeout);
			// } while ($returnConn->isUsed);
			echo " popped \n";
			if (false === $returnConn) {
                throw new ExceptionBase("Failed to pop DB connection.");
            }
		}
		// $returnConn->releaseConnection();
		// $returnConn->isUsed = true;
		Defer (function() use ($returnConn) {// release
			static::$_instance->push($returnConn);
			echo " queued " . self::$_instance->length() . "\n";
		});
		return $returnConn; 
	} 
	
	public static function populateConnectionPool(?Config $config = null) {
		if(!static::$_createdConnection->get()) {
			static::$_createdConnection->add();
			$created = 0;
			$usedSetting = $config;
			if (is_null($usedSetting) AND empty(static::$_databaseSetting)) {
				static::$_databaseSetting = new Config(Loader::Config('Swoole_Database'));
			}
			if (is_null($usedSetting)) $usedSetting = static::$_databaseSetting;
			while($created < static::$_maxConnection) {
				go(function () use ($usedSetting) {
					$newConn = new static($usedSetting->engine, $usedSetting->host, $usedSetting->user, $usedSetting->password, $usedSetting->database, $usedSetting->port, $usedSetting->options); 
					// echo "\n___ " . static::$_createdConnection->get() .  " ___\n";
					if (empty($newConn)) {
						// $returnConn = self::$_instance->pop();
						throw new ExceptionBase("Failed to initialize DB connection.");
					}
					static::$_instance->push($newConn, static::$_popTimeout);
				});
				// self::$_instance[$setting][$coId] = $returnConn;
				// static::$_createdConnection->add();
				$created++;
			}
		}
		echo "Populated " . static::$_instance->length() . " conn";
	}
	
	public function releaseConnection(/*$setting = 'default'*/) {
		// print_r($this);
		// $this->isUsed = false;
		// static::$_instance->push($this, static::$_popTimeout);
		// print_r(self::$_instance->count());
		// print_r(self::$_instance->length());
		echo " released\n";
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
            $this->db->beginTransaction();
        } catch (\Exception $e) {}
		$this->_inTransaction = true;
	}
	
	public function commit ($timeout = null) : void {
		$this->db->commit();
		$this->_inTransaction = false;
	}
	
	public function rollBack ($timeout = null) : void {
		$this->db->rollBack();
		$this->_inTransaction = false;
	}
	
	public function lastInsertId ($timeout = null) : int {
		return $this->db->lastInsertId();
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
	
	public static function getStats(/*$setting = 'default'*/) {
		echo "\nStack size: ";
		print_r(static::$_instance->length()); echo "\n";
		// var_dump(static::$_instance);
	}
	
	public static function setConfig(Config $config) {
		static::$_databaseSetting = $config;
	}
	
	public static function setParameters($instance, $maxConn, $createdConn) {
		static::$_instance = $instance;
		static::$_maxConnection = $maxConn;
		static::$_createdConnection = $createdConn;
	}
}

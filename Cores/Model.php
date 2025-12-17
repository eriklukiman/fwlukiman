<?php
namespace Lukiman\Cores;

use \Lukiman\Cores\Database;
use \Lukiman\Cores\Database\Query as Database_Query;
use \Lukiman\Cores\Exception\Base as ExceptionBase;

class Model {
	protected static $_path = 'Models/';
	protected static $_prefixClass = '\\' . LUKIMAN_NAMESPACE_PREFIX . '\\Models\\';
	protected String $table;
	protected String $prefix;
	protected String $primaryKey;
	protected ?Database $db;
	protected array $fields = [];

	public function __construct() {
		$this->db = $this->getDb();
	}

	public function getTable() : String {
		return $this->table;
	}

	public function getDb() : Database {
		if (!isset($this->db) OR is_null($this->db) OR !$this->db->ping()) $this->db = Database::getInstance();
		return $this->db;
	}

	public function reconnectDb() : void {
        if (!is_null($this->db)) $this->db->close();
        $this->db = null;
        $this->db = Database::getInstance();
    }

	public static function getPath() : String {
		return self::$_path;
	}

	public static function getPrefixClass() : String {
		return self::$_prefixClass;
	}

	public function getPrefix() : String {
		return $this->prefix;
	}

	public function getPrimaryKey() : String {
		if (empty($this->primaryKey)) $this->primaryKey = $this->prefix . 'Id';
		return $this->primaryKey;
	}

	public static function load(String $name) : Object {
		// Add the model prefix
		$class = self::$_prefixClass . $name;

		$f = self::getPath() . $name . '.php';
		$f = str_replace('\\', '/', $f);
		if (!is_readable($f)) $f = str_replace('_', '/', $f);
		if (is_readable($f)) include_once($f);

		if (class_exists($class)) {
			return new $class;
		} else {
			throw new ExceptionBase("Model '$class' not found!");
		}
	}

	public function getFieldsDetail() : array {
		if (!empty($this->fields)) return $this->fields;
		$prefix = $this->getPrefix();
		$db = Database::getInstance();
		$schemaAndTable = explode('.', $this->getTable());
		$schema = 'public';
		$table = $this->getTable();
		if (isset($schemaAndTable[1])) {
			$schema = $schemaAndTable[0];
			$table = $schemaAndTable[1];
		}

		$q = $db->prepare('
			SELECT 
    			column_name as "Field", 
    			data_type as "Type", 
    			is_nullable as "Null", 
    			column_default as "Default"
			FROM 
    			information_schema.columns
			WHERE 
				table_schema = :schema and   
				table_name = :table
			ORDER BY 
    			ordinal_position');
		$q->execute(['schema' => $schema, 'table' => $table]);
		$fields = $q->fetchAll(\PDO::FETCH_ASSOC);
		if (empty($fields)) {
			throw new ExceptionBase('Table ' . $this->getTable() . ' is not exist!');
		}
		$result = [];
		foreach ($fields as $v) {
			$result[$v['Field']] = $v;
		}
		return $result;
	}

	public function getData (mixed $id, ?array $cols = null) : mixed {
		$q = Database_Query::Select($this->getTable());
		if (is_array($id)) $q->where($id);
		else $q->where($this->getPrimaryKey(), $id);
		if (!empty($cols)) $q->columns($cols);

		$data = $q->execute($this->getDb());
		$this->getDb()->releaseConnection();
		return $data;
	}

	public function read(String $id, array $optWhere = []) : array | null {
		$q = Database_Query::Select($this->getTable())->where($this->getPrimaryKey(), $id);
		if (!empty($optWhere)) {
			foreach ($optWhere as $field => $value) {
				$q->where($field, $value);
			}
		}
		return $q->execute($this->getDb())->next('array');
	}

	public function create(array $data) : int {
		return $this->insert($data);
	}

	public function insert(array $data) : int {
		if (empty($data)) {
			throw new ExceptionBase('No Data to be added!');
		}

		return Database_Query::Insert($this->getTable())->data($data)->execute($this->getDb());
	}

	public function update(String $id, mixed $data, array $optWhere = []) : int {
		//remove ID field from being updated
		if (array_key_exists($this->getPrimaryKey(), $data)) {
			unset($data[$this->getPrimaryKey()]);
		}

		if (empty($data)) {
			throw new ExceptionBase('No Data to be updated!');
		}

		$q = Database_Query::Update($this->getTable())->data($data)->where($this->getPrimaryKey(), $id);
		if (!empty($optWhere)) {
			foreach ($optWhere as $field => $value) {
				$q->where($field, $value);
			}
		}
		$result = $q->execute($this->getDb());
		return $result;
	}

	public function delete(String $id, array $optWhere = []) : int {
		$q = Database_Query::Delete($this->getTable())->where($this->getPrimaryKey(), $id);
		if (!empty($optWhere)) {
			foreach ($optWhere as $field => $value) {
				$q->where($field, $value);
			}
		}
		return $q->execute($this->getDb());
	}

	public function getServerTimestamp() : mixed {
		$q = $this->getDb()->query('SELECT NOW() AS time ');
		$this->getDb()->releaseConnection();
		foreach($q as $v) return $v->time;
	}
}

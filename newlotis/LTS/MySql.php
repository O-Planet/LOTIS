<?php
namespace LTS;

class MySql
{
	public $link;
	public $name;
	public $dbtables;

	private $server;
	private $user;
	private $password;

	private $asarray = false;
	
	public function __construct($name, $server, $user, $password)
	{
		$this->server = $server;
		$this->user = $user;
		$this->password = $password;
		$this->name = $name;
		$this->dbtables = array();
		$this->link = null;
	}

	public function assarray() { $this->asarray = true; return $this; }
	public function assobjects() { $this->asarray = false; return $this; }
	
	public function connect()
	{
		$this->link = new \mysqli($this->server, $this->user, $this->password);
		if($this->link->connect_errno)
			return false;
		return true;
	}
	
	public function query($str_sql_query) 
	{						
		if($this->link === null)
			if(! $this->connect())
				return false;
		try
		{		
			$res = $this->link->query($str_sql_query);
		}
		catch (\Exception $e)
		{
			return false;
		}

		return $res;
	}

	public function real_escape_string($str)
	{
		if($this->link === null)
			if(! $this->connect())
				return $str;

		return $this->link->real_escape_string($str);
	}

	public function prepare($str_sql_query, $values)
	{
		if ($this->link === null && !$this->connect()) return false;

		$stmt = $this->link->prepare($str_sql_query);
		if (!$stmt) return false;

		if (!empty($values)) {
			$types = '';
			foreach ($values as $value) {
				if (is_int($value)) $types .= 'i';
				elseif (is_float($value)) $types .= 'd';
				else $types .= 's';
			}

			$stmt->bind_param($types, ...$values);
		}
		try {
			$res = $stmt->execute();
		} catch (\Exception $e) {
			return false;
		}

		return $stmt;
	}

	public function result($stmt, $asarray = false)
	{
		if (!$stmt || !is_object($stmt)) return false;

		try {
			$result = $stmt->get_result();
		} catch (\Exception $e) {
			// Fallback для случаев без mysqlnd
			$stmt->store_result();
			$variables = array();
			$data = array();
			$meta = $stmt->result_metadata();
			if ($meta) {
				while ($field = $meta->fetch_field()) {
					$variables[] = &$data[$field->name];
				}
				call_user_func_array(array($stmt, 'bind_result'), $variables);
				$res = [];
				while ($stmt->fetch()) {
					$row = array();
					foreach ($data as $key => $val) {
						$row[$key] = $val;
					}
					if($asarray || $this->asarray) {
						$res[] = $row;
					} else {
						$obj = new \stdClass();
						foreach ($row as $k => $v) $obj->$k = $v;
						$res[] = $obj;
					}
				}
				$stmt->close();
				return $res;
			}
			$stmt->close();
			return false;
		}

		$res = [];
		if ($result && $result->num_rows > 0) {
			if($asarray || $this->asarray)
				while ($ob = $result->fetch_array(MYSQLI_ASSOC))
					$res[] = $ob;
			else	
				while ($ob = $result->fetch_object()) 
					$res[] = $ob;
		}

		$stmt->close();
		return $res;
	}

	public function integritycheck($table, $rec)
	{
		if (is_object($table)) {
			$tablename = !empty($table->name) ? $table->name : null;
		} elseif (is_string($table)) {
			$tablename = $table;
		} else {
			return false;
		}

		if (is_numeric($rec)) {
			$id = $rec;
		} elseif (is_array($rec) && !empty($rec['id'])) {
			$id = $rec['id'];
		} elseif (is_object($rec) && !empty($rec->id)) {
			$id = $rec->id;
		} else {
			return false;
		}

		foreach ($this->dbtables as $t) {
			foreach ($t->fields as $field) {
				if ($field->type == 'TABLE' && $field->table->name == $tablename) {
					$sql = "SELECT COUNT(*) as kolvo FROM `{$this->name}`.`{$t->name}` WHERE `{$field->name}` = ?";
					$stmt = $this->prepare($sql, [$id]);
					if ($stmt === false) continue;

					$result = $this->result($stmt);
					if ($result && $result[0]->kolvo > 0) {
						return false;
					}
				}
			}
		}

		return true;
	}

	public function geterror()
	{
		return $this->link->error;
	}
	
	public function create()
	{
		if($this->link === null)
			if(! $this->connect())
				return false;
		$str_sql_query = "CREATE DATABASE IF NOT EXISTS `{$this->name}`";
		try
		{		
			$res = $this->link->query($str_sql_query);
		}
		catch (\Exception $e)
		{
			return false;
		}
		return $res;
	}
	
	public function table($name)
	{
		if(array_key_exists($name, $this->dbtables))
			return $this->dbtables[$name];
		else
		{
			$t = new MySqlTable($name);
			$this->dbtables[$name] = $t;
			$t->owner = $this;
			return $t;
		}
	}
	
	public function existstable($name)
	{
		return array_key_exists($name, $this->dbtables);
	}
	
	public function loadtable($name)
	{
		if (array_key_exists($name, $this->dbtables)) {
			return $this->dbtables[$name];
		}

		$safeName = $this->real_escape_string($name);
		$res = $this->query("
			SELECT TABLE_NAME 
			FROM information_schema.TABLES 
			WHERE TABLE_SCHEMA = '{$this->name}' 
			AND TABLE_NAME = '{$safeName}'
		");
		if (!$res || $res->num_rows == 0) {
			return false;
		}

		$table = new MySqlTable($name);
		$table->owner = $this;
		$this->dbtables[$name] = $table;

		$res = $this->query("
			SELECT 
				COLUMN_NAME,
				COLUMN_TYPE,
				IS_NULLABLE,
				COLUMN_DEFAULT,
				EXTRA,
				COLUMN_COMMENT,
				CHARACTER_SET_NAME,
				COLLATION_NAME
			FROM information_schema.COLUMNS 
			WHERE TABLE_SCHEMA = '{$this->name}' 
			AND TABLE_NAME = '{$safeName}'
			ORDER BY ORDINAL_POSITION
		");

		if ($res) {
			while ($col = $res->fetch_object()) {
				$this->addFieldToTable($table, $col);
			}
		}

		$res = $this->query("
			SELECT INDEX_NAME, GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) as COLUMNS
			FROM information_schema.STATISTICS 
			WHERE TABLE_SCHEMA = '{$this->name}' AND TABLE_NAME = '{$safeName}'
			AND INDEX_NAME != 'PRIMARY'
			GROUP BY INDEX_NAME
		");
		if ($res) {
			while ($idx = $res->fetch_object()) {
				$fields = \LTS::explodestr($idx->COLUMNS);
				$indexName = 'idx_' . implode('_', $fields);
				if (!in_array($indexName, $table->indexes)) {
					$table->indexes[] = $indexName;
				}
			}
		}

		return $table;
	}

	private function parseEnumFromColumnType($columnType)
	{
		if (strpos($columnType, 'enum(') === false) return [];
		
		preg_match("/enum\('(.+?)'\)/i", $columnType, $matches);
		if (empty($matches[1])) return [];

		return array_map(function($v) {
			return trim($v, "'");
		}, explode("','", $matches[1]));
	}
	
	private function addFieldToTable($table, $col)
	{
		$name = $col->COLUMN_NAME;
		$type = $col->COLUMN_TYPE;
		$nullable = $col->IS_NULLABLE === 'YES';
		$default = $col->COLUMN_DEFAULT;
		$extra = $col->EXTRA;

		if ($name === 'id') return;

		if (preg_match('/^enum\(/i', $type)) {
			$field = new MySqlField($name, 'ENUM');
			$field->values = $this->parseEnumFromColumnType($type);

		} elseif (preg_match('/^varchar\((\d+)\)$/i', $type, $matches)) {
			$field = new MySqlField($name, 'VARCHAR', (int)$matches[1]);

		} elseif (preg_match('/^char\((\d+)\)$/i', $type, $matches)) {
			$field = new MySqlField($name, 'CHAR', (int)$matches[1]);

		} elseif (preg_match('/^decimal\((\d+),(\d+)\)$/i', $type, $matches)) {
			$field = new MySqlField($name, 'DECIMAL', ['dd' => (int)$matches[1], 'dr' => (int)$matches[2]]);

		} elseif (preg_match('/^int\((\d+)\)$/i', $type)) {
			$field = new MySqlField($name, 'INT');

		} elseif (preg_match('/^tinyint\((\d+)\)$/i', $type)) {
			$field = new MySqlField($name, 'TINYINT');

		} elseif (preg_match('/^text$/i', $type)) {
			$field = new MySqlField($name, 'TEXT');

		} elseif (preg_match('/^datetime$/i', $type)) {
			$field = new MySqlField($name, 'DATETIME');

		} else {
			$field = new MySqlField($name, strtoupper(preg_replace('/\(.*/', '', $type)));
		}

		if ($nullable) {
			$field->nullable();
		}

		if ($default !== null && $default !== 'NULL') {
			$field->default = $default;
		}

		if (preg_match('/^(.+)_id$/', $name, $matches)) {
			$refTable = $matches[1];
			if ($this->existsTableInDatabase($refTable)) {
				$field->type = 'TABLE';
				$field->table = $refTable;
			}
		}

		$table->fields[$name] = $field;
	}

	private function existsTableInDatabase($tableName)
	{
		$safeName = $this->real_escape_string($tableName);
		$res = $this->query("
			SELECT 1 
			FROM information_schema.TABLES 
			WHERE TABLE_SCHEMA = '{$this->name}' AND TABLE_NAME = '{$safeName}'
			LIMIT 1
		");
		return $res && $res->num_rows > 0;
	}

	public function lastInsertId()
	{
		return $this->link->insert_id;
	}

	public function begin()
	{
		if ($this->link === null && !$this->connect()) return false;
		return $this->link->begin_transaction();
	}

	public function commit()
	{
		if ($this->link === null) return false;
		return $this->link->commit();
	}

	public function rollback()
	{
		if ($this->link === null) return false;
		return $this->link->rollback();
	}

	// Удобный метод для автоматического rollback при ошибке
	public function transaction(callable $callback)
	{
		if (!$this->begin()) return false;
		
		try {
			$result = $callback($this);
			if ($result === false) {
				$this->rollback();
				return false;
			}
			return $this->commit() ? $result : false;
		} catch (\Exception $e) {
			$this->rollback();
			return false;
		}
	}
}
?>
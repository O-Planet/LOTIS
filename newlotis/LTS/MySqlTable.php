<?php
namespace LTS;

class MySqlTable {

    public $name;
    public $fields;
    public $indexes;
	public $insert_id;

    public $str_sql_query;
    public $get_query;
    public $owner;

    public $collation;
    public $charset;
	public $distinct;

    public $queryBuilder; 
	public $asarray = false;

    public function __construct($name)
    {
        $this->name = $name;
        $this->fields = array();
        $this->indexes = array();
        $this->get_query = false;

		$this->charset = '';
		$this->collation = '';

        $this->owner = null;
		$this->distinct = false;

        $this->queryBuilder = null; 
    }

    public function getQuery()
    {
        $this->get_query = true;
        return $this;
    }

	public function query()
	{
		return new QueryBuilder($this, $this->owner);
	}
		
    public function field($name, $val = null)
    {
        if(array_key_exists($name, $this->fields))
        {
            if($val !== null)
                $this->fields[$name]->setvalue($val);

            return $this->fields[$name];
        }

        return null;
    }
	
	public function value($name, $val = null)
	{
		$f = $this->field($name);

		if($f === null)
			return $val === null ? null : $this;

		if($val === null)
			return $f->value;	

		$f->setvalue($val);
		return $this;
	}

	public function values($arr)
	{
		foreach($arr as $name => $val)
			$this->value($name, $val);

		return $this;
	}

    function int($name, $len = 11)
	{
        $field = new MySqlField($name, 'INT', $len);
		$this->fields[$name] = $field;
		return $this;
	}
	
    function string($name, $len = 100)
	{
        $field = new MySqlField($name, 'VARCHAR', $len);
		$this->fields[$name] = $field;
		return $this;
	}

    function text($name)
	{
        $field = new MySqlField($name, 'TEXT');
		$this->fields[$name] = $field;
		return $this;
	}
	
    function bool($name)
	{
        $field = new MySqlField($name, 'TINYINT');
		$this->fields[$name] = $field;
		return $this;
	}
	
    function date($name)
	{
        $field = new MySqlField($name, 'DATETIME');
		$this->fields[$name] = $field;
		return $this;
	}
	
    function float($name, $dd = 15, $dr = 2)
	{
        $field = new MySqlField($name, 'DECIMAL', array('dd' => $dd, 'dr' => $dr));
		$this->fields[$name] = $field;
		return $this;
	}
	
    function enum($name, $values = null)
	{
        $field = new MySqlField($name, 'ENUM');
        $field->values = $values;
		$this->fields[$name] = $field;
		return $this;
	}

	function file($name, $ext, $dir = false)
	{
        $field = new MySqlField($name, 'FILE', array('ext' => $ext, 'dir' => $dir));
		$this->fields[$name] = $field;

		if($dir !== false)
		{
			if(! file_exists($dir))
				@mkdir($dir, 0755, true);
		}
		else
        {
            $blob = new MySqlField("{$name}_filedata", 'BLOB');
            $this->fields["{$name}_filedata"] = $blob;            
        }

		return $this;
	}

    function point($name)
	{
        $field = new MySqlField($name, 'POINT');
		$this->fields[$name] = $field;
		return $this;
	}
	
    function table($name, $table)
	{
        $field = new MySqlField($name, 'TABLE');
        $field->table = $table;
		$this->fields[$name] = $field;
		return $this;
	}
	
    function parent($table)
	{
        $field = new MySqlField("parent_{$table->name}", 'TABLE');
        $field->table = $table;
		$this->fields["parent_{$table->name}"] = $field;
		return $this;
	}
	
	function index($name)
	{
		if(	! in_array($name, $this->indexes))
			$this->indexes[] = $name;

		return $this;
	}
	
	public function integritycheck($rec)
	{
		return $this->owner !== null ? $this->owner->integritycheck($this, $rec) : false;
	}

	private function formatCaption($name)
	{
		$name = str_replace('_', ' ', $name);
		$name = preg_replace('/([a-z])([A-Z])/u', '$1 $2', $name);
		return trim(mb_convert_case($name, MB_CASE_TITLE, 'UTF-8'));
	}

    public function generateinputs($fieldNames = null)
    {
		if ($fieldNames === null) {
			$fields = array_keys($this->fields);
		}
		else
			if(is_string($fieldNames))
				$fields = explode(',', $fieldNames);
			else
				$fields = $fieldNames;

		$inputs = [];

		foreach ($fields as $fieldName) {
			if ($fieldName === 'id') continue;

			$field = $this->field($fieldName);
			if (!$field) continue;

			$input = [
				'name' => $fieldName,
				'caption' => $this->formatCaption($fieldName),
			];

			switch ($field->type) {
				case 'TEXT':
					$input['type'] = 'textarea';
					break;
				case 'TABLE':
					$input['type'] = 'table';
					$input['dbtable'] = $field->table;
					break;
				case 'DATE':
					$input['type'] = 'date';
					break;
				case 'DATETIME':
				case 'TIMESTAMP':
					$input['type'] = 'datetime-local';
					break;
				case 'INT':
				case 'BIGINT':
					$input['type'] = 'number';
					break;
				case 'DECIMAL':
				case 'DOUBLE':
				case 'FLOAT':
					$input['type'] = 'numeric';
					break;
				case 'BOOLEAN':
				case 'TINYINT':
					if ($field->param == 1) {
						$input['type'] = 'checkbox';
						break;
					}
				case 'ENUM':
					if (!empty($field->values)) {
						$input['type'] = 'select';
						$input['values'] = $field->values;
					} else {
						$input['type'] = 'text';
					}
					break;
				default:
					$input['type'] = 'text';
			}

			if ($field->isset && $field->value !== null) {
				$input['readonly'] = '1';
			}

			$inputs[] = $input;
		}

		return $inputs;
    }

	public function create()
	{
		if ($this->owner === null) return false;

		if ($this->owner->link === null && !$this->owner->connect()) {
			return false;
		}

		$charset_info = $this->owner->link->get_charset();
		$charset = $this->charset == '' ? $charset_info->charset : $this->charset;
		$collation = $this->collation == '' ? $charset_info->collation : $this->collation;

		$parts = ["CREATE TABLE IF NOT EXISTS `{$this->owner->name}`.`{$this->name}` (
			`id` INT(11) NOT NULL AUTO_INCREMENT"];

		foreach ($this->fields as $name => $field) {
			$type = $field->gettype();
			$defaultPart = '';

			if ($field->default !== null) {
				$escapedDefault = $this->owner->real_escape_string($field->default);
				$defaultPart = " DEFAULT '{$escapedDefault}'";
			}

			$parts[] = "`{$name}` {$type}{$defaultPart}";
		}

		$indexParts = [];
		foreach ($this->indexes as $ind) {
			$iarr = \LTS::explodestr($ind);
			$_indstr = '';
			foreach ($iarr as $i) {
				$_indstr = $_indstr . (empty($_indstr) ? '' : ',') . "`" . trim($i) . "`";
			}
			if (!empty($_indstr)) {
				$indexParts[] = "INDEX ({$_indstr})";
			}
		}

		if (!empty($indexParts)) {
			$parts = array_merge($parts, $indexParts);
		}

		$parts[] = "PRIMARY KEY (`id`)
		) ENGINE = InnoDB DEFAULT CHARSET = `{$charset}` COLLATE = `{$collation}`";

		$this->str_sql_query = implode(",\n", $parts);

		if ($this->get_query) {
			$this->get_query = false;
			return $this->str_sql_query;
		}

		$res = $this->owner->query($this->str_sql_query);
		return $res !== false;
	}

    function get($id)
	{
		if ($this->owner === null || !is_numeric($id)) 
			return false;
		if ($this->owner->link === null && !$this->owner->connect()) 
			return false;

		$stmt = $this->owner->link->prepare(
			"SELECT * FROM `{$this->owner->name}`.`{$this->name}` WHERE `id` = ? LIMIT 1"
		);

		$stmt->bind_param("i", $id);
		$stmt->execute();

		$result = $stmt->get_result();

		return $result->fetch_object();	
	}

	function exists($id)
	{
		if ($this->owner === null || !is_numeric($id)) return false;
		if ($this->owner->link === null && !$this->owner->connect()) 
			return false;

		$stmt = $this->owner->link->prepare(
			"SELECT COUNT(*) as cnt FROM `{$this->owner->name}`.`{$this->name}` WHERE `id` = ? LIMIT 1"
		);
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$result = $stmt->get_result();
		$row = $result->fetch_object();
		$stmt->close();
		return $row->cnt > 0;
	}

	private function getParamType($value)
	{
		if (is_int($value)) {
			return 'i';
		} elseif (is_float($value)) {
			return 'd';
		} else {
			return 's';
		}
	}

	function with($relation)
	{
		$field = $this->field($relation);
		if (!$field || $field->type !== 'TABLE' || !$field->table) {
			return $this;
		}

		$targetTable = $field->table;
		$alias = $relation;

		if ($this->queryBuilder === null) {
			$this->queryBuilder = new QueryBuilder($this, $this->owner);
		}

		$this->queryBuilder->leftJoin(
			$targetTable,
			"`{$this->name}`.`{$relation}` = `{$alias}`.`id`",
			$alias
		);

		return $this;
	}

	function insert()
	{
		if ($this->owner === null) return 0;
		if ($this->owner->link === null && !$this->owner->connect()) 
			return 0;

		$fields = [];
		$values = [];
		$params = [];

		foreach ($this->fields as $name => $field) {
			if ($field->isset && $field->value !== null) {
				$fields[] = "`{$name}`";
				$values[] = '?';
				$params[] = $field->value;
			}
		}

		$this->freevalues();

		if (empty($fields)) return 0;

		$this->str_sql_query = "INSERT INTO `{$this->owner->name}`.`{$this->name}` (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $values) . ")";

		if ($this->get_query) {
			$this->get_query = false;
			return $this->str_sql_query;
		}

		$stmt = $this->owner->prepare($this->str_sql_query, $params);
		if ($stmt === false) return 0;

		return $this->owner->lastInsertId();
	}

	function set($id = null)
	{
		if ($this->owner === null) return 0;
		if ($this->owner->link === null && !$this->owner->connect()) 
			return 0;
		if (empty($id)) return $this->insert();

		$get_query = $this->get_query;
		$success = $this->setall(['id' => $id]);

		if($get_query) 
			return $success;

		return $success ? $id : 0;
	}

	function setall($conditions = null, $operator = 'AND')
	{
		if ($this->owner === null) return false;
		if ($this->owner->link === null && !$this->owner->connect())
			return false;

		$data = [];
		foreach ($this->fields as $name => $field) 
			if ($field->isset) 
				$data[$name] = $field->value;
		$this->freevalues();

		if (empty($data)) return false; 

		$queryBuilder = new QueryBuilder($this, $this->owner);

		if ($conditions !== null) {
			if (is_array($conditions)) {
				foreach ($conditions as $key => $value) {
					switch (strtoupper($key)) {
						case 'WHERE':
							$queryBuilder->where($value, $operator);
							break;
						case 'ORDER':
							$queryBuilder->orderBy($value);
							break;
						case 'LIMIT':
							$queryBuilder->limit($value);
							break;
						default:
							$queryBuilder->addCondition($key, $value, $operator);
					}
				}
			} elseif (is_string($conditions)) {
				$queryBuilder->where($conditions);
			}
		}

		if($this->get_query)
		{
			$queryBuilder->getQuery();
			$this->get_query = false;
		}

		$result = $queryBuilder->setall($data);
		$this->str_sql_query = $queryBuilder->str_sql_query;
		return $result;
	}

	public function freevalues()
    {
        foreach($this->fields as $field)
            $field->isset = false;

        return $this;
    }

	function all($name = null, $val = null)
	{
		if ($this->owner === null) return false;
		if ($this->owner->link === null && !$this->owner->connect()) 
			return false;

		$queryBuilder = new QueryBuilder($this, $this->owner);

		if (is_array($name)) {
			$operator = (is_string($val) ? $val : 'AND');

			foreach ($name as $key => $value) {
				if ($value === null || !isset($value)) continue;

				$upkey = strtoupper($key);
				switch ($upkey) {
					case 'FIELDS':
						$queryBuilder->select($value);
						break;
					case 'ORDER':
						$queryBuilder->orderBy($value);
						break;
					case 'GROUP':
						$queryBuilder->groupBy($value);
						break;
					case 'LIMIT':
						$queryBuilder->limit($value);
						break;
					case 'JOIN':
					case 'LEFT JOIN':
					case 'RIGHT JOIN':
						$type = strtoupper($key) === 'JOIN' ? 'INNER' : substr(strtoupper($key), 0, -5);
						$table = !empty($value['table']) ? $value['table'] : $value;
						$on = !empty($value['on']) ? $value['on'] : null;
						$alias = !empty($value['alias']) ? $value['alias'] : null;

						if (!$on) break;

						if ($type === 'LEFT') {
							$queryBuilder->leftJoin($table, $on, $alias);
						} elseif ($type === 'RIGHT') {
							$queryBuilder->rightJoin($table, $on, $alias);
						} else {
							$queryBuilder->join($table, $on, $alias);
						}
						break;
					case 'WHERE':
						$queryBuilder->where($value, $operator);
						break;
					default:
						if(in_array($upkey, QueryBuilder::$aggritems))
							$queryBuilder->aggregate($key, $value);
						else
							$queryBuilder->addCondition($key, $value, $operator);
				}
			}
		} elseif (is_string($name)) {
			if ($val === null) {
				$queryBuilder->where($name);
			} else {
				$queryBuilder->addCondition($name, $val, 'AND');
			}
		}

		$this->str_sql_query = $queryBuilder->getSQL();

		if ($this->get_query) {
			$this->get_query = false;
			return $this->str_sql_query;
		}

		$params = $queryBuilder->getParams();

		$stmt = $this->owner->prepare($this->str_sql_query, $params);
		if ($stmt === false) {
			return false;
		}

		$result = $this->owner->result($stmt, $this->asarray);
		$this->asarray = false;
		return empty($result) ? false : $result;
	}

	function del($id)
	{
		return $this->delall(['id' => (int) $id]);
	}

	function delall($name = null, $val = null)
	{
		if ($this->owner === null) return false;
		if ($this->owner->link === null && !$this->owner->connect()) 
			return false;

    	$queryBuilder = new QueryBuilder($this, $this->owner); 
		$operator = 'AND';

		if($name !== null && $val !== null) 
			if(is_array($name) && is_string($val)) {
				$conditions = $name;
				$operator = $val;
			}	
			else
				$conditions = [$name => $val];
		else
			$conditions = $name;

		if (is_array($conditions)) {
			foreach ($conditions as $key => $value) {
				switch (strtoupper($key)) {
					case 'WHERE':
						$queryBuilder->where($value, $operator);
						break;
					case 'ORDER':
						$queryBuilder->orderBy($value);
						break;
					case 'LIMIT':
						$queryBuilder->limit($value);
						break;
					default:
						$queryBuilder->addCondition($key, $value, $operator);
				}
			}
		} elseif (is_string($conditions)) {
			$queryBuilder->where($conditions);
		}

		$this->str_sql_query = $queryBuilder->getSQL('DELETE');

		if ($this->get_query) {
			$this->get_query = false;
			return $this->str_sql_query;
		}

		$params = $queryBuilder->getParams();

		$stmt = $this->owner->prepare($this->str_sql_query, $params);
		return $stmt ? true : false;
	}

	public function checkupdate()
	{
		if ($this->owner === null) return false;
		if ($this->owner->link === null && !$this->owner->connect()) 
			return false;

		$safeName = $this->owner->real_escape_string($this->name);
		$res = $this->owner->query("
			SELECT TABLE_NAME 
			FROM information_schema.TABLES 
			WHERE TABLE_SCHEMA = '{$this->owner->name}' 
			AND TABLE_NAME = '{$safeName}'
		");
		if (!$res || $res->num_rows == 0) {
			return $this->create();
		}

		$columns = [];
		$indexes = [];

		$res = $this->owner->query("
			SELECT 
				COLUMN_NAME, 
				COLUMN_TYPE, 
				IS_NULLABLE, 
				COLUMN_DEFAULT, 
				COLUMN_COMMENT,
				CHARACTER_SET_NAME,
				COLLATION_NAME
			FROM information_schema.COLUMNS 
			WHERE TABLE_SCHEMA = '{$this->owner->name}' AND TABLE_NAME = '{$safeName}'
		");
		if ($res) {
			while ($row = $res->fetch_object()) {
				$columns[$row->COLUMN_NAME] = $row;
			}
		}

		$res = $this->owner->query("
			SELECT INDEX_NAME, GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) as COLUMNS
			FROM information_schema.STATISTICS 
			WHERE TABLE_SCHEMA = '{$this->owner->name}' AND TABLE_NAME = '{$safeName}'
			GROUP BY INDEX_NAME
		");
		if ($res) {
			while ($row = $res->fetch_object()) {
				$indexes[$row->INDEX_NAME] = explode(',', $row->COLUMNS);
			}
		}

		$alterParts = [];

		foreach ($this->fields as $name => $field) {
			$type = $field->gettype();
			$nullable = $field->getnull();
			$default = $field->default !== null 
				? " DEFAULT '" . $this->owner->real_escape_string($field->default) . "'" 
				: '';

			if (isset($columns[$name])) {
				$col = $columns[$name];
				$currentType = strtolower($col->COLUMN_TYPE);
				$newType = strtolower($type);

				$needsChange = false;

				if ($currentType !== $newType) {
					$needsChange = true;
				}

				if($field->default !== null)
				{
					$currentDefault = $col->COLUMN_DEFAULT;
					$newDefault = $field->default;

					if (($currentDefault === null && $newDefault !== null) ||
						($currentDefault !== null && $newDefault === null) ||
						($currentDefault !== $newDefault)) {
						$needsChange = true;
					}
				}

				if($field->nullable !== NULL)
				{
					$currentNull = $col->IS_NULLABLE === 'YES';
					$newNull = $field->nullable;

					if ($currentNull !== $newNull) {
						$needsChange = true;
					}
				}

				if ($field->type === 'ENUM') {
					$currentValues = $this->parseEnumFromColumnType($col->COLUMN_TYPE);
					$newValues = array_keys(!empty($field->values) ? $field->values : []);
					if (json_encode($currentValues) !== json_encode($newValues)) {
						$needsChange = true;
					}
				}

				if ($needsChange) {
					if ($field->type === 'ENUM') {
						$charset = empty($col->CHARACTER_SET_NAME) ? 'utf8mb4' : $col->CHARACTER_SET_NAME;
						$collation = empty($col->COLLATION_NAME) ? 'utf8mb4_unicode_ci' : $col->COLLATION_NAME;

						$values = implode("','", array_keys(!empty($field->values) ? $field->values : []));

						$defaultClause = '';
						if ($field->default !== null) {
							$escapedDefault = $this->owner->real_escape_string($field->default);
							$defaultClause = " DEFAULT '{$escapedDefault}'";
						} elseif ($col->IS_NULLABLE === 'YES' && $col->COLUMN_DEFAULT === null) {
							$defaultClause = " DEFAULT NULL";
						}

						$clause = "CHANGE `{$name}` `{$name}` ENUM('{$values}') "
								. "CHARACTER SET {$charset} "
								. "COLLATE {$collation} "
								. "{$nullable}{$defaultClause}";

						$alterParts[] = $clause;
					} else {
						$alterParts[] = "MODIFY `{$name}` {$type} {$nullable}{$default}";
					}
				}
			} else {
				$alterParts[] = "ADD `{$name}` {$type} {$nullable}{$default}";
			}
		}

		foreach ($columns as $name => $col) {
			if ($name === 'id') continue;
			if (!array_key_exists($name, $this->fields)) {
				$alterParts[] = "DROP `{$name}`";
			}
		}

		foreach ($this->indexes as $index) {
			$fields = \LTS::explodestr($index);
			$indexName = 'idx_' . implode('_', $fields);
			$indexFields = implode(',', array_map(function($f) { return "`{$f}`"; }, $fields));

			if (!isset($indexes[$indexName])) {
				$alterParts[] = "ADD INDEX `{$indexName}` ({$indexFields})";
			}
		}

		$charset_info = $this->owner->link->get_charset();
		$charset = $this->charset == '' ? $charset_info->charset : $this->charset;
		$collation = $this->collation == '' ? $charset_info->collation : $this->collation;

		$res = $this->owner->query("SHOW TABLE STATUS LIKE '{$safeName}'");
		if ($res && $row = $res->fetch_object()) {
			if ($row->Collation !== $collation) {
				$alterParts[] = "DEFAULT COLLATE = `{$collation}`";
			}
		}

		return empty($alterParts) ? true : "ALTER TABLE `{$this->owner->name}`.`{$this->name}` " . implode(', ', $alterParts);
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

	public function update()
	{
        if($this->owner === null)
            return false;
		if ($this->owner->link === null && !$this->owner->connect()) 
			return false;

		if($this->get_query)
		{
			$this->get_query = false;
			return $this->checkupdate();
		}
			
		$str = $this->checkupdate();

		if(is_bool($str))
			return $str;

		$res = $this->owner->query($str);
		return $res;
	}
}
?>
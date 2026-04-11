<?php
namespace LTS;

class QueryBuilder
{
    public $table;
    public $owner;

    public $fields = ['*'];
    public $where = [];
    public $params = [];
    public $operators = ['AND'];
    public $order = [];
    public $group = [];
    public $limit = '';
    public $aggregates = [];
    public $distinct = false;
    public $joins = [];
    public $union = [];

    public $str_sql_query;
    public $get_query;
    public $asarray = false;

    private $processedFields = [];
    private $joinedTables = [];

    static $aggritems = [
        'SUM',
        'MIN',
        'MAX',
        'AVG',
        'STD',
        'VARIANCE',
        'GROUP_CONCAT',
        'COUNT',
        'DISTINCT',
        'BIT_AND',
        'BIT_OR',
        'BIT_XOR',
        'JSON_ARRAYAGG'];

    public function __construct($table, $owner)
    {
        $this->table = $table;
        $this->owner = $owner;
        $this->get_query = false;
    }

    public function getQuery()
    {
        $this->get_query = true;
        return $this;
    }

    public function select($fields)
    {
        $this->fields = is_array($fields) ? $fields : array_map('trim', \LTS::explodestr($fields));
        return $this;
    }

    private function processField($f)
    {
        if (isset($this->processedFields[$f])) {
            return $this->processedFields[$f];
        }

        $pos = strrpos(strtolower($f), ' as ');

        if ($pos !== false) {
            $left = substr($f, 0, $pos);
            $right = trim(substr($f, $pos + 4));
            $result = $this->processExpression($left) . " AS `{$right}`";
        } else {
            $result = $this->processExpression($f);
        }

        $this->processedFields[$f] = $result;
        return $result;
    }

    private function processExpression($expr)
    {
        if (strpos($expr, '.') !== false) {
            $this->autoJoinForField($expr);
        }

        $expr = preg_replace_callback(
            '/(?<!`) ([a-zA-Z_][a-zA-Z0-9_]*)\.([a-zA-Z_][a-zA-Z0-9_]*) /',
            function ($matches) {
                return "`{$matches[1]}`.`{$matches[2]}`";
            },
            ' ' . $expr . ' '
        );
        $expr = trim($expr);

        $expr = preg_replace_callback(
            '/(?<![\w`]) ([a-zA-Z_][a-zA-Z0-9_]*) (?![\w(])/x',
            function ($matches) {
                $field = $matches[1];
                if (!$this->table->field($field) && strtolower($field) !== 'id') {
                    return $matches[0];
                }
                return "`{$this->table->name}`.`{$field}`";
            },
            $expr
        );

        return $expr;
    }

    public function join($table, $on, $type = 'INNER', $alias = null)
    {
        if (is_object($table) && isset($table->name)) {
            $tableName = $table->name;
            $dbName = $table->owner->name;
        } else {
            $tableName = $table;
            $dbName = $this->owner->name;
        }

        $alias = empty($alias) ? $tableName : $alias;
        $this->joins[] = compact('type', 'dbName', 'tableName', 'alias', 'on');
        return $this;
    }

    public function leftJoin($table, $on, $alias = null)
    {
        return $this->join($table, $on, 'LEFT', $alias);
    }

    public function rightJoin($table, $on, $alias = null)
    {
        return $this->join($table, $on, 'RIGHT', $alias);
    }

    public function andWhere($conditions)
    {
        return $this->where($conditions, 'AND');
    }

    public function orWhere($conditions)
    {
        return $this->where($conditions, 'OR');
    }

    public function fields($fields)
    {
        $this->select($fields);
        return $this;
    }

    public function with($relation)
    {
        $field = $this->table->field($relation);
        if (!$field || $field->type !== 'TABLE' || !$field->table) {
            return $this;
        }

        $targetTable = $field->table;
        $alias = $relation;

        foreach ($this->joins as $j) {
            if ($j['alias'] === $alias) {
                return $this;
            }
        }

        $this->leftJoin($targetTable, "`{$this->table->name}`.`{$relation}` = `{$alias}`.`id`", $alias);
        return $this;
    }

    public function union($other, $all = false)
    {
        $this->union[] = [
            'builder' => $other,
            'type' => $all ? 'UNION ALL' : 'UNION'
        ];
        return $this;
    }

    public function unionAll($other)
    {
        return $this->union($other, true);
    }

    public function all()
    {
        $sql = $this->getSQL('SELECT');

        if($this->get_query)
        {
            $this->get_query = false;
            return $this->str_sql_query;
        }

        $params = $this->getParams();

        $stmt = $this->owner->prepare($sql, $params);
        if ($stmt === false) return false;

        $result = $this->owner->result($stmt, $this->asarray);
        $this->asarray = false;
        
        return empty($result) ? false : $result;
    }

    public function subquery($callback)
    {
        $sub = new self($this->table, $this->owner);
        $callback($sub);
        return $sub;
    }

    public function whereInSubquery($field, $callback)
    {
        $sub = $this->subquery($callback);
        $qualifiedField = $this->quoteQualifiedField(
            strpos($field, '.') === false ? "{$this->table->name}.{$field}" : $field
        );
        $this->where[] = "{$qualifiedField} IN (" . $sub->getSQL() . ")";
        $this->params = array_merge($this->params, $sub->getParams());
        return $this;
    }

    public function whereEqualSubquery($field, $callback)
    {
        $sub = $this->subquery($callback);
        $qualifiedField = $this->quoteQualifiedField(
            strpos($field, '.') === false ? "{$this->table->name}.{$field}" : $field
        );
        $this->where[] = "{$qualifiedField} = (" . $sub->getSQL() . ")";
        $this->params = array_merge($this->params, $sub->getParams());
        return $this;
    }

    public function first()
    {
        $this->limit(1);
        $result = $this->all();
        return is_array($result) ? (isset($result[0]) ? $result[0] : false) : false;
    }

    public function where($conditions, $operator = 'AND')
    {
        if (is_string($conditions))
        {
            $isLogicalOperator = in_array(strtoupper($operator), ['AND', 'OR']);
            if($isLogicalOperator) {
                $this->where[] = "({$conditions})";
                $this->operators[] = $operator;
                return $this;
            }
            else
                return $this->where([$conditions => $operator], 'AND');
        }

        if (is_array($conditions)) {
            foreach ($conditions as $key => $value) {
                if ($value === null || !isset($value)) continue;

                $upkey = strtoupper($key);
                
                if($upkey == 'WHERE') {
                    if (is_string($value)) {
                        $this->where[] = "({$value})";
                        $this->operators[] = $operator;
                    }
                }
                elseif(! in_array($upkey, QueryBuilder::$aggritems))
                    $this->addCondition($key, $value, $operator);
            }
        }

        return $this;
    }

    public function whereGroup($conditions, $operator = 'AND')
    {
        $groupBuilder = new self($this->table, $this->owner);

        if (is_callable($conditions)) {
            $conditions($groupBuilder);
        } elseif (is_array($conditions)) {
            foreach ($conditions as $key => $value) {
                $groupBuilder->addCondition($key, $value, $operator);
            }
        }

        if (!empty($groupBuilder->where)) {
            $this->where[] = '(' . implode(' ' . $operator . ' ', $groupBuilder->where) . ')';
            $this->params = array_merge($this->params, $groupBuilder->getParams());
        }

        return $this;
    }

    public function addCondition($name, $value, $operator)
    {
        $pos = strlen($name);
        $isLeft = strpos($name, ':left') === $pos - 5;
        $isRight = strpos($name, ':right') === $pos - 6;

        if ($isLeft || $isRight) {
            $_name = substr($name, 0, $isLeft ? $pos - 5 : $pos - 6);
            $op = $isLeft ? '>=' : '<=';
            $val = $isRight && strpos($value, ':') === false ? "{$value} 23:59:59" : $value;
            $this->addWhereCondition($_name, $op, $val, $operator);
        } 
        else 
            $this->addWhereCondition($name, '=', $value, $operator);
    }

    public function autoJoinForField($field)
    {
        if (isset($this->joinedTables[$field])) return;
        if (strpos($field, '.') === false) return;

        list($tableKey, $subField) = explode('.', $field, 2);

        $fieldObj = $this->table->field($tableKey);
        if (!$fieldObj || $fieldObj->type !== 'TABLE' || !$fieldObj->table) return;

        $targetTable = $fieldObj->table;
        $alias = $tableKey;

        foreach ($this->joins as $j) {
            if ($j['alias'] === $alias) return;
        }

        $this->leftJoin(
            $targetTable,
            "`{$this->table->name}`.`{$tableKey}` = `{$alias}`.`id`",
            $alias
        );

        $this->joinedTables[$field] = true;
    }

    private function quoteQualifiedField($field)
    {
        if (strpos($field, '.') !== false) {
            list($table, $subField) = explode('.', $field, 2);
            return "`{$table}`.`{$subField}`";
        }
        return "`{$field}`";
    }

    private function addWhereCondition($field, $op, $value, $operator)
    {
        $qualifiedField = $field;
        if (strpos($field, '.') === false) {
            $qualifiedField = "{$this->table->name}.{$field}";
        }

        $this->autoJoinForField($field);

        $c = is_string($value) ? substr($value, 0, 1) : '';
        $raw = false;

        if ($c === '@' && is_string($value)) {
            $value = substr($value, 1);
            $raw = true;
        }

        if ($raw) {
            $this->where[] = $this->quoteQualifiedField($qualifiedField) . " {$op} {$value}";
        } elseif (is_string($value) && strpos($value, 'day:') === 0) {
            $date = substr($value, 4, 10);
            $this->addRangeCondition($qualifiedField, "{$date} 00:00:00", "{$date} 23:59:59");
        } elseif (is_string($value) && strpos($value, 'period:') === 0) {
            $p = strpos($value, ',');
            if ($p > 7) {
                $d1 = substr($value, 7, 10);
                $d2 = substr($value, $p + 1, 10);
                $this->addRangeCondition($qualifiedField, "{$d1} 00:00:00", "{$d2} 23:59:59");
            }
        } else {
            $condition = $this->parseOperator($qualifiedField, $op, $value);
            if ($condition) {
                $this->where[] = $condition;
            }
        }

        $this->operators[] = $operator;
    }

    private function esc($v, $stripPrefix = 0) {
        if ($stripPrefix > 0 && is_string($v)) {
            $v = substr($v, $stripPrefix);
        }
        $this->params[] = $v;
        return '?';
    }

    public function parseOperator($field, $op, $value)
    {
        $quotedField = $this->quoteQualifiedField($field);

        if (is_array($value)) {
            $placeholders = str_repeat('?,', count($value) - 1) . '?';
            foreach ($value as $v) {
                $this->params[] = $v;
            }
            return "{$quotedField} IN ({$placeholders})";
        }
        
        $first = is_string($value) ? substr($value, 0, 1) : '';
        
        // Двухсимвольные операторы
        if (strlen($value) >= 2) {
            $two = substr($value, 0, 2);
            if ($two === '<=') {
                return "{$quotedField} <= " . $this->esc($value, 2);
            }
            if ($two === '>=') {
                return "{$quotedField} >= " . $this->esc($value, 2);
            }
        }
        
        // Односимвольные
        switch ($first) {
            case '!': 
                return "{$quotedField} != " . $this->esc($value, 1);
            case '<': 
                return "{$quotedField} < " . $this->esc($value, 1);
            case '>': 
                return "{$quotedField} > " . $this->esc($value, 1);
            case '%': 
                return "{$quotedField} LIKE " . $this->esc($value, 1);
            case '$': 
                $val = substr($value, 1);
                $this->params[] = "%{$val}%";
                return "{$quotedField} LIKE ?";
            case '(': 
                return "{$quotedField} IN {$value}";
        }
        
        if ($value === '') {
            return "{$quotedField} = ''";
        }
        
        return "{$quotedField} = " . $this->esc($value);
    }

    public function orderBy($fields)
    {
        foreach (\LTS::explodestr($fields) as $f) {
            $f = trim($f);
            if ($f === '') continue;

            $desc = substr($f, 0, 1) === '-';
            $field = $desc ? substr($f, 1) : $f;

            $qualified = $this->processExpression($field);

            $this->order[] = "{$qualified} " . ($desc ? 'DESC' : 'ASC');
        }
        return $this;
    }

    public function groupBy($fields)
    {
        $fields = \LTS::explodestr($fields);
        foreach ($fields as $field) {
            $field = trim($field);
            if ($field === '') continue;

            $this->group[] = $this->processExpression($field);
        }
        return $this;
    }

    public function limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    public function aggregate($type, $fields)
    {
        $fn = strtoupper($type);

        foreach (\LTS::explodestr($fields) as $f) {
            $as = '';
            if (strpos($f, ' as ') !== false) {
                list($col, $alias) = explode(' as ', $f, 2);
                $col = trim($col);
                $alias = trim($alias);
                $f = $col;
                $as = " AS `{$alias}`";
            }

            if ($f === '*') $f = 'id';

            $processed = $this->processExpression($f);

            if ($fn === 'DISTINCT') {
                $this->aggregates[] = "COUNT(DISTINCT {$processed})" . $as;
            } else {
                $this->aggregates[] = "{$fn}({$processed})" . $as;
            }
        }
        return $this;
    }

    public function distinct($enable = true)
    {
        $this->distinct = $enable;
        return $this;
    }

    public function getSQL($type = 'SELECT')
    {
        $sql = '';

        switch ($type) {
            case 'DELETE':
                if (!empty($this->joins)) {
                    $sql = "DELETE `{$this->table->name}` FROM `{$this->owner->name}`.`{$this->table->name}` AS `{$this->table->name}`";
                } else {
                    $sql = "DELETE FROM `{$this->owner->name}`.`{$this->table->name}`";
                }
                break;
            default:
                $sql = "SELECT ";

                if ($this->distinct && empty($this->aggregates)) {
                    $sql .= "DISTINCT ";
                }

                $selectParts = [];

                if (!empty($this->aggregates)) {
                    $selectParts = array_merge($selectParts, $this->aggregates);
                }

                if ($this->fields !== ['*']) {
                    foreach ($this->fields as $f) 
                        $selectParts[] = $this->processField($f);
                    
                } elseif (empty($this->aggregates)) {
                    $selectParts[] = "`{$this->table->name}`.*";
                }

                $sql .= implode(', ', empty($selectParts) ? ['*'] : $selectParts);

                $sql .= " FROM `{$this->owner->name}`.`{$this->table->name}` AS `{$this->table->name}`";
            }

        foreach ($this->joins as $j) {
            $sql .= " {$j['type']} JOIN `{$j['dbName']}`.`{$j['tableName']}` AS `{$j['alias']}` ON {$j['on']}";
        }

        if (!empty($this->where)) {
            $whereParts = [];
            foreach ($this->where as $i => $condition) {
                if ($i === 0) {
                    $whereParts[] = $condition;
                } else {
                    $op = isset($this->operators[$i]) ? $this->operators[$i] : 'AND'; 
                    $whereParts[] = $op . ' ' . $condition;
                }
            }
            $sql .= " WHERE " . implode(' ', $whereParts);
        }

        if($type === 'SELECT')
        {
            if (!empty($this->group)) {
                $sql .= " GROUP BY " . implode(', ', $this->group);
            }

            if (!empty($this->order)) {
                $sql .= " ORDER BY " . implode(', ', $this->order);
            }
        }

        if (! empty($this->limit)) {
            $sql .= " LIMIT {$this->limit}";
        }

        if($type === 'SELECT')
        {
            $result = $sql;
            foreach ($this->union as $union) {
                $result .= " {$union['type']} " . $union['builder']->getSQL($type);
            }

            $sql = $result;
        }

        $this->str_sql_query = $sql;

        return $sql;
    }

    public function addRangeCondition($field, $from, $to)
    {
        $quotedField = $this->quoteQualifiedField($field);
        $this->where[] = "{$quotedField} >= ? AND {$quotedField} <= ?";
        $this->params[] = $from;
        $this->params[] = $to;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function getUpdateSQL($data)
    {
        $setFields = [];
        foreach ($data as $name => $value) {
            $processed = $this->processExpression($name);
            $setFields[] = "{$processed} = ?";
        }

        $sql = "UPDATE `{$this->owner->name}`.`{$this->table->name}` AS `{$this->table->name}`";

        foreach ($this->joins as $j) {
            $sql .= " {$j['type']} JOIN `{$j['dbName']}`.`{$j['tableName']}` AS `{$j['alias']}` ON {$j['on']}";
        }

        $sql .= " SET " . implode(', ', $setFields);

        if (!empty($this->where)) {
            $whereParts = [];
            foreach ($this->where as $i => $condition) {
                if ($i === 0) {
                    $whereParts[] = $condition;
                } else {
                    $op = isset($this->operators[$i]) ? $this->operators[$i] : 'AND'; 
                    $whereParts[] = $op . ' ' . $condition;
                }
            }
            $sql .= " WHERE " . implode(' ', $whereParts);
        }

        if (!empty($this->order)) {
            $sql .= " ORDER BY " . implode(', ', $this->order);
        }

        if (!empty($this->limit)) {
            $sql .= " LIMIT {$this->limit}";
        }

        return $sql;
    }

    public function setall($data)
    {
        $sql = $this->getUpdateSQL($data);

        if ($this->get_query) {
            print_r($this->getParams());
            $this->get_query = false;
            return $sql;
        }

        $setParams = array_values($data);
        $whereParams = $this->getParams();
        $params = array_merge($setParams, $whereParams);

        $stmt = $this->owner->prepare($sql, $params);
        return $stmt ? true : false;
    }
}
?>
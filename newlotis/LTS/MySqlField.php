<?php
namespace LTS;

class MySqlField {
    public $name,
        $type,
        $param,
        $table,
        $value,
        $values,
        $isset,
        $default,
        $nullable, 
        $out;

    function __construct($name, $type, $param = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->param = $param;
        $this->value = null;
        $this->default = null;
        $this->isset = false;
        $this->nullable = null; 
        $this->table = null;
        $this->values = null;
        $this->out = null;
    }

    public function setvalue($val)
    {
        $this->value = $val;
        $this->isset = true;
        return $this;
    }

    public function nullable($val = true)
    {
        $this->nullable = $val;
        return $this;
    }

    public function getnull()
    {
        return $this->nullable === NULL ? '' : ($this->nullable ? 'NULL' : 'NOT NULL');
    }
    
    public function gettype()
    {
        switch($this->type)
        {
            case 'ENUM':
                $values = '';
                if(\LTS::isAssociativeArray($this->values))
                    foreach($this->values as $name => $value)
                        $values .= ($values == '' ? '' : ',') . "'{$name}'";
                else
                    foreach($this->values as $value)
                        $values .= ($values == '' ? '' : ',') . "'{$value}'";
                return "ENUM({$values})";
            case 'FILE':
                return 'VARCHAR(255)';
            case 'BOOL':
                return 'TINYINT(1)';
            case 'TABLE':
                return 'INT(11)';
            case 'FLOAT':
            case 'DECIMAL':
                return "DECIMAL({$this->param['dd']},{$this->param['dr']})";
            default:
                return $this->param === null ? $this->type : "{$this->type}({$this->param})";    
        }
    }
}
?>
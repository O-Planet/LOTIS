<?php
namespace LTS;

class DataTable extends Element
{
    public $head;
    public $rows;
    public $hidden;
    public $sorted;
    public $norows; 
    public $out;
    
    public function __construct($id = '')
    {
        parent::__construct($id);
        $this->classname = 'data-table';
        $this->tagname = 'table';
        $this->norows = true; 
        $this->out = '';
    }

    public function hidden($hidden)
    {
        $this->hidden = \LTS::explodestr($hidden);
        return $this;
    }

    public function sort($name)
    {
        $this->sorted = $name;
        return $this;
    }

    public function head($head)
    {
        $this->head = $head;
        return $this;
    }

    public function data($rows)
    {
        $this->rows = $rows;
        return $this;
    }

    public function out($func)
    {
        if(strpos(trim($func), 'function') === 0)
            $_func = $func;
        else
            $_func = "function (row, data) { {$func}; }";

        $this->out = $_func;

        return $this;
    }

    public function append($values)
    {
        $this->rows[] = $values;
        return $this;
    }

    public function add($name, $value = null)
    {
        if($value !== null && is_array($name) && is_array($value))
        {
            if(DataTable::isArrayOfArraysWithNumericKeys($name))
            {
                $this->head($value);
                $this->data($name);
            }
            else
            {
                $this->head($name);
                $this->data($value);
            }
        }
        elseif($value === null && is_array($name))
        {
            if(DataTable::isArrayOfArraysWithNumericKeys($name))
                $this->data($name);
            else
                $this->head($name);
        }
        else
            return parent::add($name, $value);

        return $this;
    }

    public function isArrayOfArraysWithNumericKeys($array) {
        if (!is_array($array)) {
            return false;
        }
    
        foreach ($array as $key => $element) {
            if (!is_object($element) && !is_array($element)) 
                return false;            
            if (!is_int($key)) 
                return false;            
        }
    
        return true;
    }

    public function rowclick($form) {
        $this->method('rowclick(row)', 
<<<JS
        const ltsDataId = jQuery(row).data('id');
        if(jQuery(event.target).closest('.Column_sel').length)
        {
            let selthis = LTS({$this->id}).values({ ltsDataId: ltsDataId })[0].sel;
            selthis = selthis ? 0 : 1;
            LTS({$this->id}).values({ ltsDataId: ltsDataId }, { 'sel': selthis });
        }
        else 
        {
            let str = LTS({$this->id}).values({ ltsDataId: ltsDataId })[0];
            if(LTS({$this->id}).loadrowclick) {
                const ltsRetLoadValues = LTS({$this->id}).loadrowclick(str);
                for(_n in ltsRetLoadValues)
                    str[_n] = ltsRetLoadValues[_n];
            }
            LTS({$form->id}).values(str);
            $({$form->id}).attr('ltsDataId', ltsDataId);
            LTS.signal('{$this->id}_RowClick');
        }
JS
        );
        $this->JS('ready')->add(
<<<JS
    jQuery(document).on('click', '#{$this->id} tbody tr', function (event) {
        LTS({$this->id}).rowclick(this);
    });
JS
        );
        return $this;    
    }

    public function compile() 
    {
        $head = empty($this->head) ? '[]' : json_encode($this->head);
        $rows = empty($this->rows) ? '[]' : json_encode($this->rows);
        $hidden = empty($this->hidden) ? '[]' : json_encode($this->hidden);

        $js = $this->compilemethod("ltsDataTable.add('{$this->id}',{$head},{$rows},{$hidden})");
        $this->compilemethod('head(data)',  
<<<JS
        if(data) ltsDataTable.heads['{$this->id}'] = data; 
        return ltsDataTable.heads['{$this->id}'];
JS
            , $js);
        $this->compilemethod('attr(data)', 
<<<JS
        if(data)
            ltsDataTable.hidden['{$this->id}'] = data; 
        return ltsDataTable.hidden['{$this->id}'];
JS
            , $js);
        $this->compilemethod('rows(data)',  
<<<JS
        if(data)
            ltsDataTable.rows['{$this->id}'] = ltsDataTable.deepCopyArray(data); 
        return ltsDataTable.rows['{$this->id}'];
JS
            , $js);
        $this->compilemethod('Row(id)',  
<<<JS
        return ltsDataTable.Row('{$this->id}', id);
JS
            , $js);
        $this->compilemethod('findRows(attr)', 
<<<JS
        return ltsDataTable.findRows('{$this->id}', attr);
JS
            , $js);
        $this->compilemethod('sort(name, napr)', 
<<<JS
        if(Array.isArray(name))
            ltsDataTable.sorted['{$this->id}'] = name;
        else
            ltsDataTable.sort('{$this->id}', name, napr);
JS
            , $js);
        $this->compilemethod('create(data)',  
<<<JS
        if(data)
            ltsDataTable.rows['{$this->id}'] = ltsDataTable.deepCopyArray(data); 
        else
            ltsDataTable.rows['{$this->id}'] = [];
        ltsDataTable.create('{$this->id}');
JS
            , $js);
        $this->compilemethod('values(attr, values)', 
<<<JS
        return ltsDataTable.values('{$this->id}', attr, values);
JS
            , $js);
        $this->compilemethod('clear(attr)',
<<<JS
        return ltsDataTable.clear('{$this->id}', attr);
JS
            , $js);
        $this->compilemethod('append(values)',
<<<JS
        ltsDataTable.append('{$this->id}', values);
JS
            , $js);
        $this->compilemethod('filter(values)',
<<<JS
        ltsDataTable.filterAndApply('{$this->id}', values);
JS
            , $js);
        $this->compilemethod('fieldvalues(field, selectedrows)',
<<<JS
        return ltsDataTable.fieldvalues('{$this->id}', field, selectedrows);
JS
            , $js);
        $this->compilemethod('out(f)',
<<<JS
        ltsDataTable.hucks['{$this->id}'] = f;
JS
            , $js);
        if(! empty($this->sorted))
            if(is_array($this->sorted))
            {
                $quoted = array_map(function($el) {
                    return "'" . $el . "'";
                }, $this->sorted);
                $arrsort = "[" . implode(", ", $quoted) . "]";
                $this->js('ready')->add("LTS({$this->id}).sort({$arrsort});");
            }
            else
                $this->js('ready')->add("LTS({$this->id}).sort(['{$this->sorted}']);");

        if(! empty($this->out))
            $this->js('ready')->add("LTS({$this->id}).out({$this->out});");

        $norows = $this->norows ? 'true' : 'false';
        $this->js('ready')->add("ltsDataTable.create('{$this->id}', {$norows})");

        parent::compile();
    } 
}
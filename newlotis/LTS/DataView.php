<?php
namespace LTS;

class DataView extends Grid
{
    public $table;	    // список
    public $element;    // окно просмотра и редактирования 
    public $filter;		// форма поиска и отбора в списке

    public $newbutton;      // кнопка создания нового
    public $delbutton;      // кнопка удаления
    public $filterbutton;   // кнопка открытия фильтра

    public $operdialog;     // диалог для запросов

    public $dbtable;        // таблица данных
    public $datarequest = null;    // функция чтения записей
    public $saverequest;    // функция сохранения записей
    public $deleterequest;  // функция удаления записей
    public $events;         // события

    public $noselectalert = 'Не выбрано ни одной строки в таблице. Операция не может быть выполнена!';
    public $delquery = 'Выбранные строки будут удалены. Вы уверены?';

    public $loadsubtablesrequest = null; // чтение подчиненых таблиц
    public $savesubtablesrequest = null; // запись подчиненных таблиц

    public $onsubtablesave = null;  // хак после сохранения ТЧ: onsubtablesave($args, $oldrows, $rows)

    public function __construct($id = '')
    {
        parent::__construct($id);

        $this->table = new DataTable($this->id . '_list');	
        $this->element = new Form($this->id . '_element');		       
        $this->filter = new FilterForm($this->id . '_filter');	
        
        $this->element->event('save', 
<<<JS
        if(typeof result == 'string')
            alert(result);
        else {
            if(result.result) {
                if(! result.data.sel)
                    result.data.sel = false;
                const ltsDataId = $({$this->element->id}).attr('ltsDataId');
                if(ltsDataId)
                    LTS({$this->table->id}).values({ltsDataId: ltsDataId}, result.data);
                else
                    LTS({$this->table->id}).append(result.data);
                LTS({$this->id}).signal('ElementSave');
                LTS({$this->id}).elementhide();            
            }
            else {
                if(result.code == 1)
                    window.location = 'login.php';
                else
                    if(result.code == 1000)
                        window.location = 'confirm.php';
                    else
                        alert(result.error);    
            }       
        }
JS
        )
            ->event('save', function ($args) {
                $ret = call_user_func($this->saverequest, $args);
                if($ret['result'] && array_key_exists('subtables', $args) && ! empty($this->savesubtablesrequest))
                {
                    $args['id'] = $ret['data']['id'];
                    call_user_func($this->savesubtablesrequest, $args);
                }
                if($ret['result'] && array_key_exists('subtables', $ret['data']))
                    unset($ret['data']['subtables']);
                return $ret;
            });

        $this->events = new Events();
        $this->events->client('delete(rows)',
<<<JS
    if(typeof result == 'string')
        alert(result);
    else 
    {
        if(Array.isArray(result)) {
            LTS({$this->table->id}).clear(result); 
            LTS({$this->table->id}).signal('RowsDel');
        }
        else
        if(result)
        {
            LTS({$this->table->id}).clear(LTS({$this->id}).selectedrows); 
            LTS({$this->table->id}).signal('RowsDel');
        }
        else
            alert('No delete rows!');
        LTS({$this->id}).selectedrows = null; 
    }
JS
        )
        ->server('delete(rows)', function ($args) { 
            $ret = call_user_func($this->deleterequest, $args['rows']); 
            return $ret;
        })
        ->client('loadsubtables(row)', 
<<<JS
    if(typeof result == 'object') 
        Object.entries(result).forEach(([id, data]) => { LTS.get(id).loadtable(data); LTS.get(id).mode('default'); });
    LTS({$this->id}).signal('LoadSubtables');
JS
            )
        ->server('loadsubtables', function ($args) {
            if($this->loadsubtablesrequest)
                return call_user_func($this->loadsubtablesrequest, $args['row']);
            return false;
        });

        $this->deleterequest = (function ($rows) {
            $ret = [];            
            $subtables = $this->get('subtables');
            $issubtables = is_array($subtables);
            foreach($rows as $row) {
                if($this->dbtable->del($row['id'])) 
                    $ret[] = ['ltsDataId' => $row['ltsDataId']]; 
                if($issubtables)
                    foreach($subtables as $subtable) {
                        $fieldmap = $subtable->get('subtableparam', 'fieldmap');
                        $conditions = $subtable->get('subtableparam', 'conditions');
                        foreach($fieldmap as $name => $parname) 
                            $conditions[$name] = ((array) $row)[$parname];   
                        $subtable->dbtable->delall($conditions);
                    }
            }
            return $ret;
        })->bindTo($this, $this);

        $this->saverequest = (function ($args) { 
            foreach($args as $name => $val)
                if($this->dbtable->field($name) !== null)
                    $this->dbtable->value($name, $val);

            $id = $args['id']; 
            if(empty($id)) { 
                $id = $this->dbtable->insert(); 
                $args['id'] = $id;
            }
            else 
                $this->dbtable->set($id);

            return ['result' => true, 'data' => $args];
        })->bindTo($this, $this);

        $this->operdialog = (new Dialog($this->id . '_operdialog'))
            ->capt('Внимание')
            ->modal()
            ->autoopen(false)
            ->add((new Html('p'))->setid($this->id . '_opermess'))
            ->button('OK', "if(LTS({$this->id}).ondialogok) {
                    LTS({$this->id}).ondialogok(); 
                    LTS({$this->id}).ondialogok = null; 
                }
                LTS({$this->id}_operdialog).close()")
            ->button('NO', "if(LTS({$this->id}).ondialogno) {
                    LTS({$this->id}).ondialogno(); 
                    LTS({$this->id}).ondialogno = null; 
                }
                LTS({$this->id}_operdialog).close()");
        $this->add($this->operdialog);

        $this->filterbutton = (new Button($this->id . '_filterbutton'))
            ->capt('Поиск')
            ->click(
<<<JS
    LTS({$this->id}).signal('CloseAll'); 
    const _grid = LTS({$this->id});
    if(_grid.filteropened)
        _grid.filterhide();
    else
        _grid.filtershow();
    _grid.filteropened = _grid.filteropened ? false : true;
JS
        );
        $this->newbutton = (new Button($this->id . '_newbutton'))
            ->capt('Добавить')
            ->click("LTS({$this->id}).signal('CloseAll');
            LTS({$this->element->id}).clear();
            $({$this->element->id}).attr('ltsDataId', '');
            LTS({$this->id}).clearsubtables();
            LTS({$this->id}).signal('ElementNew');
            LTS({$this->id}).elementshow();
        ");
        $this->delbutton = (new Button($this->id . '_delbutton'))
            ->capt('Удалить')
            ->click(
<<<JS
            LTS({$this->id}).signal('CloseAll');
            var vals = LTS({$this->table->id}).values({ sel: 1 });
            if(! vals.length)
                alert(LTS({$this->id}).noselectalert);
            else
            {
                LTS({$this->id}).selectedrows = vals;
                LTS({$this->id}).ondialogok = () => { 
                    LTS({$this->events->id}).delete(LTS({$this->id}).selectedrows);
                };
                LTS({$this->id}).ondialogno = () => LTS({$this->id}).selectedrows = null;
                LTS({$this->id}).dialogrequest(LTS({$this->id}).delquery);
                LTS({$this->operdialog->id}).open();
            }
JS
        );
    }

    public function _id($name) // для id объектов и сигналов
    {
        if(is_object($name))
            return $name->id;
        else
            return $this->id . '_' . $name;
    }

    public function setmodesdefault() {
        // === Режим по умолчанию (десктоп) ===
        $this->setMode('default')
            ->device('desktop')
                ->areas([
                    "header   header",
                    "menu     content",
                    "bar      bar"
                ])
                ->rows("60px 1fr auto")
                ->columns("200px 1fr")
            ->device('mobile')
                ->areas([
                    "header",
                    "content",
                    "bar"
                ])
                ->rows("60px 1fr auto")
                ->columns("1fr");

        // === Режим редактирования (элемент) ===
        $this->setMode('element')
            ->device('desktop')
                ->areas(["element"])
                ->rows("1fr")
                ->columns("1fr")
            ->device('mobile')
                ->areas(["element"])
                ->rows("1fr")
                ->columns("1fr");

        // === Режим фильтра ===
        $this->setMode('filter')
            ->device('desktop')
                ->areas([
                    "header   header",
                    "menu     filter",
                    "menu     content",
                    "bar      bar"
                ])
                ->rows("60px min-content 1fr auto")
                ->columns("200px 1fr")
            ->device('mobile')
                ->areas([
                    "header",
                    "filter",
                    "content",
                    "bar"
                ])
                ->rows("60px min-content 1fr auto")
                ->columns("1fr");

        return $this;
    }

    public function setmodessubtable() {
        $this->setMode('default')
            ->device('desktop')
                ->areas(["bar", "content"])
                ->rows("auto 1fr")
                ->columns("1fr")
            ->device('mobile')
                ->areas(["bar", "content"])
                ->rows("auto 1fr")
                ->columns("1fr");

        // === Режим редактирования (элемент) ===
        $this->setMode('element')
            ->device('desktop')
                ->areas(["element"])
                ->rows("1fr")
                ->columns("1fr")
            ->device('mobile')
                ->areas(["element"])
                ->rows("1fr")
                ->columns("1fr");

        // === Режим фильтра ===
        $this->setMode('filter')
            ->device('desktop')
                ->areas([
                    "bar",
                    "filter",
                    "content"
                ])
                ->rows("auto min-content 1fr")
                ->columns("1fr")
            ->device('mobile')
                ->areas([
                    "bar",
                    "filter",
                    "content"
                ])
                ->rows("auto min-content 1fr")
                ->columns("1fr");

        return $this;

    }

    public function dbtable(MySqlTable $dbtable)
    {
        $this->dbtable = $dbtable;
        return $this;
    }

    public function checksave($f) {
        $eventname = $this->element->eventname('save');
        if(is_string($f)) {
            if(strpos(trim($f), 'function') === false)
                $_f = "function (args) { {$f}; }";
            else
                $_f = $f;
            $this->element->getEvent()->js()->add("check{$eventname}", $_f);
        }
        if(is_callable($f))
            $this->element->getEvent()->add("check{$eventname}", $f->bindTo($this, $this));
        return $this;
    }

    public function beforesave($f) {
        $eventname = $this->element->eventname('save');
        if(is_string($f)) {
            if(strpos(trim($f), 'function') === false)
                $_f = "function (args) { {$f}; }";
            else
                $_f = $f;
            $this->element->getEvent()->js()->add("before{$eventname}", $_f);
        }
        if(is_callable($f))
            $this->element->getEvent()->add("before{$eventname}", $f->bindTo($this, $this));
        return $this;
    }

    public function onsave($f) {
        $eventname = $this->element->eventname('save');
        if(is_string($f)) {
            if(strpos(trim($f), 'function') === false)
                $_f = "function (result) { {$f}; }";
            else
                $_f = $f;
            $this->element->getEvent()->js()->add("on{$eventname}", $_f);
        }
        if(is_callable($f))
            $this->element->getEvent()->add("on{$eventname}", $f->bindTo($this, $this));
        return $this;
    }

    public function onsubtablesave($f) {
        $this->onsubtablesave = $f->bindTo($this, $this);
        return $this;
    }

    public function loadsubtablesrequest($f) {
        $this->loadsubtablesrequest = $f->bindTo($this, $this);
        return $this;
    }

    public function savesubtablesrequest($f) {
        $this->savesubtablesrequest = $f->bindTo($this, $this);
        return $this;
    }

    public function datarequest($f) {
        $this->datarequest = $f->bindTo($this, $this);
        return $this;
    }

    public function saverequest($f) {
        $this->saverequest = $f->bindTo($this, $this);
        return $this;
    }

    public function deleterequest($f) {
        $this->deleterequest = $f->bindTo($this, $this);
        return $this;
    }

    public function question($name, $quest, $onok, $onno)
    {
        $this->method($name, 
<<<JS
            function () {
                LTS({$this->id}).ondialogok = {$onok};
                LTS({$this->id}).ondialogno = {$onno};
                $({$this->id}_opermess).text(`{$quest}`);
                LTS({$this->operdialog->id}).open();
            }
JS
        );

        return $this;
    }

    public function createtable($head, callable $func)
    {
        $this->datarequest = $func;
        $all = call_user_func($func, []);
        foreach($all as $a)
            $a->sel = false;

        $this->table->head($head);
        $this->table->data($all);
        $this->table->norows = false;
        $this->table->sort(array_keys($head));

        return $this;
    }

    // fields, head, conditions, sort, inputs, cells, element, elementarea, norows, filter, filterarea, bararea, bar, content, contentarea, new, delete
    public function bindtodb($dbtable, $param = null)
    {
        $sort = null;
        $head = null;
        $arrcond = [];
        $norows = false;

        $this->dbtable = $dbtable;

        if($param !== null)
        {
            if(array_key_exists('head', $param))
                $head = $param['head'];
            if(array_key_exists('sort', $param))
                $sort = $param['sort'];
            if(array_key_exists('fields', $param))
            {
                $fields = $param['fields'];
                $arrcond['FIELDS'] = $fields;
                if($head === null)
                {
                    $parts = explode(',', $fields);
                    $head = [];
                    foreach ($parts as $part) {
                        // Обрезаем пробелы
                        $part = trim($part);

                        $pos = stripos($part, ' as ');
                        if ($pos !== false) 
                            // Берём часть после ' as '
                            $name = trim(substr($part, $pos + 4));
                        else
                            // Если 'as' нет, берём всё значение
                            $name = $part;

                        if($name != 'id') $head[$name] = $name;
                    }
                }
            }
            if(array_key_exists('conditions', $param))
            {
                $conditions = $param['conditions'];
                foreach($conditions as $name => $val)
                    $arrcond[$name] = $val;
            }
            if(array_key_exists('norows', $param))
                $norows = $param['norows'];
            if(array_key_exists('datarequest', $param) && is_callable($param['datarequest']))
                $this->datarequest = $param['datarequest'];
        }

        if($head === null)
        {
            $arr = array_keys($dbtable->fields);
            $index = array_search('id', $arr);
            unset($arr[$index]);
            $head = array_combine($arr, $arr);
        }
        $this->table->head($head);
        $this->table->attr('dataview', $this->id);
 
        if(! $norows) {
            if(empty($this->datarequest))
                $all = $dbtable->all($arrcond);
            else
                $all = call_user_func($this->datarequest, $arrcond);
            if($all !== false && count($all) > 0)
                foreach($all as $a)
                    $a->sel = false;
            $this->table->data($all);
        }

        $this->table->norows = $norows;
        if($sort !== null)
            $this->table->sort($sort);

        if($param !== null) {
            if(array_key_exists('element', $param))
            {
                $this->element = $param['element'];
                if($this->element) {
                    $elementarea = array_key_exists('elementarea', $param) ? $param['elementarea'] : 'element';
                    $this->area($elementarea)->add($this->element);
                }
                else
                    $this->newbutton = null;
            }
            else
            if($this->element)
            {
                $elementarea = array_key_exists('elementarea', $param) ? $param['elementarea'] : 'element';
                if(array_key_exists('inputs', $param)) 
                {
                    $inputs = $param['inputs'];
                    $this->elementgenerate($elementarea, $inputs);
                }
                else
                {
                    $inputs = $dbtable->generateinputs();
                    $this->elementgenerate($elementarea, $inputs);
                }
                if(array_key_exists('cells', $param)) 
                    $this->elementcells($param['cells']);
                else
                {
                    $_names = [];
                    foreach($inputs as $inp)
                        $_names[] = $inp['name'];
                    $cellsnames = implode(',', $_names);
                    $this->elementcells($cellsnames);
                }
            }
            else
            {
                $this->element = null;
                $this->newbutton = null;
            }

            if(array_key_exists('filter', $param))
            {
                $filterarr = $param['filter'];
                if($filterarr) {
                    $filterarea = array_key_exists('filterarea', $param) ? $param['filterarea'] : 'filter';
                    $this->filterassign($filterarea, $filterarr);
                    }
                else
                    $this->filterbutton = null;
            }
            else
            {
                $this->filter = null;
                $this->filterbutton = null;
            }

            $this->content(array_key_exists('contentarea', $param) ? $param['contentarea'] : 'content');

            $bararea = array_key_exists('bararea', $param) ? $param['bararea'] : 'bar';

            if(array_key_exists('new', $param)) {
                $this->newbutton = $param['new'];
            }
            if(array_key_exists('delete', $param)) {
                $this->delbutton = $param['delete'];
                if(empty($this->delbutton))
                {
                    $this->events->client('delete(rows)', false);
                    $this->events->server('delete(rows)', false);
                }
            }

            if(array_key_exists('bar', $param)) {
                $bar = $param['bar'];
                if($bar)
                    $this->bar($bararea, ...$bar);
            }
            else
                $this->bar($bararea);
        }
        return $this;
    }

    public function subtable($name = '', $dbtable = null, $param = null) {
        $subtable = new DataView($name);

        $subtableparam = ['fieldmap' => ["parent_{$this->dbtable->name}" => 'id'], 'fields' => [], 'conditions' => []];
        if(is_array($param))
        {
            $param['norows'] = true;
            if($dbtable !== null)
                $subtable->bindtodb($dbtable, $param);

            if(array_key_exists('fields', $param))
                $subtableparam['fields'] = $param['fields'];
            if(array_key_exists('conditions', $param))
                $subtableparam['conditions'] = $param['conditions'];
            if(array_key_exists('fieldmap', $param))
                $subtableparam['fieldmap'] = $param['fieldmap'];
            if(array_key_exists('area', $param)) {
                $subtablearea = $param['area'];
                $this->area($subtablearea)->add($subtable);
            }
        }
        else {
            if($dbtable !== null)
                $subtable->bindtodb($dbtable);
            $subtable->table->norows = true;
        }

        foreach($subtableparam as $key => $val)
            $subtable->set('subtableparam', $key, $val);

        $this->set('subtables', $subtable->id, $subtable);

        if($this->loadsubtablesrequest === null)
            $this->loadsubtablesrequest = (function ($parentobj = null) {
                $ret = [];

                $subtables = $this->get('subtables');
                if(is_array($subtables))
                    foreach($subtables as $subtablename => $subtable) {
                        if(empty($parentobj))
                            $data = [];
                        else {
                            $fields = $subtable->get('subtableparam', 'fields');
                            $fieldmap = $subtable->get('subtableparam', 'fieldmap');
                            $conditions = $subtable->get('subtableparam', 'conditions');
                            if($fields)
                                $conditions['FIELDS'] = $fields; 
                            foreach($fieldmap as $name => $parname) 
                                $conditions[$name] = ((array) $parentobj)[$parname];
                            if(empty($subtable->datarequest))   
                                $data = $subtable->dbtable->all($conditions); 
                            else
                                $data = call_user_func($subtable->datarequest, $conditions);
                            if($data === false)
                                $data = [];
                        }
                        $ret[$subtablename] = $data;
                    }
                
                return $ret;
            })->bindTo($this, $this);

        if($this->savesubtablesrequest === null)
            $this->savesubtablesrequest = (function ($args) {
                $subtables = $this->get('subtables');
                if(is_array($subtables))
                    foreach($subtables as $subtable) 
                        if(! empty($subtable->element))
                            $subtable->element->getEvent()->eventjoin('save', $subtable->saverequest, $args);
                return true;
            })->bindTo($this, $this);

        if($subtable->element) {
            $subtable->element->event('save', false);

            $subtable->method('rowsave(values)',
<<<JS
        const ltsDataId = $({$subtable->element->id}).attr('ltsDataId');
        values.sel = false;
        if(ltsDataId)
            LTS({$subtable->table->id}).values({ltsDataId: ltsDataId}, values);
        else
            LTS({$subtable->table->id}).append(values);
        LTS({$subtable->id}).signal('ElementSave');
        LTS({$subtable->id}).elementhide();            
JS
            );
            $subtable->js('ready')->add(
<<<JS
        jQuery(document).on('click', '#{$subtable->element->id} button[name="save"]', function () {
            const values = LTS({$subtable->element->id}).values();
            LTS({$subtable->id}).rowsave(values);
        });
JS
            );
        }
        $subtable->events->client('delete(rows)', false);
        $subtable->events->server('delete(rows)', false);
        $subtable->method('rowsdelete(rows)', "LTS({$subtable->table->id}).clear(rows); LTS({$subtable->table->id}).signal('RowsDel')");
        if(! empty($subtable->delbutton)) {
            $subtable->delbutton->click(
<<<JS
            LTS({$subtable->id}).signal('CloseAll');
            const vals = LTS({$subtable->table->id}).values({ sel: 1 });
            if(! vals.length)
                alert(LTS({$subtable->id}).noselectalert);
            else
            {
                LTS({$subtable->id}).selectedrows = vals;
                LTS({$subtable->id}).ondialogok = () => { 
                    LTS({$subtable->id}).rowsdelete(LTS({$subtable->id}).selectedrows);
                };
                LTS({$subtable->id}).ondialogno = () => LTS({$subtable->id}).selectedrows = null;
                LTS({$subtable->id}).dialogrequest(LTS({$subtable->id}).delquery);
                LTS({$subtable->operdialog->id}).open();
            }
JS
            );
        }

        $subtable->saverequest(function ($args) {
            $fields = $this->get('subtableparam', 'fields');
            $fieldmap = $this->get('subtableparam', 'fieldmap');
            $conditions = $this->get('subtableparam', 'conditions');
            if($fields)
                $conditions['FIELDS'] = $fields;
            foreach($fieldmap as $name => $parname)
                $conditions[$name] = $args[$parname];   
            
            $rows = array_key_exists($this->id, $args['subtables']) ? $args['subtables'][$this->id] : [];

            $myids = count($rows) > 0 ? array_column($rows, 'id') : [];
            $oldrows = $this->dbtable->all($conditions);
            if($oldrows !== false && count($oldrows) > 0) {
                $oldids = array_column($oldrows, 'id');
                $delids = array_values(array_diff($oldids, $myids));
                if(count($delids) > 0)
                    $this->dbtable->delall(['id' => $delids]);
            }
            else
                $oldrows = [];

            foreach($rows as $row)
            {
                $rowid = empty($row['id']) ? null : $row['id'];
                foreach($row as $key => $value) 
                    if($this->dbtable->field($key) !== null)
                        $this->dbtable->value($key, $value);
                foreach($fieldmap as $name => $parname)
                    if($this->dbtable->field($name) !== null)
                        $this->dbtable->value($name, $args[$parname]);   
                $this->dbtable->set($rowid);
            }

            if($this->onsubtablesave !== null && is_callable($this->onsubtablesave))
                call_user_func($this->onsubtablesave, $args, $oldrows, $rows);
            
            return true;
        });

        return $subtable;
    }

    public function bar($areaName, ...$extraButtons)
    {
        // Размещаем стандартные кнопки
        $area = $this->area($areaName);
        $area->addmany($this->filterbutton, $this->newbutton, $this->delbutton);
        // Добавляем дополнительные
        if($extraButtons)
            foreach ($extraButtons as $btn) 
                $area->add($btn);

        return $this;
    }

    public function content($areaName)
    {
        // Просто размещаем таблицу
        $this->area($areaName)->add($this->table);
        return $this;
    }

    public function elementgenerate($areaName, $arr)
    {
        $this->element->generate($arr);
        $this->area($areaName)->add($this->element); 
        return $this;
    }

    public function elementcells($arr)
    {
        return $this->element->cells($arr);
    }

    public function filterassign($areaName, $arr)
    {
        $this->filter->assign($this->table, $arr);
        $this->area($areaName)->add($this->filter);
        return $this;
    }

    public function compile()
    {
        if($this->filter)
        {
            $this->method('filtershow', 
<<<JS
                LTS({$this->id}).mode('filter');
                LTS({$this->id}).signal('FilterShow');
JS
            );
            $this->method('filterhide', 
<<<JS
                LTS({$this->id}).signal('FilterHide');
                LTS({$this->id}).mode('default');
JS
            );
        }
        if($this->element)
        {
            $this->method('elementshow', 
<<<JS
                LTS({$this->id}).mode('element');
                LTS({$this->id}).signal('ElementShow');
JS
            );
            $this->method('elementhide', 
<<<JS
                LTS({$this->id}).signal('ElementHide');
                LTS({$this->id}).mode('default');
JS
            );
            $_childs = $this->element->getchilds('html', ['tagname' => 'button', 'name' => 'close']); 
            if(count($_childs) > 0) 
            {
                $_button = $_childs[0]; 
                $_button->click("LTS.get('{$this->id}').elementhide()");
            }
            if($this->table) {
                $this->table->rowclick($this->element);
                $this->signal('RowClick',
<<<JS
    const ltsDataId = $({$this->element->id}).attr('ltsDataId');
    LTS({$this->events->id}).loadsubtables(LTS({$this->table->id}).values({ltsDataId: ltsDataId})[0]);
    LTS({$this->id}).signal('ElementLoad');
    LTS({$this->id}).elementshow();
JS
                , $this->table);
            }
        }

        $clearjs = '';
        $subtables = $this->get('subtables');
        if(is_array($subtables)) {
            $subtablesjs = 'let subtables = {};';
            foreach($subtables as $subtableid => $subtable) {
                $js = $subtable->compilemethod('loadtable(data)', "LTS.get('{$subtable->table->id}').create(data)");
                $subtable->compilemethod('cleartable()', "LTS.get('{$subtable->table->id}').clear()", $js);
                $subtable->compilemethod('values(values)', "return LTS.get('{$subtable->table->id}').values(values)", $js);
                $clearjs .= "
                    LTS.get('{$subtableid}').cleartable(); LTS.get('{$subtableid}').mode('default');";
                $subtablesjs .= "
                    subtables.{$subtableid} = LTS({$subtableid}).values();";
            }
            $subtablesjs .= "
                args.set('subtables', '<JSON>' + JSON.stringify(subtables));";
            $this->beforesave("function (args) { {$subtablesjs} return args; }");
        }
        $js = $this->compilemethod('clearsubtables', $clearjs);
        $this->compilemethod('dialogrequest(mes)', "jQuery('#{$this->id}_opermess').text(mes)", $js);
        $this->compilemethod(
<<<JS
    LTS.get('{$this->id}').delquery = `{$this->delquery}`;
    LTS.get('{$this->id}').noselectalert = `{$this->noselectalert}`;
JS
, null, $js);

        if(! empty($this->table) || is_array($subtables))
            $this->add($this->events);
        
        parent::compile();
    }
}
?>
<?php
namespace LTS;

class LookupField extends Div
{
    public $dbtable;                // MySqlTable
    public $searchfield = 'name';   // По какому полю искать в запросе, может быть таблица.имя
    public $tablefield = 'name';    // Псевдоним в таблице
    public $conditions = [];        // Доп. условия
    public $events;                 // Внутренние события
    public $datarequest;            // Кастомная функция загрузки данных

    public $input;                  // Поле ввода
    public $dropdown;               // Выпадающий блок
    public $table;                  // Таблица результатов

    public $dropdownenable = true;  // Будем ли использовать выпадающий блок
    public $binddatatable = true;   // Привязывать DataTable к Div
    public $fieldmap = null;        // Сопоставление полей в DataTable с полями в методе values. { datatable_name: values_name, ... }

    // Автоподкачка
    public $autoupload = true;      // Разрешена подкачка
    public $system = null;          // Условия внутренних функций, в частности, для LIMIT
    public $limit = 100;            // Кол-во записей для одновременного чтения
    public $vars;                   // Переменные для сохранения состояния
    public $wrapper = null;         // Элемент, относительно которого отслеживается появление последней строки 

    public function __construct($id = '')
    {
        parent::__construct($id);

        $this->addclass('lookup-field');

        $this->input = new Input("{$this->id}_input"); 
        //$this->input->id = "{$this->id}_input";
        $this->input->attr('lookupfield', $this->id)
                    ->attr('autocomplete', 'off');

        $this->dropdown = (new Div("{$this->id}_dropdown"))->addclass('lookup-dropdown');
        $this->dropdown->css()->add('display', 'none');

        $this->wrapper = $this->dropdown;

        $this->table = new DataTable("{$this->id}_results");

        $this->events = new Events();
        $this->method('search(term)', "LTS({$this->events->id}).search(term)");
        $this->method('value(term)', "return LTS({$this->input->id}).value(term)");

        $this->vars = new Vars("ltsSync_{$this->id}");

        // === Устанавливаем дефолтную функцию поиска ===
        $this->datarequest(function ($term) { 

            $query = $this->dbtable->query(); 
            $query->where($this->searchfield, "%%{$term}%");
            $this->applycondition($query, $this->conditions);
            $this->applycondition($query, $this->system);
            $this->system = null;

            $ret =  $query->all(); 
            
            return $ret == false || count($ret) == 0 ? [] : $ret;
        });

        $this->addmany($this->events, $this->input);
    }

    // Применить условия к query
    public function applycondition($query, $cond) {
        if(! empty($cond))
            foreach ($cond as $key => $value) {
                if (empty($value)) continue;

                switch (strtoupper($key)) {
                    case 'FIELDS':
                        $query->select($value);
                        break;
                    case 'WHERE':
                        $query->where($value);
                        break;
                    case 'ORDER':
                        $query->orderBy($value);
                        break;
                    case 'LIMIT':
                        $query->limit($value); 
                        break;
                    case 'GROUP':
                        $query->groupBy($value);
                        break;
                    default:
                        $query->addCondition($key, $value, 'AND');
            }
        }
        return $this;
    }

    // --- Настройка ---

    public function dbtable($dbtable)
    {
        $this->dbtable = $dbtable;
        return $this;
    }

    public function label($caption = null)
    {
        $childs = $this->get('childs');
        foreach($childs as $child)
            if($child->type == 'html' && $child->tagname == 'label')
            {
                if($caption !== null)
                    $child->caption = $caption ? $caption : '';

                return $child;
            }

        if($caption !== null)
        {
            $label = new Html('label');
            $label->attr('for', $this->input->id);
            $label->caption = $caption;
            $this->add($label);
            return $label;
        }

        return false;        
    }

    public function head($arr)
    {
        $this->table->head($arr);
        return $this;
    }

    public function searchfield($field) // может быть таблица.имя as имя
    {
        $f = preg_replace('/\s+AS\s+/i', ' as ', $field);
        $pos = strrpos($f, ' as ');

        if (strpos($f, '.') !== false) {
            $this->tablefield = trim(substr($f, $pos + 4));
            $this->searchfield = trim(substr($f, 0, $pos));
        }
        else
        {
            $this->searchfield = $field;
            $this->tablefield = $field;
        }

        return $this;
    }

    public function condition($key, $value = null)
    {
        if($value === null && is_array($key))
            foreach($key as $_key => $_value)
                $this->conditions[$_key] = $_value;
        else
            $this->conditions[$key] = $value;
        return $this;
    }

    public function datarequest($f)
    {
        $this->datarequest = $f->bindTo($this, $this);
        return $this;
    }

    public function search($term)
    {
        return call_user_func($this->datarequest, $term);
    }

    public function fieldmap($prot)
    {
        $this->fieldmap = $prot;
        return $this;
    }

    // Элемент, относительно которого отслеживаем появление последней строки
    public function wrapper($el)
    {
        $this->wrapper = $el;
        return $this;
    }

    // Сбросить счетчик записей для автоподгрузчика
    public function reset() {
        $this->vars->set('begin', 0)
            ->set('next', 1)
            ->store();
        return $this;
    }

    // Получить очередной массив записей
    public function upload()
    {
        $begin = $this->vars->get('begin');
        $next = $this->vars->get('next');
        $term = $this->vars->get('term');
        if($next === 0)
            return [];
        if($begin === null)
            $begin = 0;
        $this->system = ['LIMIT' => "{$begin}, {$this->limit}"];
        $all = call_user_func($this->datarequest, $term); 
        $next = 0;
        if($all !== false && count($all) > 0)
            $next = count($all) == $this->limit ? 1 : 0;
        $this->vars->set('next', $next);
        if($next == 1)
            $this->vars->set('begin', $begin + $this->limit);
        $this->vars->store(); 
        return $all;
    }
    // --- Компиляция ---

    public function compile()
    {
        $id = $this->id;
        $inputId = $this->input->id;
        $dtId = $this->table->id;
        $drdId = $this->dropdown->id;

        $dropdownshow = ';';
        $dropdownhide = ';';

        if($this->hasclass('filterfield')) 
            $this->removeclass('filterfield')->input->addclass('filterfield');

        if($this->dropdownenable)
        {
            $this->add($this->dropdown->add($this->table));
            $dropdownshow = "jQuery('#{$drdId}').show();";
            $dropdownhide = "jQuery('#{$drdId}').hide();";
        }
        elseif($this->$binddatatable)
            $this->add($this->table);

        //$this->events->client('search(term)', 'alert(result)');
        $this->events->server('search', function ($args) { 
            if($this->autoupload)
            {
                $this->vars->set('term', $args['term']);
                $data = $this->upload();
            }
            else
                $data = $this->search($args['term']); 
            return ['result' => true, 'data' => $data ]; });

        $uploadtable = ! $this->autoupload ? '' :
<<<JS
            if(result.data.length == {$this->limit}) {
                const lastid = ltsDataTable.lastdataid('{$dtId}');
                if(lastid)
                    LTS({$this->id}).observe(LTS({$dtId}).Row(lastid));
            }
JS
;

        // === Клиентское событие: обработка результата ===
        $this->events->client('search(term)',
<<<JS
            if(typeof result === 'string')
            {
                {$dropdownhide}
                alert(result);
            }
            else
            if(typeof result === 'object')
                if (result.result) {
                    if (result.data.length > 0)
                    {
                        LTS({$dtId}).create(result.data);
                        {$uploadtable}
                        {$dropdownshow}
                    }
                    else 
                    {
                        LTS({$dtId}).clear();
                        {$dropdownhide}
                    }
                }
                else
                {
                    {$dropdownhide};
                    alert(result.error);
                }
JS
        );

        // === Обработчик клика по строке таблицы ===
        $jsready = new JS('ready');
        $jsready->compile = false;
        $this->add($jsready);

        if($this->autoupload)
        {
            // Добавляем Vars
            $this->add($this->vars);
            // Events
            $this->events->client('upload', 
<<<JS
                if(result)
                {
                    LTS({$dtId}).append(result);
                    {$uploadtable}
                }
JS
            )->server('upload', function ($args) {
                $all = $this->upload();
                if($all == false || count($all) == 0)
                    return false;
                return $all;
            });

            // Client
            $wrapperstr = '';
            if(! empty($this->wrapper)) {
                $_id = null;
                if(is_string($this->wrapper))
                    $_id = $this->wrapper;
                elseif(is_object($this->wrapper) && property_exists($this->wrapper, 'id'))
                    $_id = $this->wrapper->id;
                if(! empty($_id))
                    $wrapperstr = ", { root: document.getElementById('{$_id}') }";
            }

            $js = $this->compilemethod(
<<<JS
                LTS.get('{$id}').searchfield = '{$this->tablefield}';
                LTS.get('{$id}').observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            LTS.get('{$id}').unobserve(entry.target);
                            LTS.get('{$this->events->id}').upload();
                        }
                    });
                }{$wrapperstr});
JS
                );
            // Сбросить переменные для нового upload
            $this->compilemethod('reset', 
<<<JS
                let vars = LTS.vars('ltsSync_{$id}');
                vars.set('begin', 0).set('next', 1);
                this.unobserve();
                return this;
JS
                , $js);
            // Начать мониторить jQuery объект (строку таблицы)
            $this->compilemethod('observe(row)', 
<<<JS
                let elem;
                if(row instanceof jQuery)
                    elem = row[0];
                else
                    if(typeof row == 'string')
                        elem = document.getElementById(row);
                    else
                        if(row instanceof Element)
                            elem = row;
                if(elem) {
                    this.observer.observe(elem); 
                    this.observerow = elem; 
                }
                return this; 
JS
                , $js);
            // Завершить мониторить объект
            $this->compilemethod('unobserve(row)', 
<<<JS
                if(row) {
                    let elem;
                    if(row instanceof jQuery)
                        elem = row[0];
                    else
                        if(typeof row == 'string')
                            elem = document.getElementById(row);
                        else
                            if(row instanceof Element)
                                elem = row;
                    if(elem) {
                        this.observer.unobserve(row); 
                        if(elem == this.observerow)
                            this.observerow = null; 
                    }
                }
                else
                if(this.observerow) {
                    this.observer.unobserve(this.observerow); 
                    this.observerow = null; 
                }
                return this; 
JS
                , $js);
            $this->compilemethod('select(row)', "if(row) this.selected = row; else this.selected = null", $js);
            $this->compilemethod('clear()', 
<<<JS
            this.selected = null;
            jQuery('#{$inputId}').val('');
            LTS.get('{$dtId}').clear();
            LTS.get('{$id}').reset();
            {$dropdownhide}; 
JS
            , $js);

            $jsready->add("LTS.get('{$id}').reset()");

            $startsearch = "LTS.get('{$id}').reset(); LTS.get('{$id}').search(term);";
            $endsearch = "LTS.get('{$id}').unobserve();";

            $jsready->add(
<<<JS
            jQuery('#{$inputId}').on('focus', function () {
                if(LTS.get('{$id}').firstloaded)
                    return;
                const term = jQuery(this).val();
                LTS.get('{$id}').firstloaded = true;
                {$startsearch}
            });
JS
            );
        }
        else {
            $startsearch = 
<<<JS
if (term.length > 2)
    LTS.get('{$id}').search(term);
else {
    LTS.get('{$dtId}').clear();
    {$dropdownhide}
}
JS
            ;
            $endsearch = '';
        }

        if($this->fieldmap !== null)
            $jsready->add("LTS.get('{$this->id}').fieldmap = " . json_encode($this->fieldmap) . ';');
        $jsready->add(
<<<JS
            jQuery(document).on('click', '#{$dtId} tbody tr', function () {
                {$endsearch}
                const id = jQuery(this).data('id');
                const row = LTS.get('{$dtId}').values({ ltsDataId: id })[0];
                LTS.get('{$id}').select(row);
                LTS.get('{$id}').firstloaded = false;
                {$dropdownhide}
                jQuery('#{$inputId}').val(row['{$this->tablefield}']).trigger('change');               
                LTS.signal('{$id}' + '_Selected');
            });
            
            jQuery('#{$inputId}').on('input', function () {
                const term = jQuery(this).val();
                LTS.get('{$id}').firstloaded = term.lenght != 0;
                {$startsearch}
            });
            
            jQuery(document).on('click', function (e) {
                if (!jQuery(e.target).closest('#{$id}').length)
                {
                    LTS.get('{$id}').firstloaded = false;
                    {$endsearch}
                    {$dropdownhide}
                    const selectedrow = LTS.get('{$id}').selected;
                    if(jQuery('#{$inputId}').val() == '')
                        LTS.get('{$id}').select(null);
                    else 
                        if(selectedrow) 
                            jQuery('#{$inputId}').val(selectedrow['{$this->tablefield}']);
                        else
                            jQuery('#{$inputId}').val('');
                }
            });
JS
        );

        parent::compile();
    }
}
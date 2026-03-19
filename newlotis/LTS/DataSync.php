<?php
namespace LTS;

/*
Использование:
$datasync = LTS::DataSync('Sync', $maindiv)
    ->initmanager($base)    // второй параметр true при первом запуске для создания таблицы в баз данных
    ->dbtable($dbtable)
    ->datatable($datatable)
    ->wrapper($content);    // если надо явно указать обертку, относительно которой отслеживаем появлениевидимость строк datatable 

$datasync->recidname = 'code';  // если в datatable id записи хранится в поле code
$datasync->changeregime = 0;    // если предполагается самостоятельная обработка изменений на клиенте
                                // пришедшие строки можно получить LTS(object).changet и LTS(object).deleted
                                // по сигналу: id_Change, где id - id объекта

$datasync->autochange();        // если требуется отслеживать изменения
$datasync->autoupload();        // если требуется подкачка в реальном времени

$datasync->condition($key, $value);
$datasync->condition([...]);    // установить серверные условия отбора и сортировки записей dbtable

// Если требу    ется ручная обработка на сервере
$datasync->reset();             // сбросить счетчики прочитанных записей (для автоматической автоподкачки)
$datasync->upload();            // получить очередной пул записей. вернет: [{...}, {...}, ...]] или false
$datasynk->getchange();         // получить список изменений. 
                                // вернет: [ 'result' => true/false, 'changed' => [{...}, {...}, ...], 'deleted' => [id1, id2, ...]]
$datasync->fixchange($arr, $type);    // Зафиксировать изменение записей. arr - массив id записей, type - 1:измененные 0:удаленные

// Ручная обработка изменений таблицы. Требуется при установленном отборе или сортировке
$datatable->signal('Sync_Change', 
<<<JS
function () {
    let changedrows = LTS(datasync).changed;    // [{...}, {...}, ...] -  строки для DataTable
    let deletedids = LTS(datasync).deleted;     // [id1, id2, ...] - id-ы удаленных записей
    ...
    LTS(datatable).filter([...]);      // Применение фильтра к вновь добавленным строкам
    LTS(datatable).sort(name, napr);    // Сортировка
}
JS
);
$datatable->signal('Sync_Upload', "function () { ... }"); // Событие на получение очередного пула данных

// На клиенте:

LTS(datasync).condition([...]);             // Установить локальные условия отбора и сортировки записей dbtable для автоподкачки
LTS(datasync).fixchange(changed, deleted)   // Передать данные об id измененных и удаленных записей [id1, id2, ...]
LTS(datasync).reset([...]);     // Очистить таблицу, установить новые условия отбора и сортировки и загрузить первый пул данных
LTS(datasync).upload();         // Запустить вручную добавление в datatable очередного пула данных  
*/

class DataSync extends Quark {
    public $datatable;          // DataTable
    public $dbtable;            // MySqlTable
    public $conditions = [];    // Условия, установленные на сервере
    public $usersconditions = null;   // Условия пользователя
    public $system = null;      // Условия внутренних функций, в частности, для LIMIT
    public $datarequest;           // Функция ввода
    public $limit = 100;        // Кол-во записей для одновременного чтения
    public $ltstablesmanager;   // Таблица - менеджер изменений в базе данных
    public $events;             // События
    public $vars;               // Переменные для сохранения состояния
    public $timer = 5000;       // Как часто происходит запрос обновления
    public $wrapper = null;     // Элемент, относительно которого отслеживается появление последней строки 
    public $autochange = false; // разрешено отслеживание изменений
    public $autoupload = false; // разрешена подкачка
    public $recidname = 'id';   // поле, в котором хранится в таблице id
    public $changeregime = 1;   // режим обнвления измененных записей: 
                                // 1 - автоматическое 0 - не обновлять, 
                                // помещать в LTS(this).changed и LTS(this).deleted

    public function __construct($id = '') {
        parent::__construct($id);

        $this->type = 'DataSync';

        // Переопределяемая функция чтения записей по массиву ids
        // В возвращаемых записях id должен быть обязательно
        $this->datarequest = function ($ids = null) {
            if(empty($this->dbtable)) 
                return false; 

            $query = $this->dbtable->query();
            if(! empty($ids))
                $this->applycondition($query, ['id' => $ids]);
            $this->applycondition($query, $this->conditions)
                ->applycondition($query, $this->usersconditions)
                ->applycondition($query, $this->system);

            $this->usersconditions = null;
            $this->system = null;

            $all = $query->all();

            return $all;
        };

        $this->vars = new Vars("ltsSync_{$this->id}");
        $client = $this->vars->get('client');
        if(empty($client))
            $this->vars->value('client', bin2hex(random_bytes(16)))->store();

        $this->events = new Events("{$this->id}_Events");
        $this->addmany($this->events, $this->vars);
    }

    public function autochange($reg = true) {
        $this->autochange = $reg;
        return $this;
    }

    public function autoupload($reg = true) {
        $this->autoupload = $reg;
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

    // Применить условия к query
    public function applycondition($query, $cond) {
        if(! empty($cond) && is_array($cond))
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

    // Привязать MySqlTable
    public function dbtable($dbtable)
    {
        $this->dbtable = $dbtable;
        return $this;
    }

    // Привязать DataTable
    public function datatable($datatable)
    {
        $this->datatable = $datatable;
        return $this;
    }

    // Элемент, относительно которого отслеживаем появление последней строки
    public function wrapper($el)
    {
        $this->wrapper = $el;
        return $this;
    }

    // Переопределить datfunc
    public function datarequest($f)
    {
        $this->datarequest = $f->bindTo($this, $this);
        return $this;
    }

    // Инициализация (и создание) таблицы менеджера
    public function initmanager($base, $create = false) {
        $this->ltstablesmanager = $base->table('ltstablesmanager')
            ->string('name', 100)   // Имя таблицы данных
            ->int('recid')          // id измененной записи
            ->string('client', 16)  // Автор изменений
            ->date('created')       // Датавремя изменений
            ->int('type');          // Тип операции: 0 - удалено, 1 - изменено/добавлено
        if($create)
            $this->ltstablesmanager->create();
        return $this;
    }

    // Зафиксировать изменения записей
    public function fixchange($arr, $type = 1) { // arr - массив индексов или единичный индекс измененных записей
        if(empty($this->ltstablesmanager))
        {
            $this->valueError('fixchange', 'ltstablesmanager', 'Object is not inizialized!');
            return $this;
        }
        if(! is_array($arr))
            $_arr = [$arr];
        else
            $_arr = $arr;

        $client = $this->vars->get('client');
        foreach($_arr as $id)
            $this->ltstablesmanager
                ->value('name', $this->dbtable->name)
                ->value('recid', $id)
                ->value('client', $client)
                ->value('created', date('Y-m-d H:i:s'))
                ->value('type', $type)
                ->insert();
        return $this;
    }

    // Очищаем менеджер от всех устаревших записей
    public function checktime() {
        $check = $this->vars->get('checktime');
        $currentTime = time();
        if(! empty($check))
        {
            $storedTimestamp = strtotime($check);
            if($currentTime < $check)
                return;
        }

        $check = $currentTime - 120;
        $formattedTime = date('Y-m-d H:i:s', $check);

        $this->ltstablesmanager->delall('created', "<{$formattedTime}");

        $check = $currentTime + 180;
        $formattedTime = date('Y-m-d H:i:s', $check);
        $this->vars->set('checktime', $formattedTime)->store();
        return $this;
    }

    // Получить массив id измененных записей
    public function getchange() {
        if(empty($this->ltstablesmanager))
        {
            $this->valueError('getchange', 'ltstablesmanager', 'Object is not inizialized!');
            return ['result' => false];
        }

        $this->checktime();

        $client = $this->vars->get('client');
        $id = $this->vars->get('recid');
        if(empty($id))
            $id = 0;
        $res = false;

        $all = $this->ltstablesmanager->all([
                'name' => $this->dbtable->name, 
                'client' => "!{$client}",
                'id' => ">{$id}" 
            ]);

        $changed = [];
        $deletedids = [];
        if($all !== false && count($all) > 0)
        {
            $res = true;
            $ind = count($all) - 1;
            $id = $all[$ind]->id;
            $this->vars->set('recid', $id)->store();
            $deletedids = [];
            $changedids = [];
            foreach ($all as $row) {
                if ($row['type'] == 0) {
                    $deletedids[] = $row['recid'];
                } elseif ($row['type'] == 1) {
                    $changedids[] = $row['recid'];
                }
            }
            $deletedids = array_unique($deletedids);
            $changedids = array_unique($changedids);
            $changedids = array_diff($changedids, $deletedids);
            $changed = call_user_func($this->datarequest, $changedids);
        }

        return ['result' => $res, 'changed' => $changed, 'deleted' => $deletedids];
    }

    // Установить максимальный id (при начале работы)
    public function begchange() {
        if(empty($this->ltstablesmanager))
        {
            $this->valueError('getchange', 'ltstablesmanager', 'Object is not inizialized!');
            return ['result' => false];
        }
        $all = $this->ltstablesmanager->all([
                'FIELDS' => 'id', 
                'ORDER' => "-id" ,
                'LIMIT' => 1
            ]);

        if($all !== false && count($all) > 0)
            $id = $all[0]->id;
        else
            $id = 0;

        $this->vars->set('recid', $id)->store();

        return $this;
    }

    // Поставить или обновить блокировку на запись
    public function setlock($recid) {
        if(empty($this->ltstablesmanager))
        {
            $this->valueError('getchange', 'ltstablesmanager', 'Object is not inizialized!');
            return ['result' => false];
        }
        $client = $this->vars->get('client');

        $all = $this->ltstablesmanager->all([
                'FIELDS' => 'id, recid', 
                'name' => $this->dbtable->name,
                'recid' => $recid,
                'type' => 2,
                'LIMIT' => 1
            ]);

        if(is_array($recid)) {
            $ids = [];
            if($all !== false && count($all) > 0)
                foreach($all as $o)
                    $ids[$o->recid] = $o->id;

            foreach($recid as $rid) {
                if(array_key_exists($rid, $ids))
                    $id = $ids[$rid];
                else
                    $id = null;
                $this->ltstablesmanager
                    ->value('name', $this->dbtable->name)
                    ->value('recid', $rid)
                    ->value('client', $client)
                    ->value('created', date('Y-m-d H:i:s'))
                    ->value('type', 2)
                    ->set($id);
                }
        }
        else
        {
            $id = $all === false || count($all) == 0 ? $all[0]->id : null;
            $this->ltstablesmanager
                ->value('name', $this->dbtable->name)
                ->value('recid', $recid)
                ->value('client', $client)
                ->value('created', date('Y-m-d H:i:s'))
                ->value('type', 2)
                ->set($id);
        }

        return $this;
    }

    // Снять блокировку с записи
    public function removelock($recid) {
        if(empty($this->ltstablesmanager))
        {
            $this->valueError('getchange', 'ltstablesmanager', 'Object is not inizialized!');
            return ['result' => false];
        }
        $this->ltstablesmanager->delall([
            'name' => $this->dbtable->name,
            'recid' => $recid,
            'type' => 2
        ]);

        return $this;
    }

    // Проверить, заблокирована ли запись
    public function checklock($recid) {
        if(empty($this->ltstablesmanager))
        {
            $this->valueError('getchange', 'ltstablesmanager', 'Object is not inizialized!');
            return ['result' => false];
        }
        $all = $this->ltstablesmanager->all([
                'FIELDS' => 'id', 
                'name' => $this->dbtable->name,
                'recid' => $recid,
                'type' => 2,
                'LIMIT' => 1
            ]);

        return $all === false || count($all) == 0;
    }

    // Получить очередной массив записей
    public function upload()
    {
        $begin = $this->vars->get('begin');
        $next = $this->vars->get('next');
        if($next == 0)
            return ['result' => false];
        $this->system = ['LIMIT' => "{$begin}, {$this->limit}"];
        $all = call_user_func($this->datarequest, null); 
        $next = 0;
        if($all !== false && count($all) > 0)
            $next = count($all) == $this->limit ? 1 : 0;
        $this->vars->set('next', $next);
        if($next == 1)
            $this->vars->set('begin', $begin + $this->limit);
        $this->vars->store();
        return $all;
    }

    // Сбросить счетчик записей для автоподгрузчика
    public function reset() {
        $this->vars->set('begin', 0)
            ->set('next', 1)
            ->store();
        return $this;
    }

    public function compile()
    {
        $jsready = new JS('ready'); 
        $jsready->compile = false;
        $this->add($jsready);

        if($this->autoupload)
        {
            $this->events->client('upload(usersconditions, reset)', 
<<<JS
                if(result.result)
                {
                    LTS({$this->datatable->id}).append(result.data);
                    if(result.data.length == {$this->limit}) {
                        const lastid = ltsDataTable.lastdataid('{$this->datatable->id}');
                        if(lastid)
                            LTS({$this->id}).observe(LTS({$this->datatable->id}).Row(lastid));
                        LTS.signal('{$this->id}_Upload');
                    }
                }
                this.autochange = {$this->autochange};
JS
            )->server('upload', function ($args) {
                $usersconditions = $args['usersconditions']; 
                $reset = $args['reset'];
                if($reset && $reset !== 'undefined')
                    $this->reset();
                if(! empty($usersconditions) && $usersconditions !== 'undefined')
                    $this->usersconditions = $usersconditions;
                $all = $this->upload(); 
                $res = $all !== false && count($all) > 0;
                return ['result' => $res, 'data' => $all];
            });

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

            $jsready->add(
<<<JS
            _obj.observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            LTS.get('{$this->id}').unobserve(entry.target);
                            LTS.get('{$this->id}').upload(LTS.get('{$this->id}').usersconditions);
                        }
                    });
                }{$wrapperstr});
            LTS.get('{$this->id}').observe = function (row) { 
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
                    if(elem) 
                        this.observer.observe(elem); 
                }
                return this; 
            };
            LTS.get('{$this->id}').unobserve = function (row) {
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
                    if(elem) 
                        this.observer.unobserve(elem); 
                }
                return this;
            };
            LTS.get('{$this->id}').upload = function (usersconditions, reset) { 
                    LTS.get('{$this->events->id}').upload(usersconditions, reset); 
                return this; 
            };
            LTS.get('{$this->id}').reset = function (usersconditions) {
                this.autochange = false;
                LTS.get('{$this->datatable->id}').clear();
                if(usersconditions)
                    this.condition(usersconditions)
                this.upload(usersconditions, true); 
                return this;
            };
            (function () {
                const _vars = LTS.vars('ltsSync_{$this->id}');
                _vars.set('begin', 0);
                _vars.set('next', 1);
            })();
JS
            );
        }

        // Установить локальные условия на текущей странице: сортировка, отбор. Метод нужен для автоподгрузки
        $jsready->add("LTS.get('{$this->id}').condition = function (usersconditions) { this.usersconditions = usersconditions; return this; }");

        if($this->autochange) {
            $this->events->client('setlock(recid)', '')
                ->server('setlock(recid)', function ($args) { $this->setlock($args['recid']); return true; });
            $this->events->client('removelock(recid)', '')
                ->server('removelock(recid)', function ($args) { $this->removelock($args['recid']); return true; });
            $this->events->client('checklock(recid)', "LTS({$this->events}).__checklock = result")
                ->server('checklock(recid)', function ($args) { return $this->checklock($args['recid']); });
            
            $this->events->client('getchange()', 
                ($this->changeregime === 1 ?
<<<JS
                if(result.result)
                {
                    result.changed.forEach(str => {
                            const t = LTS({$this->datatable->id});
                            const strofid = t.filter({{$this->recidname} : str.id});
                            if(strofid.length)
                                t.values({{$this->recidname}: str.id}, str);
                            else
                                t.append(str);
                        });
                    result.deleted.forEach(id => LTS({$this->datatable->id}).clear({{$this->recidname} : id}));
                }
JS
                : '') .
<<<JS
                if(result.result)
                {
                    LTS({$this->id}).changed = result.changed;
                    LTS({$this->id}).deleted = result.deleted;
                    LTS.signal('{$this->id}_Change');
                }
JS
            )->server('getchange', function ($args) {
                return $this->getchange();
            });
            $this->events->client('fixchange(changed, deleted)', '')
            ->server('fixchange', function ($args) {
                $changed = $args['changed'];
                $deleted = $args['deleted'];
                if(! empty($changed))
                    $this->fixchange($changed, 1);
                if(! empty($deleted))
                    $this->fixchange($deleted, 0);
                return true;
            });

            $jsready->add(
<<<JS
            LTS.get('{$this->id}').fixchange = function (changed, deleted) { 
                LTS.get('{$this->events->id}').fixchange(changed, deleted); 
                return this; 
            };
            LTS.get('{$this->id}').setlock = function (recid) { 
                const _ev = LTS.get('{$this->events->id}');
                if(! _ev.__locks)
                    _ev.__locks = new Set();
                if(! _ev.__locks.has(recid))
                    _ev.__locks.add(recid);
                LTS.get('{$this->events->id}').setlock(recid); 
            };
            LTS.get('{$this->id}').removelock = function (recid) { 
                const _ev = LTS.get('{$this->events->id}');
                if(! _ev.__locks)
                    _ev.__locks = new Set();
                if(_ev.__locks.has(recid))
                    _ev.__locks.remove(recid);
                LTS.get('{$this->events->id}').removelock(recid); 
            };
            LTS.get('{$this->id}').checklock = function (recid) {
                LTS.get('{$this->events->id}').checklock(recid); 
                return LTS.get('{$this->events->id}').__checklock;
            };
            setInterval(() => { 
                    if(LTS.get('{$this->id}').autochange) {
                        LTS.get('{$this->events->id}').getchange();
                    }
                }, {$this->timer});
            setInterval(() => { 
                    if(LTS.get('{$this->id}').autochange) {
                        LTS.get('{$this->id}').setlock(LTS.get('{$this->events->id}').__locks);
                    }
                }, 60000); 
            LTS.get('{$this->id}').autochange = true;
JS
                );
            $this->begchange();
        }
        parent::compile();
    }
}
?>

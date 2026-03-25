<?php
// Подключаем LOTIS
define('UPPER_DIR', dirname(__DIR__) . '/newlotis/');
include_once UPPER_DIR . 'lotis.php';
// Описываем таблицы базы данных
$base = LTS::MySql('mybase', 'localhost', 'root', 'root');

$users = $base->table('users');
$users->string('name', 100);
$users->float('total');

$kassa = $base->table('kassa');
$kassa->date('date');

$kassatable = $base->table('kassatable');
$kassatable->parent($kassa);
$kassatable->table('user', $users);
$kassatable->string('message', 100);
$kassatable->float('pay');

$money = $base->table('money');
$money->int('doc');
$money->date('date');
$money->table('user', $users);
$money->float('pay');

// Создание базы данных: делается один раз
//$base->create();
//$users->create();
//$kassa->create();
//$kassatable->create();
//$money->create();
//$users->value('name', 'Bred Pitt')
//    ->value('total', 0)
//    ->insert();

// Создаем документ
$maindiv = LTS::DataView();

// Привязываем DataView к таблице kassa базы данных 
$maindiv->bindtodb($kassa, [
    // Колонки таблицы документов
    'head' => ['sel' => '', 'date' => 'Дата'],
    // Поля редактора шапки документа
    'inputs' => [
        ['name' => 'id',  'type' => 'hidden'],
        ['name' => 'date', 'type' => 'date', 'caption' => 'Дата'],
        ['name' => 'save', 'caption' => 'Записать', 'type' => 'button'],
        ['name' => 'close', 'caption' => 'Отмена', 'type' => 'button']
    ],
    // Группировка полей редактора
    'cells' => ['save, close', 'date'],
    // Поля окна отбора документов
    'filter' => [['name' => 'date', 'type' => 'date', 'caption' => 'Дата']],
    // Колонки, по которым можно производить сортировку таблицы документов
    'sort' => ['date as date']
]);

// Хак на вывод строки в таблицу документов
$maindiv->table->out(
<<<JS
    function (row, obj) { 
        // Если строка была отмечена
        row.find('td.Column_sel').text(obj.sel ? '✅' : '☐'); 
        // Форматируем вывод даты, отсекаем время
        row.find('td.Column_date').text(obj.date.substr(0, 10)); 
    }
JS
);

// Определяем табличную часть документа
$subtable = $maindiv->subtable('kassasubtable', $kassatable, [
    // Колонки табличной части документа
    'head' => [
        'sel' => '',
        'name' => 'Сотрудник',
        'pay' => 'Получено',
        'del' => ''
    ],
    // Явно задаем поля, которые будут читаться из kassatable
    'fields' => 'user, user.name as name, message, pay',
    // Область сетки, куда будет помещена табличная часть
    'area' => 'table',
    // Поля редактора строки табличной части документа
    'inputs' => [
        ['name' => 'ltsDataId', 'type' => 'hidden'],
        ['name' => 'user', 'type' => 'table', 'dbtable' => $users, 'caption' => 'Сотрудник'],
        ['name' => 'message', 'caption' => 'Назначение'],
        ['name' => 'pay', 'type' => 'numeric', 'caption' => 'Выдано'],
        ['name' => 'save', 'type' => 'button', 'caption' => 'Ок'],
        ['name' => 'close', 'type' => 'button', 'caption' => 'Отмена']
    ],
    // Группировка полей редактора строки
    'cells' => ['save, close', 'user', 'message', 'pay'],
    // Фильтр отключаем
    'filter' => null
]);

// Связь поля выбора сотрудника из базы данных со строкой табличной части
$userfield = $subtable->element->field('user');
$userfield->head(['name' => 'Сотрудник', 'total' => 'Получено всего']);
$userfield->fieldmap(['id' => 'user', 'name' => 'name']);

// Хак на вывод строки в табличную часть
$subtable->table->out(
<<<JS
function (row, obj) {
    row.find('td.Column_sel').text(obj.sel ? '✅' : '☐'); 
    row.find('td.Column_del').html('<input type="button" class="ltsRowDelbutton" value="x">');
} 
JS
);

// Проверки перед окончанием редактирования строки
$subtable->method('checkrowsave(values)',
<<<JS
    if(! LTS(userfield).selected) {
        alert('Не выбран сотрудник!');
        return false;
    }
    if(values.pay == 0) {
        alert('Сумма не должна равняться нулю!');
        return false;
    }
    values.name = LTS(userfield).selected.name; 
    return true;
JS
);

// Перезапись стоков данных при сохранении документа
$maindiv->onsave(function ($args, $result) {
    global $money, $users;
    
    if(! $result['result'])
        return $result;

    // Получаем строки табличной части
    $paytable = $args['subtables']['kassasubtable'];
    // Получаем дату документа
    $date = $args['date'];
    // Добавляем дату в каждую строку
    $paytable = array_map(function ($item) use ($date) { 
        $item['date'] = $date; 
        return $item; }, 
        $paytable);

    // Открываем сток money
    $stock = LTS::Stock($money);
    // Обновляем поле total у users данными из табличной части
    $stock->collector($users, 'user', ['total' => 'pay']);
    // Обновляем записи стока
    $stock->update(['doc' => $result['data']['id']], $paytable); 

    return $result;
});

// Подключаем стили Из файла index.css
$maindiv->CSS()->add('payment.css');

// Устанавливаем разметку Grid
$maindiv->setmodesdefault();
$subtable->setmodessubtable();
// Переопределяем шаблон element, добавив в него таблицу
$maindiv->setMode('element')
     ->device('desktop')
        ->areas(["element", "table"])
        ->rows("auto 1fr")
        ->columns("1fr")
     ->device('mobile')
        ->areas(["element", "table"])
        ->rows("auto 1fr")
        ->columns("1fr");
$maindiv->defaultmode('default');

// Построение страницы
LTS::Space()->build($maindiv);
?>

<?php
define('UPPER_DIR', dirname(__DIR__) . '/newlotis/');
include_once UPPER_DIR . 'lotis.php';

$base = LTS::MySql('tracking', 'localhost', 'root', 'root');
$works = $base->table('works');
$works->string('name', 100);
$works->float('price');
/*
// --- Подготовка данных ---
$base->create();
$works->create(); 

// Заполним тестовыми данными 
$works->values(['name' => 'Ремонт двигателя', 'price' => 5000])->insert();
$works->values(['name' => 'Замена масла', 'price' => 800])->insert();
$works->values(['name' => 'Диагностика ходовой', 'price' => 1200])->insert();
$works->values(['name' => 'Техобслуживание', 'price' => 2500])->insert();
$works->values(['name' => 'Покраска кузова', 'price' => 15000])->insert();
*/

// --- Интерфейс ---

// Создаем поле поиска
$workLookup = LTS::LookupField('work_lookup');
$workLookup->dbtable($works)->head(['name' => 'Работа']);
$workLookup->signal('work_lookup_Selected', 
<<<JS
    function() {
        const selected = LTS(workLookup).selected;
        if (selected) 
            $(resultDiv).text('Выбрано: ' + selected.name + ' — ' + selected.price + ' ₽');
    }
JS
);

// Контейнер для отображения результата
$resultDiv = LTS::Div('result')->capt('Ничего не выбрано');

// Страница
$page = LTS::Div(); 
$page->add(LTS::Div('capt1')->capt('Поиск работы'))
    ->add(LTS::Div('capt2')->capt('Начните вводить наименование работы:'))
    ->add($workLookup)
    ->add($resultDiv);

// Подключаем стили и скрипты
$page->css()->add('lookuptest.css'); 

// Строим страницу
LTS::Space()->build($page);
?>

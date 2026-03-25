<?php
// Подключаем LOTIS
define('UPPER_DIR', dirname(__DIR__) . '/newlotis/');
include_once UPPER_DIR . 'lotis.php';

// ========================
// 1. СОЗДАЁМ ГЛАВНЫЙ КОНТЕЙНЕР
// ========================

$mainDiv = LTS::Grid()->gap('16px');

// Заголовок
$mainDiv->area('header')->capt('<h2>Сотрудники</h2>');

// ========================
// 2. ТАБЛИЦА
// ========================

$table = LTS::DataTable();
$table->head([
    'sel' => '',
    'name' => 'Имя',
    'age' => 'Возраст',
    'del' => ''
]);
$table->data([
    ['name' => 'Анна', 'age' => 28],
    ['name' => 'Борис', 'age' => 34],
    ['name' => 'Виктория', 'age' => 22]
]);
$table->norows = false; // выводить строки таблицы при создании
$table->out(
<<<JS
function (row, obj) {
    row.find('td.Column_sel').text(obj.sel ? '✅' : '☐');
    row.find('td.Column_del').html('<input type="button" class="ltsRowDelbutton" value="x">');
}
JS
);

// Помещаем таблицу в область table сетки
$mainDiv->area('table')->add($table);
// ========================
// 3. ФОРМА РЕДАКТИРОВАНИЯ
// ========================

$form = LTS::Form();
$form->hidden('ltsDataId');
$form->text('name', 'Имя')->attr('required', true);
$form->number('age', 'Возраст')->attr('min', 1)->attr('required', true);

// Кнопки
$savebutton = $form->button('save', '✅ Сохранить')->click(
<<<JS
    const values = LTS(form).values();
    values.sel = 0;
    if(values.ltsDataId)
        LTS(table).values({ltsDataId: values.ltsDataId}, values);
    else
        LTS(table).append(values);
    LTS(mainDiv).mode("opentable");
JS
);
$form->button('cancel', '❌ Отмена')->click('LTS(mainDiv).mode("opentable")');

// Автоматическая разметка через cells()
$form->cells('save, cancel', 'name', 'age');

// Хук на клик по строке таблицы
$table->rowclick($form)
    ->signal('RowClick', 'LTS(mainDiv).mode("openeditor")', $table);

// Помещаем форму в область editor сетки
$mainDiv->area('editor')->add($form);

// ========================
// 3. КНОПКИ УПРАВЛЕНИЯ
// ========================

$mainDiv->area('buttons')
    ->add(LTS::Button()->capt('➕ Добавить')->click('LTS(form).clear(); LTS(mainDiv).mode("openeditor")'))
    ->add(LTS::Button()->capt('🗑️ Удалить отмеченные')->click('LTS(table).clear({sel: 1})'));

// ========================
// 4. СТИЛИ И РАЗМЕТКА СЕТКИ
// ========================

$mainDiv->CSS()->add('tabletest.css'); 

$mainDiv->defaultmode('opentable');

// Переопределяем шаблон element, добавив в него таблицу
$mainDiv->setMode('opentable')
        ->device('desktop')
            ->areas(["header", "buttons", "table"])
            ->rows("auto 1fr")
            ->columns("1fr")
     ->setMode('openeditor')
        ->device('desktop')
            ->areas(["header", "editor"])
            ->rows("auto auto 1fr")
            ->columns("1fr");

LTS::Space()->build($mainDiv);
?>
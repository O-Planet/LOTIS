[English](README.md) | [Русский](README.ru.md)

# LOTIS — PHP Framework for Fast Business Apps

"Меньше кода - больше смысла"

LOTIS (Low Time Script) — это фреймворк для быстрого создания интерактивных WEB-приложений и бизнес систем на PHP. 
Вы строите приложение как при десктопной разработке, используя принцпы ООП без привязки в верстке.   

## Ключевые особенности

- 💻 **Десктопный подход в WEB**: Управляйте интерфейсом через PHP-объекты (`Form`, `DataTable`) — без ручной верстки.
- 🔄 **Единое пространство имён**: Доступ к объектам через одни и те же переменные на сервере и на клиенте.
- 🗃 **Встроенная ORM**: Работа с базой через классы `MySqlTable` и `QueryBuilder`.
- 📦 **Готовые бизнес-компоненты**: документы с табличными частями `DataView`, стоки данных `Stock`, синхронизация с базой данных "на лету" `DataSync` — для сложных систем.
- ⚡ **Событийная модель**: `Events` заменяет AJAX — пишете логику, а не запросы.
- 🧩 **Минимум зависимостей**: Только PHP, jQuery, MySQL.

## Пример 1: Чтение файла и вывод в блок

```php
// Подключаем LOTIS
include_once 'newlotis/lotis.php';

// Основной контейнер
$maindiv = LTS::Div(); 

// Контейнер под содержимое прочитанного файла
$content = LTS::Div();

// Кнопка, по которой будет прочитан с сервера файл
$button = LTS::Button()
    ->capt('Нажми меня')
    ->click(<<<JS LTS(events).loadreadme(); >>>);

// Событие, читающее файл с сервера и выводящее на клиенте в контейнер $content
$events = LTS::Events();
$events->client('loadreadme',
<<<JS
   $(content).text(result); $(button).hide();
>>>
);
$events->server('loadreadme', function ($args) { return file_get_contents('readme.md'); });

// Подключаем стили Из файла index.css
$maindiv->CSS()->add('index.css');

// Построение страницы
$maindiv->addmany($events, $content, $button);
LTS::Space()->build($maindiv);
```

## Пример 2: Получение новых сообщений из БД

```php
// Подключаем LOTIS
include_once 'newlotis/lotis.php';

// Описываем таблицы базы данных
$base = LTS::MySql('mybase', 'localhost', 'root', 'root');

$users = $base->table('users');
$users->string('name', 100);

$messages = $base->table('messages');
$messages->table('recipient', $users);
$messages->table('sender', $users);
$messages->date('date');
$messages->text('info');
$messages->bool('newmessage');

// Форма для ввода имени пользователя
$form = LTS::Form();
$username = $form->text('name', 'Имя пользователя');
$form->button('enter', 'Получить сообщения')->click(
<<<JS
    const user = LTS(username).value();
    if(! user) {
        alert('Введите имя пользователя!');
        return;
    } 
    LTS(events).getinfo(user); 
>>>
);

// Собывтие, читающее данные из базы данных и возвращающее в таблицу 
$events = LTS::Events();
$events->client('getinfo(name)',
<<<JS
    if(result.ok)
        LTS(table).create(result.data);
    else
        alert(result.error);
>>>
);
$events->server('getinfo', function ($args) {
    global $messages;
    // Имя пользователя, введенное в форме
    $name = $args['name'];
    // Запрос к базе данных
    $allmessages = $messages->all(['recipient.name' => $name, 
        'newmessages' => 1, 
        'ORDER' => '-date',
        'FIELDS' => 'date, sender.name as autor, info']);
    // Если нет новых сообщений
    if(count($allmessages) == 0)
        return ['ok' => false, 'error' => 'Пользователь не получил новых сообщений'];
    // Собираем в массив id-ы всех полученных сообщений
    $ids = array_map(function ($item) { return $item->id; }, $allmessages);
    // Сбрасываем признак новых у всех прочитанных сообщений
    $messages->value('newmessage', 0)
        ->setall(['id' => $ids]);    
    // Возвращаем результ на клиен
    $return ['ok' => true, 'data' => $allmessages];
});

// Таблица для вывода сообщений
$table = LTS::DataTable();
$table->head(['date' => 'Дата', 'autor' => 'Автор', 'info' => 'Сообщение']);

// Основной контейнер
$maindiv = LTS::Div(); 

// Подключаем стили Из файла index.css
$maindiv->CSS()->add('index.css');

// Построение страницы
$maindiv->addmany($events, $form, $table);
LTS::Space()->build($maindiv);
```

## Пример 3: Документ выплат сотрудникам

```php
// Подключаем LOTIS
include_once 'newlotis/lotis.php';

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
    'area' => 'element',
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
JS)
;

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
$maindiv->CSS()->add('index.css');

// Построение страницы
LTS::Space()->build($maindiv);
```

## Как работает?

- PHP-классы формируют метаданные.
- Класс Space преобразует метаданные в HTML.

## Установка

```bash
git clone https://github.com/o-planet/lotis.git 
```

или просто подключите папку src/newlotis.

## Книга

"Разработка WEB-приложений для бизнеса на PHP"  
Автор: Олег Пономаренко  
Скоро в продаже.

Практическое руководство по инженерному мышлению в разработке, основанное на 25-летнем опыте создания коммерческих WEB-приложений. 
Автор предлагает уникальный подход: вместо изучения готовых решений вы создадите собственный фреймворк, понимая каждую строчку кода «изнутри».

## Для кого:

- WEB-приложения любой сложности
- ERP, CRM, API, сайты
- Документооборот
- Любой бизнес-проект, где важны скорость и надёжность

## Особенности

- ✅ PHP 7+
- ✅ Без Composer (поддерживает)
- ✅ Лёгкий вес (~50 КБ)
- ✅ До 10 000 строк в таблицах
- ✅ Интуитивный рабочий инструмент
- ✅ Минимальные требования
- ✅ Для автоматизации бизнеса

## Архитектура

```
src/
├── lotis.php            # Точка входа
├── LTS/                 # Ядро
│   ├── DataView.php     # Главная форма
│   ├── DataTable.php    # Таблицы с сортировкой и отбором
│   ├── FilterForm.php   # Форма фильтрации таблиц
│   ├── Stock.php        # Централизованный учёт
│   ├── Space.php        # Сборка UI
│   ├── Events.php       # События через AJAX
│   ├── Form.php         # Форма ввода: базовый контейнер для полей
│   ├── Input.php        # Поле ввода текста
│   ├── LookupField.php  # Поле поиска с выпадающим списком и автоподгрузкой
│   ├── JS.php           # Генерация JavaScript-логики на стороне PHP
│   ├── CSS.php          # Вставка и управление CSS-правилами на лету
│   ├── Div.php          # Универсальный контейнер
│   ├── Vars.php         # Система глобальных переменных
│   ├── Grid.php         # Сеточная вёрстка (таблицоподобная)
│   ├── Dialog.php       # Модальное окно (popup) для подтверждений и форм
│   ├── MySql.php        # Подключение к MySQL
│   ├── MySqlField.php   # Представление поля таблицы
│   ├── MySqlTable.php   # Работа с таблицей: select, insert, update, delete
│   ├── QueryBuilder.php # Построитель SQL-запросов
│   ├── Button.php       # Кнопка с действиями: отправка, сигнал, открытие формы
│   ├── Cells.php        # Сетка элементов: раскладка по строкам и столбцам
│   ├── Columns.php      # Гибкая колоночная вёрстка (аналог grid CSS)
│   ├── Accordion.php    # Раскрывающиеся блоки для группировки форм
│   ├── Construct.php    # Динамическое создание UI-структур
│   ├── DataSync.php     # Синхронизация данных между клиентом и сервером
│   ├── Debug.php        # Инструменты отладки: логирование, вывод переменных
│   ├── Element.php      # Базовый класс всех UI-элементов
│   ├── Ether.php        # Система широковещательных сообщений (pub/sub)
│   ├── Html.php         # Работа с HTML-тегами: label, span, div и др.
│   ├── Lang.php         # Поддержка мультиязычности
│   ├── LayerSlider.php  # Переключение слоёв интерфейса (пошаговые формы)
│   ├── Logger.php       # Логирование действий и ошибок
│   ├── ProgressBar.php  # Визуальный прогресс выполнения задачи
│   ├── Quark.php        # Мини-реализация объектной модели: события, подписки, вызовы
│   ├── Select.php       # Выпадающий список
│   ├── SimpleChart.php  # Простые графики: bar, line, pie
│   ├── Span.php         # Inline-контейнер
│   ├── Tabs.php         # Вкладки для переключения между разделами
│   └── Video.php        # Встраивание видео (локального или из сети)
├── JS/                  # Клиентская часть
│   ├── lts.js           
│   ├── Form.js          
│   ├── Events.js        
│   └── ...           
├── CSS/                 # Предопределенные стили
│   ├── FilterForm.css
│   ├── LookupField.css          
│   └── ...           
└── examples/            # Примеры проектов
    ├── tracker.zip      
    ├── testgrid.zip      
    └── ...      
```

## Лицензия

MIT — свободно используйте, модифицируйте, распространяйте.

## Автор

Олег Пономаренко (O-Planet)  
olegspost@list.com  
TG: @OPlanet  
http://www.o-planet.ru 

Сегодня тот самый день, чтобы начать!


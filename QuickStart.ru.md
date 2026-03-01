[English](QuickStart.md) | [Русский](QuickStart.ru.md)

# LOTIS Framework: Быстрый старт

## Установка

```bash
# Поместите фреймворк в отдельный каталог проекта
mkdir newlotis && cd newlotis
# Точка входа — файл lotis.php
```

---

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

## Объектно-ориентированный подход

LOTIS строит веб-приложения по классическим принципам ООП. Фреймворк предоставляет три типа объектов:

| Тип | Примеры | Назначение |
|:---|:---|:---|
| **UI-компоненты** | `Div`, `Grid`, `Tabs`, `Button`, `Dialog`, `Form` | Визуальные элементы интерфейса |
| **Функциональные объекты** | `JS`, `CSS`, `Vars`, `Events` | Управление скриптами, стилями, состоянием и взаимодействием |
| **Предметная область** | `DataTable`, `DataView`, `DataSync` | Работа с данными и бизнес-логикой |

### Создание объектов

Все объекты создаются через фабричный класс `LTS`:

```php
$form = LTS::Form();
$grid = LTS::Grid();
```

### Иерархическая структура

Каждый объект должен быть вложен в родительский контейнер:

```php
$main = LTS::Div('main');      // Явный ID
$form = LTS::Form();           // Автоматический ID
$main->add($form);
```

### Сборка приложения

```php
// Передаём корневой объект в Space для рендеринга
LTS::Space()->build($main);
```

---

## Принцип "Десктопности"

LOTIS устраняет разрыв между серверной и клиентской разработкой:

- **Единый файл** — весь код приложения в одном PHP-файле
- **Последовательное выполнение** — команды интерпретируются сверху вниз
- **Единое пространство имён** — одни названия переменных на сервере и клиенте

---

## Клиент-серверная интеграция

### Класс `JS`: встраивание скриптов

```php
$form = LTS::Form();
$form->js('ready')->add(<<<JS
    console.log('Форма готова');
JS);
```

Доступные контексты: `script` (в `<head>`), `ready` (после загрузки DOM).

### Единство переменных

PHP-переменные автоматически доступны в клиентском коде:

```php
$form = LTS::Form();
$table = LTS::DataTable();
$btn = LTS::Button()->capt('Добавить')->click(<<<JS
    const rowdata = LTS(form).values();  // Доступ к объекту по имени переменной
    LTS(table).append(rowdata);
    $(btn).hide();                       // jQuery-обёртка
JS);
```

> **Важно:** Переменная может быть объявлена в PHP *после* использования в JS-блоке, но *до* вызова `build()`.

---

## События: двусторонняя коммуникация

Класс `Events` связывает клиент и сервер:

```php
$events = LTS::Events();

// Клиентский обработчик ответа
$events->client('calculate(a, b)', <<<JS
    alert(result);
JS);

// Серверный обработчик
$events->server('calculate', function($args) {
    return $args['a'] + $args['b'];
});

// Вызов из интерфейса
$btn = LTS::Button()->capt('Сумма')->click(<<<JS
    LTS(events).calculate(5, 3);
JS);
```

---

## Глобальное состояние: класс `Vars`

Переменные, сохраняющиеся между запросами в рамках сессии:

```php
$vars = LTS::Vars();

// Проверка первого запуска
if (!$vars->get('initialized')) {
    $div->capt('Добро пожаловать!');
} else {
    $div->capt('С возвращением!');
}

// Установка флага на клиенте
$div->js('ready')->add(<<<JS
    LTS.vars().set('initialized', true).store();
JS);
```

---

## Расширение функциональности

### Пользовательские методы объектов

```php
$panel = LTS::Div();
$panel->method('highlight(color)', <<<JS
    $(panel).css('border-color', color);
JS);

$btn = LTS::Button()->capt('Выделить')->click(<<<JS
    LTS(panel).highlight('#ff0000');
JS);
```

### Хуки жизненного цикла

Для событий и методов доступны точки внедрения:

| Хук | Назначение | Возвращаемое значение |
|:---|:---|:---|
| `check` | Валидация возможности выполнения | `true`/`false` |
| `before` | Предобработка аргументов | Изменённые аргументы |
| `on` | Постобработка результата | — |

```php
// Пример: проверка на клиенте для события savefile(name)
$events->method('checksavefile(name)', <<<JS
  if(! name) alert('Имя файла не может быть пустым!');
  return name ? true : false;
JS);

// Пример: автоматическое переименование на сервере при коллизии имён файлов для события savefile
$events->add('beforesavefile', function($args) {
    $name = $args['name'];
    if (file_exists($name)) {
        $args['name'] = uniqid() . '_' . $name;
    }
    return $args;
});
```

---

## Сигналы и подписки

Реализация паттерна Pub/Sub для клиентской части:

```php
$container = LTS::Div();
$container->signal('theme:dark', <<<JS
    $(container).addClass('dark-theme');
JS);

// Триггер сигнала из любого места
$btn = LTS::Button()->capt('Тёмная тема')->click(<<<JS
    LTS.signal('theme:dark');
JS);
```

---

## Жизненный цикл объекта

```
┌─────────────┐    ┌────────────┐    ┌───────────┐    ┌──────────┐    ┌──────────────┐
│ __construct │  → │ compile    │  → │  shine    │  → │  childs  │  → │  addinspace  │
│             │    │ (проверки) │    │(рендеринг)│    │(потомки) │    │(регистрация) │
└─────────────┘    └────────────┘    └───────────┘    └──────────┘    └──────────────┘
```

### Точки внедрения в жизненный цикл

```php
$obj->check       = function() { /* ... */ return true; /* or false */ };
$obj->before     = function() { $this->prepareData(); };
$obj->on         = function() { $this->logCreation(); };
$obj->checkchilds = function() { /* ... */ return true; /* or false */ };
$obj->beforechilds = function() { $this->prepareChildsData(); };
$obj->onchilds   = function() { $this->logChildsCreation(); };
```

---


## Класс `Div`

Универсальный контейнер с полной поддержкой CSS Flexbox.

```php
$app = LTS::Div('app')
    ->flex()
    ->column()
    ->align('stretch') 
    ->gap('20px');
```

---

## Класс Grid

Адаптивная CSS Grid-сетка с поддержкой мультимодовости.

```php
$grid = LTS::Grid('layout');

// Именованные моды
$grid->setMode('element')
     ->device('desktop')
        ->areas([
          "header header",
          "menu content",
          "footer footer"
        ])
        ->rows("auto 1fr")
        ->columns("1fr")
     ->device('mobile')
        ->areas([
          "header",
          "content",
          "footer"
        ])
        ->rows("auto 1fr")
        ->columns("1fr");

// Размещение объектов в сетке
$grid->area('content')->add($table);
```

Переключение мод

```js
LTS(grid).mode('element');
```

---

## Работа с таблицами: `DataTable`

```php
$dataTable = LTS::DataTable('users')
    ->head(['name', 'email'])
    ->data([
        ['1', 'Alice', 'alice@example.com'],
        ['2', 'Bob', 'bob@example.com']
    ])
    ->sort(['name']);
```

### На клиенте доступны методы:

```js
LTS(dataTable).head(prot)            // установить шапку
LTS(dataTable).rows(data)            // установить строки
LTS(dataTable).create(values)        // сформировать таблицу
LTS(dataTable).values(attr, values)  // фильтр + обновление строк
LTS(dataTable).append(values)        // добавление новых строк
LTS(dataTable).clear(attr)           // очистка по фильтру
LTS(dataTable).filter(values)        // показ строк по фильтру
LTS(dataTable).sort(name, direction) // сортировка строк
LTS(dataTable).fieldvalues(field, selectedrows)        // все значения колонки по выбранным строкам
LTS(dataTable).findRows(attr)        // jQuery-строки по фильтру 
LTS(dataTable).Row(values)           // jQuery-строка по данным  
```

---

## Работа с формами: `Form`, `Input`

```php
$form = LTS::Form('login')
    ->text('username', 'Логин')
    ->password('password', 'Пароль')
    ->button('send', 'Войти');

// Событие при надатии на кнопку
$form->event('send', function ($args) {
    if ($args['username'] == 'admin' && $args['password'] == 'secret') {
        return 'Вы вошли';
    }
    return 'Неверный логин или пароль';
})
->event('send', <<<JS
  alert(result);
JS);
```

### По шаблону

```php
$form->generate([
    ['name' => 'id',  'type' => 'hidden'],
    ['name' => 'date',  'caption' => 'Дата', 'type' => 'date'],
    ['name' => 'user', 'type' => 'table', 'dbtable' => $users, 'caption' => 'Сотрудник'],
    ['name' => 'comment',  'caption' => 'Комментарий'],
    ['name' => 'total',  'caption' => 'Сумма', 'type' => 'number', 'readonly' => 1],
    ['name' => 'save', 'caption' => 'Записать', 'type' => 'button'],
    ['name' => 'close', 'caption' => 'Отмена', 'type' => 'button']
]);
```

### На клиенте:

```js
LTS(form).data();       // получить значения в формате FormData
LTS(form).values(vals); // получить или установить значения
LTS(form).value('username', 'trump'); // получить или установить значение поля
LTS(form).clear();      // очистить форму
```

---

## Локализация: `Lang`

Создаётся автоматически по имени файла:

```php
$lang = LTS::Lang('ru');
echo $lang->say('Hello'); // → Привет, если есть перевод
```

Файл: `index.ru`

```
welcome: Добро пожаловать
description: Это сайт о программировании
footer: © Все права защищены
```

---

## ORM: `MySql`, `MySqlTable`, `MySqlField`, `QueryBuilder`

```php
$base = new MySql('testdb', 'localhost', 'root', '');
$users = $base->table('users');
$users->string('name', 100);
$users->string('phone', 12);
$users->string('password', 60);
$users->enum('role', ['admin' => 'Администратор', 'user' => 'Пользователь']);
$users->bool('disabled');
$users->index('phone');
```

### Запись в таблицу:

```php
$users->value('name', 'Donald Trump')
  ->value('phone', '1-222-333-444')
  ->insert();
```

### Запросы к базе данных
```php
$all = $users->all(['role' => 'user', 'disabled' => 1]);
```

### Сложные запросы с использованием QueryBuilder

```php
$trackingprod->query()
      ->fields("id, total,
                trackingprod.techmap, 
                trackingprod.quantity, 
                techmap.goods as goods, 
                goods.name as name,
                goods.price as price")
      ->leftJoin($techmap, 'trackingprod.techmap = techmap.id', 'techmap')
      ->leftJoin($goods, 'techmap.goods = goods.id', 'goods')
      ->andWhere($conditions)
      ->all();
```

---

## Класс `DataView`

Предметно-ориентированный компонент для управления табличными данными: список записей с фильтрацией, форма редактирования, подчинённые таблицы.

DataView объединяет три элемента интерфейса в единый компонент:

| Элемент      | Класс        | Функция                                               |
| :----------- | :----------- | :---------------------------------------------------- |
| **Список**   | `DataTable`  | Табличное отображение записей с сортировкой и выбором |
| **Карточка** | `Form`       | Форма создания/редактирования записи                  |
| **Фильтр**   | `FilterForm` | Панель отбора и поиска                                |

```php
// Минимальная конфигурация
$view = LTS::DataView()
    ->setmodesdefault()                          // стандартные режимы отображения
    ->bindtodb($emplpay, [
    'head' => [
        'sel'   => '',
        'date' => 'Дата'
    ],
    'inputs' => [
        ['name' => 'id',  'type' => 'hidden'],
        ['name' => 'date', 'type' => 'date', 'caption' => 'Дата'],
        ['name' => 'save', 'caption' => 'Записать', 'type' => 'button'],
        ['name' => 'close', 'caption' => 'Отмена', 'type' => 'button']
    ],
    'fields' => 'id, date',
    'cells' => ['save, close', 'date'],
    'filter' => [
        ['name' => 'date', 'type' => 'date', 'caption' => 'Дата']
    ],
    'sort' => ['date as date']
]);

LTS::Space()->build($view);
```

### Подчиненные таблицы

```php
$subtable = $view->subtable('emplpaysubtable', $emplpaytable, [
    'head' => [
        'name' => 'Сотрудник',
        'pay' => 'Выплатить'
    ],
    'fields' => 'employee, employee.name as name, pay',
    'area' => 'table',
    'inputs' => [
        ['name' => 'ltsDataId', 'type' => 'hidden'],
        ['name' => 'employee', 'type' => 'select', 'values' => $employeeslist, 'caption' => 'Сотрудник'],
        ['name' => 'pay', 'type' => 'numeric', 'caption' => 'Выплатить'],
        ['name' => 'save', 'type' => 'button', 'caption' => 'Ок'],
        ['name' => 'close', 'type' => 'button', 'caption' => 'Отмена']
    ],
    'cells' => ['save, close', 'employee', 'pay'],
    'filter' => null
]);
```
---

## Класс `Stock`

Система управления операциями и синхронизации табличных данных. Обеспечивает атомарное обновление записей с автоматическим пересчётом связанных коллекторов (остатков, сумм, статистик).

### Назначение

Решает задачу согласованного обновления двух связанных сущностей:
- **Таблица операций** — детальные строки документа (приход, расход, перемещение)
- **Таблица остатков** — агрегированные значения по ключевым полям

При изменении операций автоматически корректируются остатки без риска рассогласования данных.

```php
// 1. Создаём сток для таблицы операций
$stock = LTS::Stock($operationsTable);

// 2. Подключаем коллектор остатков товаров
$stock->collector($goodsTable, 'goods', ['total' => 'quantity']);

// 3. Фиксируем изменения документа
$stock->update(
    ['doc_id' => 123],                           // ключи для выборки старых значений
    [                                            // новые строки документа
        ['goods' => 1, 'quantity' => 10],
        ['goods' => 2, 'quantity' => 5]
    ]
);
```

---

### Принцип работы

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│  Старые строки  │────→│   Сопоставление │←────│  Новые строки   │
│   (из БД)       │     │   по ключевым   │     │   (из формы)    │
└─────────────────┘     │     полям       │     └─────────────────┘
                        └────────┬────────┘
                                 │
              ┌──────────────────┼──────────────────┐
              ↓                  ↓                  ↓
        ┌─────────┐        ┌─────────┐        ┌─────────┐
        │ Удалить │        │ Обновить│        │ Добавить│
        │ (устар.)│        │ (изм-я) │        │ (новые) │
        └────┬────┘        └────┬────┘        └────┬────┘
             │                  │                  │
             └──────────────────┼──────────────────┘
                                ↓
                    ┌─────────────────────┐
                    │ Пересчёт коллекторов│
                    │  (дельта: ±значения)│
                    └─────────────────────┘
```
---

## ▶️ Что дальше?

- Изучи [полные примеры](Examples.ru.md)
- Задавай вопросы в [Telegram](https://t.me/OPlanet)
- Создавай свои компоненты и делись ими!


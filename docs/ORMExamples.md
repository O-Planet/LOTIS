# ПРИМЕРЫ ИСПОЛЬЗОВАНИЯ LOTIS ORM

## 1. ПОДКЛЮЧЕНИЕ К БАЗЕ ДАННЫХ

### Простое подключение:
```php
$db = LTS::MySql('tracking', 'localhost', 'root', 'password');
```

### С проверкой ошибок:
```php
$db = LTS::MySql('tracking', 'localhost', 'root', 'password');
if (!$db->connect()) {
    die("Ошибка подключения: " . $db->geterror());
}
```

### Создание базы данных если не существует:
```php
$db = LTS::MySql('new_database', 'localhost', 'root', 'password');
$db->create();  // CREATE DATABASE IF NOT EXISTS
```

## 2. ОПРЕДЕЛЕНИЕ СТРУКТУРЫ ТАБЛИЦ

### Базовая таблица пользователей:
```php
$users = $db->table('users');
$users->string('login', 50);
$users->string('password', 255);
$users->string('email', 100);
$users->enum('status', ['active' => 'Активен', 'blocked' => 'Заблокирован', 'pending' => 'Ожидает']);
$users->bool('is_admin');
$users->date('created_at');
$users->index('status');
$users->index('login,email');  // составной индекс
$users->create();
```

### Таблица товаров с внешними ключами:
```php
$categories = $db->table('categories');
$categories->string('name', 100);
$categories->create();

$goods = $db->table('goods');
$goods->string('name', 200);
$goods->string('sku', 50);  // артикул
$goods->text('description');
$goods->float('price', 15, 2);
$goods->float('quantity', 14, 3);
$goods->enum('unit', ['piece' => 'шт', 'kg' => 'кг', 'meter' => 'м']);
$goods->table('category_id', $categories);  // внешний ключ
$goods->file('image', 'jpg,png,gif', '/uploads/goods/');
$goods->point('location');  // геокоординаты
$goods->index('sku');
$goods->index('category_id');
$goods->create();
```

### Древовидная структура (разделы):
```php
$sections = $db->table('sections');
$sections->string('name', 100);
$sections->parent($sections);  // ссылка на родителя
$sections->int('sort_order');
$sections->create();
```

## 3. ОПЕРАЦИИ ВСТАВКИ (INSERT)

### Простая вставка:
```php
$users->value('login', 'ivanov');
$users->value('password', password_hash('secret', PASSWORD_DEFAULT));
$users->value('email', 'ivanov@company.ru');
$users->value('status', 'active');
$userId = $users->insert();
```

### Цепочка value():
```php
$goods->value('name', 'Ноутбук Dell XPS 13')
->value('sku', 'NB-DELL-001')
->value('price', 89999.99)
->value('quantity', 15)
->value('unit', 'piece')
->value('category_id', 5)
->insert();
```

### Массовая вставка через values():
```php
$goods->values([
    'name' => 'Монитор 27"',
    'sku' => 'MON-27-001',
    'price' => 35000,
    'quantity' => 8,
    'category_id' => 3
])->insert();
```

### Вставка с проверкой:
```php
$data = [
    'login' => 'petrov',
    'email' => 'petrov@company.ru',
    'status' => 'active'
];
$users->values($data);
if ($id = $users->insert()) {
    echo "Создан пользователь ID: $id";
} else {
    echo "Ошибка: " . $db->geterror();
}
```

## 4. ОПЕРАЦИИ ОБНОВЛЕНИЯ (UPDATE)

### Обновление по ID (set):
```php
$users->value('status', 'blocked');
$users->set(15);  // обновить пользователя с ID=15
```

### Обновление с проверкой существования:
```php
$users->value('last_login', date('Y-m-d H:i:s'));
if ($users->set(15)) {
    echo "Обновлено";
} else {
    echo "Пользователь не найден";
}
```

### Массовое обновление (setall):
```php
// Повысить цену на 10% для всех товаров категории 5
$goods->value('price', '@price * 1.1');  // raw SQL с @
$goods->setall(['category_id' => 5]);
```

### Условия в setall:
```php
// Активировать всех неактивных пользователей, созданных до 2024 года
$users->value('status', 'active');
$users->setall([
    'status' => 'pending',
    'created_at:left' => '2024-01-01'  // <= 2024-01-01 00:00:00
]);
```

### Сложные условия:
```php
$goods->value('quantity', 0);
$goods->value('status', 'out_of_stock');
$goods->setall([
    'WHERE' => 'quantity < 5',
    'ORDER' => 'id',
    'LIMIT' => 100
], 'AND');
```

## 5. ВЫБОРКА ДАННЫХ (SELECT)

### Получение одной записи по ID:
```php
$user = $users->get(15);
echo $user->login;
echo $user->email;
```

### Проверка существования:
```php
if ($users->exists(15)) {
    echo "Пользователь найден";
}
```

### Простая выборка всех записей:
```php
$allUsers = $users->all();
foreach ($allUsers as $user) {
    echo $user->login . "\n";
}
```

### Выборка с условием:
```php
$activeUsers = $users->all('status', 'active');
```

### Выборка с условиями (массив):
```php
$activeUsers = $users->all(['status' => 'active']);
```

### Несколько условий:
```php
$filtered = $goods->all([
    'category_id' => 5,
    'price:left' => 1000,    // price >= 1000
    'price:right' => 5000,   // price <= 5000
    'status' => 'active'
]);
```

### Сортировка и лимит:
```php
$latest = $users->all([
    'status' => 'active',
    'ORDER' => '-created_at',  // сортировка по убыванию даты
    'LIMIT' => 10
]);
```

### Выборка конкретных полей:
```php
$list = $goods->all([
    'FIELDS' => 'id,name,price',
    'category_id' => 3
]);
```

### LIKE поиск:
```php
$found = $users->all(['name' => '$иван']);      // LIKE '%иван%'
$found = $users->all(['name' => '%Иванов']);    // LIKE 'Иванов%'
$found = $users->all(['email' => '!test@test.com']);  // != 'test@test.com'
```

### Диапазоны дат:
```php
// За конкретный день
$today = $orders->all(['created_at' => 'day:' . date('Y-m-d')]);

// За период
$period = $orders->all(['created_at' => 'period:2024-01-01,2024-01-31']);

// Через :left и :right
$range = $orders->all([
    'created_at:left' => '2024-01-01',   // >= 2024-01-01 00:00:00
    'created_at:right' => '2024-01-31'   // <= 2024-01-31 23:59:59
]);
```

### IN условие (массив):
```php
$selected = $goods->all(['id' => [10, 20, 30, 40, 50]]);
```

### Условия с магическими символами:
```php
$complex = $goods->all([
    'status' => '!new',
    'price' => '>=1000',
    'quantity' => '<5'
]);
```

### Сложное WHERE условие (строка):
```php
$complex = $goods->all("price > 1000 AND (quantity < 5 OR status = 'out_of_stock')");
```

### Группировка и агрегация:
```php
$stats = $orders->all([
    'FIELDS' => 'status',
    'SUM' => 'total as sum_total',
    'COUNT' => '* as orders_count',
    'GROUP' => 'status'
]);
```

### Только уникальные значения:
```php
$cities = $users->all([
    'FIELDS' => 'city',
    'DISTINCT' => 'city'
]);
```

### Возврат массивом вместо объектов:
```php
$db->assarray();
$data = $users->all();  // массивы ассоциативные
$db->assobjects();      // вернуть к объектам
```

### Среднее значение:
```php
$avgPrice = $goods->all([
    'AVG' => 'price as avg_price',
    'category_id' => 5
])[0]->avg_price;
```

### В QueryBuilder:
```php
$stats = $orders->query()
->aggregate('AVG', 'total as average_order')
->aggregate('SUM', 'total as total_revenue')
->aggregate('COUNT', '* as orders_count')
->where('status', 'completed')
->first();

echo "Средний чек: " . $stats->average_order;
echo "Всего продаж: " . $stats->total_revenue;
```

### Группировка со статистикой:
```php
$byCategory = $goods->all([
    'FIELDS' => 'category_id',
    'AVG' => 'price as avg_price',
    'MIN' => 'price as min_price',
    'MAX' => 'price as max_price',
    'COUNT' => 'id as products_count',
    'GROUP' => 'category_id'
]);
```

### GROUP_CONCAT (объединение строк):
```php
// Получить все теги товара через запятую
$products = $goods->all([
    'FIELDS' => 'id,name',
    'GROUP_CONCAT' => 'tag as tags_list',
    'GROUP' => 'id'
]);
// Результат: tags_list = "электроника,ноутбук, Dell"
```

## 6. QUERYBUILDER (СЛОЖНЫЕ ЗАПРОСЫ)

### Базовое использование:
```php
$result = $users->query()
->where('status', 'active')
->orderBy('name')
->all();
```

### Цепочка условий:
```php
$result = $goods->query()
->where('category_id', 5)
->where('price', '>=1000')  // >= 1000
->where('name', '$ноутбук') // LIKE '%ноутбук%'
->orderBy('-price')
->limit(20)
->all();
```

### OR условия:
```php
$result = $users->query()
->where('status', 'active')
->orWhere('status', 'pending')
->all();
```

### Группировка условий (скобки):
```php
$result = $users->query()
->whereGroup(function($q) {
    $q->where('status', 'active');
    $q->orWhere('status', 'pending');
})
->where('is_admin', 0)
->all();
```

### JOIN с другой таблицей:
```php
$result = $goods->query()
->select('id, name, categories.name as category_name')
->leftJoin($categories, 'goods.category_id = categories.id', 'categories')
->where('goods.price', '>1000')
->all();
```

### Автоматический JOIN через with():
```php
$result = $goods->query()
->with('category_id')  // автоматически JOIN categories
->where('category_id.status', 'active')
->all();
```

### Подзапросы:
```php
$result = $goods->query()
->whereInSubquery('category_id', function($q) use ($categories) {
    $q->select('id')->from($categories)->where('type', 'electronics');
})
->all();
```

### Агрегации:
```php
$stats = $orders->query()
->aggregate('SUM', 'total as revenue')
->aggregate('COUNT', '* as count')
->aggregate('AVG', 'total as average')
->where('status', 'completed')
->first();
```

### UNION запросы:
```php
$q1 = $users->query()->where('status', 'active');
$q2 = $users->query()->where('status', 'pending');
$all = $q1->union($q2)->all();
```

### Получение только первой записи:
```php
$first = $users->query()
->where('status', 'active')
->orderBy('created_at')
->first();
```

### Отладка (получить SQL):
```php
$sql = $users->query()
->where('status', 'active')
->getQuery()
->all();  // вернет строку SQL вместо выполнения
```

## 7. УДАЛЕНИЕ ДАННЫХ

### Удаление по ID:
```php
$users->del(15);
```

### Массовое удаление:
```php
$users->delall(['status' => 'blocked', 'created_at:left' => '2023-01-01']);
```

### С лимитом:
```php
$logs->delall([
    'WHERE' => 'created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)',
    'LIMIT' => 1000
]);
```

### Проверка целостности перед удалением:
```php
if ($goods->integritycheck(15)) {
    $goods->del(15);  // можно удалять, ссылок нет
} else {
    echo "Нельзя удалить: есть связанные записи";
}
```

## 8. РАБОТА СО СХЕМОЙ БД

### Загрузка существующей таблицы из БД:
```php
$existing = $db->loadtable('users');
if ($existing) {
    print_r($existing->fields);  // увидеть все поля
}
```

### Проверка изменений структуры:
```php
$users->string('phone', 20);  // новое поле
$users->index('phone');       // новый индекс
$sql = $users->getQuery()->checkupdate();  // получить SQL изменений
// или сразу применить:
$users->update();
```

### Полное обновление структуры:
```php
// Добавить поле, изменить тип, удалить старое
$goods->string('barcode', 50);
$goods->float('weight', 10, 3);
// ... удалить поле old_field просто убрав его из определения ...
$goods->update();  // синхронизировать с БД
```

## 9. ТРАНЗАКЦИИ

### Ручное управление:
```php
$db->begin();
try {
    $orderId = $orders->value('total', 1000)->insert();
    foreach ($items as $item) {
        $orderItems->values([
            'order_id' => $orderId,
            'product_id' => $item['id'],
            'quantity' => $item['qty']
        ])->insert();
    }
    $db->commit();
} catch (Exception $e) {
    $db->rollback();
    echo "Ошибка: " . $e->getMessage();
}
```

### Автоматические через callback:
```php
$result = $db->transaction(function($db) use ($orders, $items) {
    $orderId = $orders->value('total', 1000)->insert();
    foreach ($items as $item) {
        // если здесь будет ошибка — автоматический rollback
        $db->table('order_items')->values([
            'order_id' => $orderId,
            'product_id' => $item['id']
        ])->insert();
    }
    return $orderId;  // вернет это значение если успешно
});

if ($result === false) {
    echo "Транзакция отменена";
} else {
    echo "Заказ создан: $result";
}
```

## 10. ГЕНЕРАЦИЯ ФОРМ

### Автоматическая генерация полей формы:
```php
$inputs = $goods->generateinputs();  // все поля кроме id
// или только нужные:
$inputs = $goods->generateinputs('name,price,quantity,category_id');
// или массивом:
$inputs = $goods->generateinputs(['name', 'price', 'description']);
```

### Результат — массив для генератора форм:
```php
[
    [
        'name' => 'name',
        'caption' => 'Name',
        'type' => 'text'
    ],
    [
        'name' => 'description',
        'caption' => 'Description',
        'type' => 'textarea'
    ],
    [
        'name' => 'category_id',
        'caption' => 'Category Id',
        'type' => 'table',
        'dbtable' => [объект таблицы categories]
    ],
    [
        'name' => 'price',
        'caption' => 'Price',
        'type' => 'numeric'
    ]
]
```

## 11. ПОЛЕ ТИПА FILE

### Сохранение в директорию:
```php
$goods->file('photo', 'jpg,png', '/uploads/products/');
$goods->create();

// При загрузке файла:
$goods->value('photo', 'product_123.jpg');  // имя файла в БД
// сам файл должен быть перемещен в /uploads/products/ вручную
```

### Сохранение в BLOB:
```php
$goods->file('document', 'pdf,doc', false);  // false = в БД
$goods->create();  // создаст поля document и document_filedata

// Загрузка:
$content = file_get_contents($_FILES['doc']['tmp_name']);
$goods->value('document', 'contract.pdf');
$goods->value('document_filedata', $content);  // бинарные данные
$goods->insert();
```

## 12. ОБРАБОТКА ОШИБОК И ОТЛАДКА

### Получение последнего SQL:
```php
$users->getQuery()->set(15);
echo $users->str_sql_query;  // UPDATE ... WHERE id = ?
```

### Проверка ошибок:
```php
if (!$result = $users->insert()) {
    echo "Ошибка SQL: " . $db->geterror();
}
```

### Режим отладки в QueryBuilder:
```php
$sql = $users->query()
->where('status', 'active')
->getQuery()
->all();  // строка SQL, не выполняется

echo $sql;  // посмотреть что получилось
```

## 13. ПРАКТИЧЕСКИЕ СЦЕНАРИИ

### Пагинация:
```php
$page = $_GET['page'] ?? 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

$items = $goods->all([
    'status' => 'active',
    'ORDER' => '-created_at',
    'LIMIT' => "$offset,$perPage"
]);

$total = $goods->all([
    'status' => 'active',
    'COUNT' => '* as total'
])[0]->total ?? 0;

$pages = ceil($total / $perPage);
```

### Поиск с автодополнением:
```php
$term = $_GET['q'] ?? '';
$suggestions = $goods->all([
    'name' => '$' . $term,  // LIKE %term%
    'FIELDS' => 'id,name',
    'LIMIT' => 10
]);
```

### Обновление остатков:
```php
$db->begin();
// Списание со склада
$stock->value('quantity', '@quantity - ' . $amount);
$stock->setall(['product_id' => $productId]);

// Запись в историю
$history->values([
    'product_id' => $productId,
    'operation' => 'write_off',
    'amount' => -$amount,
    'date' => date('Y-m-d H:i:s')
])->insert();

$db->commit();
```

### Копирование записи:
```php
$original = $goods->get(15);
$goods->values([
    'name' => $original->name . ' (копия)',
    'sku' => $original->sku . '-COPY',
    'price' => $original->price,
    'category_id' => $original->category_id
]);
$newId = $goods->insert();
```

### Массовое копирование:
```php
$sourceItems = $goods->all(['category_id' => 5]);
foreach ($sourceItems as $item) {
    $goods->values([
        'name' => $item->name,
        'category_id' => 10,  // новая категория
        'price' => $item->price
    ])->insert();
}
```

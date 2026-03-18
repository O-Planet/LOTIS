[English](ORMExamples.md) | [Русский](ORMExamples.ru.md)

# LOTIS ORM USAGE EXAMPLES

## 1. DATABASE CONNECTION

### Simple connection:
```php
$db = LTS::MySql('tracking', 'localhost', 'root', 'password');
```

### With error checking:
```php
$db = LTS::MySql('tracking', 'localhost', 'root', 'password');
if (!$db->connect()) {
    die("Connection error: " . $db->geterror());
}
```

### Create database if not exists:
```php
$db = LTS::MySql('new_database', 'localhost', 'root', 'password');
$db->create();  // CREATE DATABASE IF NOT EXISTS
```

## 2. DEFINING TABLE STRUCTURE

### Basic users table:
```php
$users = $db->table('users');
$users->string('login', 50);
$users->string('password', 255);
$users->string('email', 100);
$users->enum('status', ['active' => 'Active', 'blocked' => 'Blocked', 'pending' => 'Pending']);
$users->bool('is_admin');
$users->date('created_at');
$users->index('status');
$users->index('login,email');  // composite index
$users->create();
```

### Goods table with foreign keys:
```php
$categories = $db->table('categories');
$categories->string('name', 100);
$categories->create();

$goods = $db->table('goods');
$goods->string('name', 200);
$goods->string('sku', 50);  // SKU
$goods->text('description');
$goods->float('price', 15, 2);
$goods->float('quantity', 14, 3);
$goods->enum('unit', ['piece' => 'pcs', 'kg' => 'kg', 'meter' => 'm']);
$goods->table('category_id', $categories);  // foreign key
$goods->file('image', 'jpg,png,gif', '/uploads/goods/');
$goods->point('location');  // geocoordinates
$goods->index('sku');
$goods->index('category_id');
$goods->create();
```

### Tree structure (sections):
```php
$sections = $db->table('sections');
$sections->string('name', 100);
$sections->parent($sections);  // reference to parent
$sections->int('sort_order');
$sections->create();
```

## 3. INSERT OPERATIONS

### Simple insert:
```php
$users->value('login', 'ivanov');
$users->value('password', password_hash('secret', PASSWORD_DEFAULT));
$users->value('email', 'ivanov@company.ru');
$users->value('status', 'active');
$userId = $users->insert();
```

### Chain value():
```php
$goods->value('name', 'Dell XPS 13 Laptop')
->value('sku', 'NB-DELL-001')
->value('price', 89999.99)
->value('quantity', 15)
->value('unit', 'piece')
->value('category_id', 5)
->insert();
```

### Mass insert via values():
```php
$goods->values([
    'name' => '27" Monitor',
    'sku' => 'MON-27-001',
    'price' => 35000,
    'quantity' => 8,
    'category_id' => 3
])->insert();
```

### Insert with check:
```php
$data = [
    'login' => 'petrov',
    'email' => 'petrov@company.ru',
    'status' => 'active'
];
$users->values($data);
if ($id = $users->insert()) {
    echo "User created ID: $id";
} else {
    echo "Error: " . $db->geterror();
}
```

## 4. UPDATE OPERATIONS

### Update by ID (set):
```php
$users->value('status', 'blocked');
$users->set(15);  // update user with ID=15
```

### Update with existence check:
```php
$users->value('last_login', date('Y-m-d H:i:s'));
if ($users->set(15)) {
    echo "Updated";
} else {
    echo "User not found";
}
```

### Mass update (setall):
```php
// Increase price by 10% for all goods in category 5
$goods->value('price', '@price * 1.1');  // raw SQL with @
$goods->setall(['category_id' => 5]);
```

### Conditions in setall:
```php
// Activate all inactive users created before 2024
$users->value('status', 'active');
$users->setall([
    'status' => 'pending',
    'created_at:left' => '2024-01-01'  // <= 2024-01-01 00:00:00
]);
```

### Complex conditions:
```php
$goods->value('quantity', 0);
$goods->value('status', 'out_of_stock');
$goods->setall([
    'WHERE' => 'quantity < 5',
    'ORDER' => 'id',
    'LIMIT' => 100
], 'AND');
```

## 5. DATA SELECTION (SELECT)

### Get one record by ID:
```php
$user = $users->get(15);
echo $user->login;
echo $user->email;
```

### Check existence:
```php
if ($users->exists(15)) {
    echo "User found";
}
```

### Simple select all records:
```php
$allUsers = $users->all();
foreach ($allUsers as $user) {
    echo $user->login . "\n";
}
```

### Select with condition:
```php
$activeUsers = $users->all('status', 'active');
```

### Select with conditions (array):
```php
$activeUsers = $users->all(['status' => 'active']);
```

### Multiple conditions:
```php
$filtered = $goods->all([
    'category_id' => 5,
    'price:left' => 1000,    // price >= 1000
    'price:right' => 5000,   // price <= 5000
    'status' => 'active'
]);
```

### Sorting and limit:
```php
$latest = $users->all([
    'status' => 'active',
    'ORDER' => '-created_at',  // sort by date descending
    'LIMIT' => 10
]);
```

### Select specific fields:
```php
$list = $goods->all([
    'FIELDS' => 'id,name,price',
    'category_id' => 3
]);
```

### LIKE search:
```php
$found = $users->all(['name' => '$ivan']);      // LIKE '%ivan%'
$found = $users->all(['name' => '%Ivanov']);    // LIKE 'Ivanov%'
$found = $users->all(['email' => '!test@test.com']);  // != 'test@test.com'
```

### Date ranges:
```php
// For a specific day
$today = $orders->all(['created_at' => 'day:' . date('Y-m-d')]);

// For a period
$period = $orders->all(['created_at' => 'period:2024-01-01,2024-01-31']);

// Via :left and :right
$range = $orders->all([
    'created_at:left' => '2024-01-01',   // >= 2024-01-01 00:00:00
    'created_at:right' => '2024-01-31'   // <= 2024-01-31 23:59:59
]);
```

### IN condition (array):
```php
$selected = $goods->all(['id' => [10, 20, 30, 40, 50]]);
```

### Conditions with magic symbols:
```php
$complex = $goods->all([
    'status' => '!new',
    'price' => '>=1000',
    'quantity' => '<5'
]);
```

### Complex WHERE condition (string):
```php
$complex = $goods->all("price > 1000 AND (quantity < 5 OR status = 'out_of_stock')");
```

### Grouping and aggregation:
```php
$stats = $orders->all([
    'FIELDS' => 'status',
    'SUM' => 'total as sum_total',
    'COUNT' => '* as orders_count',
    'GROUP' => 'status'
]);
```

### Only unique values:
```php
$cities = $users->all([
    'FIELDS' => 'city',
    'DISTINCT' => 'city'
]);
```

### Return as array instead of objects:
```php
$db->assarray();
$data = $users->all();  // associative arrays
$db->assobjects();      // return to objects
```

### Average value:
```php
$avgPrice = $goods->all([
    'AVG' => 'price as avg_price',
    'category_id' => 5
])[0]->avg_price;
```

### In QueryBuilder:
```php
$stats = $orders->query()
->aggregate('AVG', 'total as average_order')
->aggregate('SUM', 'total as total_revenue')
->aggregate('COUNT', '* as orders_count')
->where('status', 'completed')
->first();

echo "Average check: " . $stats->average_order;
echo "Total sales: " . $stats->total_revenue;
```

### Grouping with statistics:
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

### GROUP_CONCAT (concatenate strings):
```php
// Get all product tags comma-separated
$products = $goods->all([
    'FIELDS' => 'id,name',
    'GROUP_CONCAT' => 'tag as tags_list',
    'GROUP' => 'id'
]);
// Result: tags_list = "electronics,laptop, Dell"
```

## 6. QUERYBUILDER (COMPLEX QUERIES)

### Basic usage:
```php
$result = $users->query()
->where('status', 'active')
->orderBy('name')
->all();
```

### Chain conditions:
```php
$result = $goods->query()
->where('category_id', 5)
->where('price', '>=1000')  // >= 1000
->where('name', '$laptop') // LIKE '%laptop%'
->orderBy('-price')
->limit(20)
->all();
```

### OR conditions:
```php
$result = $users->query()
->where('status', 'active')
->orWhere('status', 'pending')
->all();
```

### Grouping conditions (brackets):
```php
$result = $users->query()
->whereGroup(function($q) {
    $q->where('status', 'active');
    $q->orWhere('status', 'pending');
})
->where('is_admin', 0)
->all();
```

### JOIN with another table:
```php
$result = $goods->query()
->select('id, name, categories.name as category_name')
->leftJoin($categories, 'goods.category_id = categories.id', 'categories')
->where('goods.price', '>1000')
->all();
```

### Automatic JOIN via with():
```php
$result = $goods->query()
->with('category_id')  // automatically JOIN categories
->where('category_id.status', 'active')
->all();
```

### Subqueries:
```php
$result = $goods->query()
->whereInSubquery('category_id', function($q) use ($categories) {
    $q->select('id')->from($categories)->where('type', 'electronics');
})
->all();
```

### Aggregations:
```php
$stats = $orders->query()
->aggregate('SUM', 'total as revenue')
->aggregate('COUNT', '* as count')
->aggregate('AVG', 'total as average')
->where('status', 'completed')
->first();
```

### UNION queries:
```php
$q1 = $users->query()->where('status', 'active');
$q2 = $users->query()->where('status', 'pending');
$all = $q1->union($q2)->all();
```

### Get only first record:
```php
$first = $users->query()
->where('status', 'active')
->orderBy('created_at')
->first();
```

### Debug (get SQL):
```php
$sql = $users->query()
->where('status', 'active')
->getQuery()
->all();  // will return SQL string instead of executing
```

## 7. DELETING DATA

### Delete by ID:
```php
$users->del(15);
```

### Mass delete:
```php
$users->delall(['status' => 'blocked', 'created_at:left' => '2023-01-01']);
```

### With limit:
```php
$logs->delall([
    'WHERE' => 'created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)',
    'LIMIT' => 1000
]);
```

### Integrity check before delete:
```php
if ($goods->integritycheck(15)) {
    $goods->del(15);  // can delete, no references
} else {
    echo "Cannot delete: there are related records";
}
```

## 8. WORKING WITH DB SCHEMA

### Load existing table from DB:
```php
$existing = $db->loadtable('users');
if ($existing) {
    print_r($existing->fields);  // see all fields
}
```

### Check structure changes:
```php
$users->string('phone', 20);  // new field
$users->index('phone');       // new index
$sql = $users->getQuery()->checkupdate();  // get SQL of changes
// or apply immediately:
$users->update();
```

### Full structure update:
```php
// Add field, change type, remove old one
$goods->string('barcode', 50);
$goods->float('weight', 10, 3);
// ... remove field old_field simply by removing it from definition ...
$goods->update();  // synchronize with DB
```

## 9. TRANSACTIONS

### Manual control:
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
    echo "Error: " . $e->getMessage();
}
```

### Automatic via callback:
```php
$result = $db->transaction(function($db) use ($orders, $items) {
    $orderId = $orders->value('total', 1000)->insert();
    foreach ($items as $item) {
        // if there is an error here — automatic rollback
        $db->table('order_items')->values([
            'order_id' => $orderId,
            'product_id' => $item['id']
        ])->insert();
    }
    return $orderId;  // will return this value if successful
});

if ($result === false) {
    echo "Transaction cancelled";
} else {
    echo "Order created: $result";
}
```

## 10. FORM GENERATION

### Automatic form field generation:
```php
$inputs = $goods->generateinputs();  // all fields except id
// or only needed ones:
$inputs = $goods->generateinputs('name,price,quantity,category_id');
// or as array:
$inputs = $goods->generateinputs(['name', 'price', 'description']);
```

### Result — array for form generator:
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
        'dbtable' => [categories table object]
    ],
    [
        'name' => 'price',
        'caption' => 'Price',
        'type' => 'numeric'
    ]
]
```

## 11. FILE FIELD TYPE

### Save to directory:
```php
$goods->file('photo', 'jpg,png', '/uploads/products/');
$goods->create();

// When uploading file:
$goods->value('photo', 'product_123.jpg');  // file name in DB
// the file itself must be moved to /uploads/products/ manually
```

### Save to BLOB:
```php
$goods->file('document', 'pdf,doc', false);  // false = in DB
$goods->create();  // will create fields document and document_filedata

// Upload:
$content = file_get_contents($_FILES['doc']['tmp_name']);
$goods->value('document', 'contract.pdf');
$goods->value('document_filedata', $content);  // binary data
$goods->insert();
```

## 12. ERROR HANDLING AND DEBUGGING

### Get last SQL:
```php
$users->getQuery()->set(15);
echo $users->str_sql_query;  // UPDATE ... WHERE id = ?
```

### Check errors:
```php
if (!$result = $users->insert()) {
    echo "SQL Error: " . $db->geterror();
}
```

### Debug mode in QueryBuilder:
```php
$sql = $users->query()
->where('status', 'active')
->getQuery()
->all();  // SQL string, not executed

echo $sql;  // see what turned out
```

## 13. PRACTICAL SCENARIOS

### Pagination:
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

### Search with autocomplete:
```php
$term = $_GET['q'] ?? '';
$suggestions = $goods->all([
    'name' => '$' . $term,  // LIKE %term%
    'FIELDS' => 'id,name',
    'LIMIT' => 10
]);
```

### Stock update:
```php
$db->begin();
// Write-off from warehouse
$stock->value('quantity', '@quantity - ' . $amount);
$stock->setall(['product_id' => $productId]);

// Write to history
$history->values([
    'product_id' => $productId,
    'operation' => 'write_off',
    'amount' => -$amount,
    'date' => date('Y-m-d H:i:s')
])->insert();

$db->commit();
```

### Copy record:
```php
$original = $goods->get(15);
$goods->values([
    'name' => $original->name . ' (copy)',
    'sku' => $original->sku . '-COPY',
    'price' => $original->price,
    'category_id' => $original->category_id
]);
$newId = $goods->insert();
```

### Mass copy:
```php
$sourceItems = $goods->all(['category_id' => 5]);
foreach ($sourceItems as $item) {
    $goods->values([
        'name' => $item->name,
        'category_id' => 10,  // new category
        'price' => $item->price
    ])->insert();
}
```

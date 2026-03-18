[English](MySqlTable.md) | [Русский](MySqlTable.ru.md)

🚀 [Quick Start](../QuickStart.md)

[LOTIS ORM Usage Examples](ORMExamples.md)

# MySqlTable Class

## Purpose
A class representing a database table in the LOTIS ORM framework. Designed for defining table structure, managing fields and indexes, generating SQL queries for table creation and modification, and performing CRUD operations (select, insert, update, delete). The class stores metadata about fields (type, size, nullable, default, enum values), relationships with other tables, and indexes. It is the main working object of the ORM — all data operations are performed through a MySqlTable instance. Created via the `MySql::table()` method and stores a reference to the owner connection.

### Key Features

* **Creating tables** in the database
* **Managing table fields**
* **Working with indexes**
* **Form generation** based on table structure
* **Executing CRUD operations**
* **Data integrity** checking

## Properties

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$name** | `string` | `public` | Table name in the database. Set in constructor. Used for generating SQL queries. |
| **$fields** | `array` | `public` | Associative array of MySqlField objects keyed by field names. Stores table structure. Keys are field names, values are MySqlField objects. |
| **$indexes** | `array` | `public` | Array of table index names. Populated when defining structure via `index()` or when loading via `MySql::loadtable()`. |
| **$insert_id** | `int/null` | `public` | ID of the last inserted record. Populated after calling `insert()`. |
| **$str_sql_query** | `string` | `public` | Last generated SQL query. Populated when calling `create()`, `insert()`, `all()`, `delall()`, `setall()` methods. |
| **$get_query** | `bool` | `public` | Flag to return SQL query instead of execution. If `true` — methods return SQL string, if `false` — execute query. Controlled by `getQuery()` method. |
| **$owner** | `MySql/null` | `public` | Reference to the owner MySql object. Set when creating via `MySql::table()`. Used to execute queries through the connection. |
| **$collation** | `string` | `public` | Collation for the table. Default is empty string — uses connection collation. |
| **$charset** | `string` | `public` | Character set for the table. Default is empty string — uses connection charset. |
| **$distinct** | `bool` | `public` | Flag to use DISTINCT in SELECT queries. Controlled via QueryBuilder. |
| **$queryBuilder** | `QueryBuilder/null` | `public` | Cached QueryBuilder object for complex queries. Created lazily on first call to `with()` or `query()`. |
| **$asarray** | `bool` | `public` | Flag for result return format. If `true` — results are returned as associative arrays, if `false` — as stdClass objects. |

## Methods

### Constructor

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **__construct** | `string $name` | — | Class constructor. Saves table name, initializes `$fields` and `$indexes` as empty arrays, `$get_query` to `false`, `$charset` and `$collation` to empty strings, `$owner` to `null`. Does not establish connection — requires call through MySql owner. |

### Field Creation Methods

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **int** | `string $name`, `int $len = 11` | `$this` | Adds an INT type field. Creates MySqlField with type `'INT'` and specified length. Saves to `$fields[$name]`. Returns `$this` to support fluent interface. |
| **string** | `string $name`, `int $len = 100` | `$this` | Adds a VARCHAR type field. Creates MySqlField with type `'VARCHAR'` and specified length. Saves to `$fields[$name]`. Returns `$this` to support fluent interface. |
| **text** | `string $name` | `$this` | Adds a TEXT type field. Creates MySqlField with type `'TEXT'`. Saves to `$fields[$name]`. Returns `$this` to support fluent interface. |
| **bool** | `string $name` | `$this` | Adds a TINYINT field for boolean values. Creates MySqlField with type `'TINYINT'`. Saves to `$fields[$name]`. Returns `$this` to support fluent interface. |
| **date** | `string $name` | `$this` | Adds a DATETIME type field. Creates MySqlField with type `'DATETIME'`. Saves to `$fields[$name]`. Returns `$this` to support fluent interface. |
| **float** | `string $name`, `int $dd = 15`, `int $dr = 2` | `$this` | Adds a DECIMAL type field. Creates MySqlField with type `'DECIMAL'` and parameters `dd` (digits before decimal) and `dr` (digits after). Saves to `$fields[$name]`. Returns `$this` to support fluent interface. |
| **enum** | `string $name`, `array $values = null` | `$this` | Adds an ENUM type field. Creates MySqlField with type `'ENUM'` and array of allowed values. Saves to `$fields[$name]`. Returns `$this` to support fluent interface. |
| **file** | `string $name`, `string $ext`, `bool $dir = false` | `$this` | Adds a field for file storage. Creates MySqlField with type `'FILE'` and parameters for extension and directory. If `$dir === false` — creates additional field `{$name}_filedata` of type BLOB for storing data in DB. If `$dir !== false` — creates directory if it doesn't exist. Returns `$this` to support fluent interface. |
| **point** | `string $name` | `$this` | Adds a POINT type field for geospatial data. Creates MySqlField with type `'POINT'`. Saves to `$fields[$name]`. Returns `$this` to support fluent interface. |
| **table** | `string $name`, `MySqlTable $table` | `$this` | Adds a reference field to another table. Creates MySqlField with type `'TABLE'` and reference to table object. Used for defining foreign keys. Returns `$this` to support fluent interface. |
| **parent** | `MySqlTable $table` | `$this` | Adds a parent relationship field. Creates MySqlField with name `"parent_{$table->name}"`, type `'TABLE'`, and reference to table. Convenient method for hierarchical structures. Returns `$this` to support fluent interface. |
| **index** | `string $name` | `$this` | Adds an index on the specified field. Checks if name exists in `$indexes` and adds if absent. Returns `$this` to support fluent interface. |

### Table Structure Methods

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **create** | — | `bool/string` | Creates table in DB based on defined fields. Generates SQL `CREATE TABLE IF NOT EXISTS` with fields, indexes, PRIMARY KEY (`id`), ENGINE InnoDB. If `$get_query === true` — returns SQL string. Otherwise — executes query and returns `true` on success, `false` on error. |
| **checkupdate** | — | `bool/string` | Checks if table structure in DB matches the code definition. If table doesn't exist — calls `create()`. If exists — compares fields, types, nullable, default, ENUM values, indexes, collation. Returns `true` if structure is up to date, SQL string `ALTER TABLE` if there are changes, `false` on error. |
| **update** | — | `bool` | Updates table structure in DB. Calls `checkupdate()`. If `$get_query === true` — returns SQL string. Otherwise — executes ALTER TABLE if there are changes. Returns `true` on success, `false` on error. |
| **field** | `string $name`, `mixed $val = null` | `MySqlField/null` | Returns field object by name. If `$val !== null` — sets field value via `setvalue()` and returns object. If field not found — returns `null`. |
| **integritycheck** | `mixed $rec` | `bool` | Checks data integrity before deleting a record. Calls `$owner->integritycheck($this, $rec)`. Returns `true` if no references, `false` if there are. |

### Data Writing Methods

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **value** | `string $name`, `mixed $val = null` | `mixed/$this` | Universal method for getting/setting field value. If `$val === null` — returns current field value. If passed — sets value via `setvalue()` and returns `$this`. If field not found — returns `null` or `$this`. |
| **values** | `array $arr` | `$this` | Sets values for multiple fields at once. Iterates over array and calls `value()` for each key-value pair. Returns `$this` to support fluent interface. |
| **freevalues** | — | `$this` | Resets `$isset` flags of all fields to `false`. Clears prepared values for next insert/update operation. Returns `$this` to support fluent interface. |
| **insert** | — | `int` | Executes INSERT query with data from fields. Collects fields with `$isset === true` and `$value !== null`, generates SQL, executes via `$owner->prepare()`. Calls `freevalues()` after insert. Returns ID of inserted record via `$owner->lastInsertId()` or `0` on error. |
| **set** | `int $id = null` | `int` | Universal insert/update method. If `$id === null` or `empty` — calls `insert()`. Otherwise — calls `setall(['id' => $id])`. If `$get_query === true` — returns `true/false`, otherwise — returns ID or `0`. |
| **setall** | `mixed $conditions = null`, `string $operator = 'AND'` | `bool` | Executes UPDATE query for records matching conditions. Collects data from fields with `$isset === true`, creates QueryBuilder, adds WHERE/ORDER/LIMIT conditions, executes via `QueryBuilder::setall()`. Calls `freevalues()` after update. Returns `true` on success, `false` on error. |

### Data Reading Methods

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **get** | `int $id` | `object/false` | Finds record by ID. Returns one stdClass object with record data or `false` if not found or connection error. Uses prepared statement with `WHERE id = ? LIMIT 1`. |
| **exists** | `int $id` | `bool` | Checks if record exists by ID. Executes `SELECT COUNT(*)` and returns `true` if record found, `false` if not or connection error. |
| **with** | `string $relation` | `$this` | Adds LEFT JOIN to related table. Finds field of type `'TABLE'` by name, creates QueryBuilder if not exists, adds JOIN with condition `{$this->name}.{$relation} = {$alias}.id`. Returns `$this` to support fluent interface. |
| **all** | `mixed $name = null`, `mixed $val = null` | `array/false` | Executes SELECT query with optional filtering. If `$name === null` — returns all records. If string — adds WHERE condition. If array — supports keys: `FIELDS`, `ORDER`, `GROUP`, `LIMIT`, `JOIN`, `LEFT JOIN`, `RIGHT JOIN`, `WHERE`, aggregate functions. Returns array of objects/arrays or `false` on error. |
| **getQuery** | — | `$this` | Sets `$get_query` flag to `true`. Subsequent method calls will return SQL queries instead of executing. Returns `$this` to support fluent interface. |
| **query** | — | `QueryBuilder` | Creates and returns a new QueryBuilder object for building complex SELECT queries with JOIN, WHERE, GROUP BY, ORDER BY, LIMIT. QueryBuilder uses current connection `$owner`. |

### Data Deletion Methods

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **del** | `int $id` | `bool` | Deletes record by ID. Calls `delall(['id' => (int) $id])`. Returns `true` on success, `false` on error. |
| **delall** | `mixed $name = null`, `mixed $val = null` | `bool` | Executes DELETE query with optional filtering. Supports same conditions as `all()`. Creates QueryBuilder, generates DELETE SQL, executes via prepared statement. Returns `true` on success, `false` on error. |

### Integration

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **generateinputs** | `mixed $fieldNames = null` | `array` | Generates UI field configuration for form based on table structure. If `$fieldNames === null` — uses all fields except `id`. If string — splits by comma. If array — uses as list. Returns array of configurations with keys: `name`, `caption`, `type`, `values`, `readonly`, `dbtable`. |

### Private Methods

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **formatCaption** | `string $name` | `string` | Formats field name into readable caption. Replaces `_` with space, splits camelCase, capitalizes first word. Used in `generateinputs()`. |
| **getParamType** | `mixed $value` | `string` | Determines parameter type for prepared statement. Returns `'i'` for int, `'d'` for float, `'s'` for others. Used in legacy code. |
| **parseEnumFromColumnType** | `string $columnType` | `array` | Extracts values from ENUM type when loading structure. Parses string like `enum('val1','val2')` via regular expression. Returns array of values without quotes. |

## Usage Examples

```php
// Database connection:
$db = LTS::MySql('mydb', 'localhost', 'user', 'pass');

// Table definition:
$users = $db->table('users');
$users->string('name', 100);
$users->string('email', 255);
$users->enum('status', ['active' => 'Active', 'blocked' => 'Blocked']);
$users->table('role_id', $roles);  // foreign key
$users->create();  // create in DB

// Insert:
$users->value('name', 'John')
    ->value('email', 'john@test.com')
    ->insert();

// Update:
$users->value('status', 'blocked')->set(5);  // by ID=5

// Mass update:
$users->value('status', 'active')->setall(['role_id' => 1]);

// Select:
$list = $users->all(['status' => 'active', 'ORDER' => '-created_at', 'LIMIT' => 10]);

// Complex select via QueryBuilder:
$result = $users->query()
    ->where('status', 'active')
    ->where('name', '$john')  // LIKE '%john%'
    ->leftJoin($roles, 'users.role_id = roles.id', 'roles')
    ->orderBy('-created_at')
    ->limit(10)
    ->all();
```

## Notes
*   The class is created via `MySql::table()` — not recommended to create instances directly via `new MySqlTable()`.
*   All field addition methods return `$this` to support fluent interface and chaining.
*   Reference fields (type `'TABLE'`) automatically determine foreign keys by convention `{fieldname}_id` when loading via `MySql::loadtable()`.
*   Method `insert()` automatically escapes values via prepared statements — no need for manual `real_escape_string()` call.
*   Methods `del()` and `delall()` don't automatically check integrity — requires explicit `integritycheck()` call before deletion.
*   Data for insert/update accumulates via `value()`/`values()` — one object can be used for multiple operations.
*   Method `query()` returns QueryBuilder for complex queries — simple queries are executed via `all()`/`get()`/`exists()`.
*   Result return format (array/object) is determined by `$asarray` flag or `$owner->asarray`.
*   When loading structure via `MySql::loadtable()`, fields and indexes are populated automatically from `information_schema`.
*   ENUM values are stored in MySqlField object — can be used for validation and UI generation via `generateinputs()`.
*   Method `create()` is useful for deploying DB structure from code — doesn't require manual table creation via phpMyAdmin.
*   Indexes are added via `index()` — not created automatically, only by explicit developer indication.
*   Field `id` is considered default primary key — added automatically in `CREATE TABLE`, no explicit declaration required.
*   Method `set()` requires passing record ID for update — updates only one record by primary key.
*   For mass update use `setall()` with WHERE conditions or QueryBuilder.
*   The class doesn't implement soft delete — deletion is physical via DELETE.
*   For transactional operations, wrap calls in `$owner->transaction()`.
*   Method `get()` returns `false` if record not found — doesn't throw exceptions.
*   When inserting via `insert()`, `id` field is ignored — auto-generated by auto-increment.
*   The class is compatible with MySQL 5.7+ and MariaDB 10.0+.
*   For DATETIME fields, values must be in `'Y-m-d H:i:s'` format — no automatic conversion.
*   Boolean fields are stored as TINYINT — `true` = 1, `false` = 0.
*   Method `checkupdate()` compares current structure in DB with code definition — useful for migrations.
*   MySqlTable objects are cached in `$owner->dbtables` — subsequent `table()` calls return the same object.
*   For write methods (insert/update/delete) to work, connection is required — called automatically.
*   The class doesn't implement data validation — responsibility lies with developer or UI components.
*   Method `value()` can be called multiple times — data accumulates until `insert()`/`setall()` is called.
*   When updating via `setall()`, only fields with `$isset === true` from `$fields` are passed.
*   For complex WHERE conditions, use QueryBuilder instead of condition array in `all()`.
*   Method `generateinputs()` automatically determines UI field type based on DB field type — TEXT → textarea, ENUM → select, TABLE → lookup, etc.
*   For FILE fields with `$dir !== false`, files are saved to filesystem, with `$dir === false` — to BLOB field in DB.
*   Method `with()` caches QueryBuilder in `$queryBuilder` — subsequent calls use the same object.
*   Flag `$get_query` is useful for debugging — allows viewing generated SQL before execution.
*   When creating table via `create()`, ENGINE InnoDB is used — required for transaction and foreign key support.
*   Method `update()` doesn't delete fields from DB that are absent in code — for this use direct ALTER TABLE.
*   For DECIMAL fields, `dd` and `dr` parameters define overall precision and decimal places count.
*   Method `exists()` is more efficient than `get()` for existence check — executes `COUNT(*)` instead of `SELECT *`.
*   When loading structure via `loadtable()`, `id` field is ignored — considered default primary key.
*   Indexes are loaded from `information_schema.STATISTICS` — primary key (`PRIMARY`) is not included in `$indexes` list.
*   Method `freevalues()` should be called after insert/update if object is reused — prevents data duplication.
*   For working with multiple DBs, create separate MySql instances — one object = one connection.
*   The class doesn't implement connection pooling — each instance creation potentially creates new connection.
*   Method `all()` returns `false` on empty result — not empty array, requires `empty()` check.
*   For reference fields, type `'TABLE'` is converted to `INT(11)` when generating SQL — foreign key is not automatically created at DB level.
*   Method `checkupdate()` doesn't automatically modify structure — returns SQL for manual execution or `update()` call.
*   When generating UI via `generateinputs()`, `id` field is excluded — not included in edit form.
*   For ENUM, associative arrays are converted to value list — keys are used as values in SQL.
*   The class supports loading structure from existing DB via `MySql::loadtable()` — fields are created automatically.
*   Method `setall()` supports special condition keys: `WHERE`, `ORDER`, `LIMIT` — processed separately from fields.
*   For mass operations, use transactions via `$owner->transaction()` — ensures atomicity.
*   On connection error, methods return `false` or `0` — requires check before using result.
*   The class doesn't implement query result caching — each call executes DB query.
*   For optimizing large selects, use `LIMIT` in `all()` method.
*   Method `query()` creates new QueryBuilder each call — not cached for query independence.

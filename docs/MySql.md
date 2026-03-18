[English](MySql.md) | [Русский](MySql.ru.md)

🚀 [Quick Start](../QuickStart.md)

[LOTIS ORM Usage Examples](ORMExamples.md)

# MySql Class

## Purpose
The main class for working with MySQL databases in the LOTIS ORM framework. Designed for establishing connections to database servers, managing connections, executing SQL queries, preparing parameterized queries, and managing transactions. The class provides a low-level API for interacting with MySQL via the mysqli extension, supports automatic reconnection when the connection is lost, string escaping, and data integrity checks when deleting records. It acts as the owner for MySqlTable objects — creates, caches, and manages the pool of application tables. All data operations are performed through MySqlTable instances obtained from this class.

### Key Features

* **Database connection** management
* **Query execution** of various types
* **Data preparation** for safe usage
* **Transactional** data operations
* **Data integrity** verification
* **Table management** and structure control

When working with LOTIS ORM, you will most often need two methods from this class: database connection (`connect` method) and obtaining a reference to a table (`table` method). Other methods are used within `MySqlTable` and `QueryBuilder`.

## Properties

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$link** | `mysqli/null` | `public` | Active database connection. Initialized on first call to `connect()` or `query()`. Contains `null` before connection. |
| **$name** | `string` | `public` | Database name. Used for forming SQL queries, creating databases, and accessing tables via `information_schema`. |
| **$dbtables** | `array` | `public` | Associative array of MySqlTable objects keyed by table names. Caches loaded table structures to speed up subsequent accesses. Keys are table names, values are MySqlTable objects. |
| **$server** | `string` | `private` | MySQL server address (host). Set in constructor. Used when connecting via `new mysqli()`. |
| **$user** | `string` | `private` | Username for database connection. Set in constructor. |
| **$password** | `string` | `private` | Password for database connection. Set in constructor. |
| **$asarray** | `bool` | `private` | Flag for query result format. If `true` — results are returned as associative arrays, if `false` — as stdClass objects. Default is `false`. Controlled by `assarray()`/`assobjects()` methods. |

## Methods

### Constructor

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **__construct** | `string $name`, `string $server`, `string $user`, `string $password` | — | Class constructor. Saves connection parameters to private properties, initializes `$dbtables` as empty array, `$link` as `null`. Does not establish connection immediately — connection happens lazily on first query via `connect()`, `query()`, `prepare()` or `real_escape_string()`. |

### Connection Methods

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **connect** | — | `bool` | Establishes connection to MySQL server via `new mysqli($server, $user, $password)`. Checks `$link->connect_errno` — returns `false` on error. On successful connection saves mysqli object to `$link` and returns `true`. Called automatically on first query. |
| **query** | `string $str_sql_query` | `mysqli_result/false` | Executes arbitrary SQL query. If `$link` is not set — calls `connect()`. On execution error (exception or mysqli error) returns `false`. Returns mysqli result object on success. Supports SELECT, INSERT, UPDATE, DELETE, CREATE, DROP and other queries. |
| **prepare** | `string $str_sql_query`, `array $values` | `mysqli_stmt/false` | Prepares parameterized SQL query with value binding. Automatically determines parameter types: `i` — int, `d` — float, `s` — string. Calls `bind_param()` with array unpacking via `...$values` and `execute()`. Returns mysqli_stmt object on success, `false` on error. |
| **result** | `mysqli_stmt $stmt`, `bool $asarray = false` | `array/false` | Extracts results from prepared query. Supports two modes: via `get_result()` (requires mysqlnd, faster) and via `bind_result()` (universal fallback). Formats result according to `$asarray` parameter or `$this->asarray` property. Returns array of rows (arrays or objects) or `false` on error. Closes stmt after data extraction. |

### Data Management Methods

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **create** | — | `mysqli_result/false` | Creates database if it doesn't exist. Executes query `CREATE DATABASE IF NOT EXISTS {$name}`. If connection is not established — calls `connect()`. Returns query result or `false` on error. Useful for automatic database deployment on first application launch. Not all hosting providers allow this query, assuming database creation via control panel. |
| **table** | `string $name` | `MySqlTable` | Returns or creates MySqlTable object by name. If table already exists in `$dbtables` — returns existing object (cache). Otherwise — creates new MySqlTable, saves to `$dbtables`, sets `$owner` to current MySql object and returns. Main method for accessing tables. |
| **existstable** | `string $name` | `bool` | Checks if table exists in `$dbtables` cache. Returns `true` if table was already loaded via `table()` or `loadtable()`, `false` if not. Does not check actual table existence in DB — only checks memory. |
| **loadtable** | `string $name` | `MySqlTable/false` | Loads table structure from database. Executes queries to `information_schema.TABLES` (existence check), `information_schema.COLUMNS` (fields) and `information_schema.STATISTICS` (indexes). Creates MySqlField objects for each field, determines types, nullable, default values, ENUM values. Determines foreign keys by `_id` suffix. Saves table to `$dbtables` and returns object. Returns `false` if table doesn't exist. |
| **real_escape_string** | `string $str` | `string` | Escapes special string for safe use in SQL query. If connection is not established — calls `connect()`. Returns escaped string via `$link->real_escape_string()`. Used for SQL injection protection when forming queries without parameterization. |
| **integritycheck** | `mixed $table`, `mixed $rec` | `bool` | Checks data integrity before deleting a record. Accepts table (MySqlTable object or name) and record (ID, array with `id` key or object with `id` property). Searches for references to the record being deleted in other tables via `TABLE` type fields (foreign keys). If references found — returns `false` (deletion prohibited). If no references — returns `true`. |

### Transactional Operations

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **begin** | — | `bool` | Begins transaction via `$link->begin_transaction()`. If connection is not established — calls `connect()`. Returns `true` on success, `false` on connection error. Works only with InnoDB tables. |
| **commit** | — | `bool` | Commits transaction via `$link->commit()`. Returns `true` on success, `false` if connection is not established. All changes since `begin()` are saved to DB. |
| **rollback** | — | `bool` | Rolls back transaction via `$link->rollback()`. Returns `true` on success, `false` if connection is not established. All changes since `begin()` are cancelled. |
| **transaction** | `callable $callback` | `mixed/false` | Executes code in transaction context with automatic rollback on error. Calls `begin()`, executes `$callback($this)`, on success — `commit()`, on error/exception — `rollback()`. Returns callback result or `false` on failure. Recommended for all write operations to ensure atomicity. |

### Information Methods

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **geterror** | — | `string` | Returns text of last error from MySQL server via `$link->error`. Used for debugging after failed queries. |
| **lastInsertId** | — | `int` | Returns ID of last inserted record via `$link->insert_id`. Used after INSERT queries to get auto-increment ID. Returns 0 if last query was not INSERT. |
| **assarray** | — | `$this` | Sets `$asarray` flag to `true`. Results of all subsequent queries will be returned as associative arrays. Supports fluent interface for chaining. |
| **assobjects** | — | `$this` | Sets `$asarray` flag to `false`. Results of all subsequent queries will be returned as stdClass objects. Supports fluent interface for chaining. |

### Private Methods

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **addFieldToTable** | `MySqlTable $table`, `object $col` | — | Private method for adding field to table during structure loading. Analyzes column type from `information_schema.COLUMNS`, creates corresponding MySqlField object (VARCHAR, INT, ENUM, DECIMAL, DATETIME, etc.). Determines foreign keys by `_id` suffix — if field is named `{tablename}_id` and table exists — sets type to `TABLE` and reference to table. |
| **parseEnumFromColumnType** | `string $columnType` | `array` | Private method for extracting values from ENUM type. Parses string like `enum('value1','value2','value3')` via regular expression. Returns array of allowed values without quotes. Used when loading table structure. |
| **existsTableInDatabase** | `string $tableName` | `bool` | Private method for checking table existence in DB. Executes query to `information_schema.TABLES` with `LIMIT 1`. Returns `true` if table found, `false` if not. Used when determining foreign keys. |

### Usage Examples

```php
// Database connection
$db = LTS::MySql('mydatabase', 'localhost', 'user', 'password');

// Create database
$db->create();

// Create table
$table = $db->table('users');
$table->string('name', 100)
     ->email('email')
     ->create();

// Execute simple query without using MySqlTable
$result = $db->query("SELECT * FROM users");
```

## Notes
*   The class doesn't require external ORM — all mappings are determined automatically based on DB structure via `information_schema`.
*   Database connection is established lazily — on first call to `query()`, `prepare()`, `connect()`, `real_escape_string()`, `begin()` or `loadtable()`.
*   Parameterized queries via `prepare()` automatically determine parameter types — no need to specify types manually (`i`, `d`, `s` are formed automatically).
*   Method `result()` supports two operation modes — via `get_result()` (faster, requires mysqlnd) and via `bind_result()` (universal, works without mysqlnd).
*   Flag `$asarray` controls result return format — can be set globally via `assarray()`/`assobjects()` or locally via `result()` parameter.
*   Method `integritycheck()` prevents deletion of records that have references in other tables — ensures referential integrity at application level (foreign keys determined by `_id` suffix).
*   Foreign keys are determined automatically by naming convention — fields with `_id` suffix are considered references to tables with corresponding names (e.g., `user_id` → `user` table).
*   Method `transaction()` ensures safe operation execution with automatic rollback on errors — recommended for all write operations (INSERT, UPDATE, DELETE).
*   Table cache `$dbtables` speeds up subsequent accesses — table structure is loaded from DB only once per session via `loadtable()` or `table()`.
*   Method `loadtable()` loads complete table structure including fields, types, nullable, default values, indexes, comments and ENUM values.
*   ENUM fields are parsed automatically — values are saved in MySqlField object for validation and UI generation.
*   The class supports working with multiple databases — you can create multiple MySql instances with different connection parameters.
*   Connection and query execution errors don't throw exceptions — return `false` for developer handling (exceptions are caught inside methods).
*   Method `real_escape_string()` should be called for all user data before inserting into non-parameterized queries.
*   The class is compatible with MySQL 5.7+ and MariaDB 10.0+.
*   Method `create()` is useful for automatic database deployment on first application launch — doesn't require manual creation via phpMyAdmin.
*   Transactions work only with InnoDB tables — for MyISAM `begin()`/`commit()`/`rollback()` have no effect.
*   Method `lastInsertId()` returns 0 if last query was not INSERT — requires check before use.
*   MySqlTable objects created via `table()` keep reference to owner (`$owner`) — this allows executing queries in connection context without passing `$link`.
*   When loading table, `id` field is ignored in `addFieldToTable()` — considered default primary key, not added to `$fields`.
*   Indexes are loaded from `information_schema.STATISTICS` — primary key (`PRIMARY`) is not included in table's index list.
*   For DECIMAL fields precision is preserved — `dd` (digits before decimal) and `dr` (digits after decimal) parameters are extracted from `COLUMN_TYPE`.
*   Method `existstable()` checks only cache — for checking actual existence in DB use `loadtable()` or direct query to `information_schema`.
*   On query preparation error `prepare()` returns `false` — requires check before calling `result()`.
*   The class doesn't implement migrations — for changing DB structure use `MySqlTable::update()` or SQL queries directly via `query()`.
*   For connection pooling it's recommended to create one MySql instance per request — reuse saves resources and avoids multiple connections.
*   Method `table()` is the main way to access tables — not recommended to create MySqlTable directly via `new`.
*   When loading table structure field comments (`COLUMN_COMMENT`) are extracted but not saved in MySqlField — can be used for documentation generation.
*   The class doesn't support named parameters in prepared statements — uses positional parameters `?`.
*   For DATETIME fields values must be in `'Y-m-d H:i:s'` format — no automatic conversion.
*   Boolean fields are stored as TINYINT(1) — MySQL doesn't have separate BOOL type.
*   Method `transaction()` catches exceptions — if callback throws Exception, rollback will be executed.
*   When working with multiple DBs you need to create separate MySql instances — one object = one connection.
*   The class doesn't implement connection pooling — each instance creation potentially creates new connection.
*   To extend connection lifetime it's recommended to store MySql instance in global variable or singleton.
*   Method `geterror()` returns last query error — for error history use `Logger` class.
*   When connecting to remote server MySQL access needs to be configured (user, host, permissions).
*   The class doesn't encrypt password in memory — for enhanced security use environment variables.
*   Method `query()` supports multiple queries only if enabled in MySQL settings (`MULTI_STATEMENTS`).
*   The class doesn't implement retry logic on connection loss — requires handling `false` returns in user code.

[English](QueryBuilder.md) | [Русский](QueryBuilder.ru.md)

🚀 [Quick Start](../QuickStart.md)

[LOTIS ORM Usage Examples](ORMExamples.md)

# QueryBuilder Class

## Purpose
A class for building complex SQL queries in the LOTIS ORM framework. Designed for programmatic generation of SELECT, UPDATE, DELETE queries with support for JOIN, WHERE, GROUP BY, ORDER BY, LIMIT, aggregate functions, UNION, and subqueries. Allows building queries through a fluent interface without writing raw SQL, provides automatic value escaping, SQL injection protection via prepared statements, and automatic JOIN addition for related tables. The class is used via the `MySqlTable::query()` or `MySqlTable::all()` method and executes queries through the MySql owner.

### Key Features

* **Query building** of various types
* **Search condition management**
* **JOIN and UNION operations**
* **Aggregate functions**
* **Pagination and sorting**
* **Query parameterization**

## Properties

| Property | Type | Visibility | Description |
|----------|------|------------|-------------|
| **$table** | `MySqlTable` | `public` | Reference to the owner table. Set in constructor via `MySqlTable::query()`. Used to determine table name and fields during SQL generation. |
| **$owner** | `MySql` | `public` | Reference to the database connection object. Set in constructor. Used to execute prepared statements via `prepare()` and `result()`. |
| **$fields** | `array` | `public` | Array of fields for selection. Default `['*']`. Populated via `select()` or `fields()` methods. Fields are processed via `processField()` during SQL generation. |
| **$where** | `array` | `public` | Array of WHERE conditions. Populated via `where()`, `andWhere()`, `orWhere()`, `addCondition()` methods. Conditions are combined with operators from `$operators`. |
| **$params** | `array` | `public` | Array of parameters for prepared statements. Populated automatically when adding conditions via `parseOperator()`, `esc()`. Used during query execution via `$owner->prepare()`. |
| **$operators** | `array` | `public` | Array of logical operators for WHERE conditions (`AND`, `OR`). Default `['AND']`. Populated when adding conditions via `where()`, `addCondition()`. |
| **$order** | `array` | `public` | Array of ORDER BY clauses. Populated via `orderBy()` method. Each condition stored as string `"field ASC"` or `"field DESC"`. |
| **$group** | `array` | `public` | Array of GROUP BY fields. Populated via `groupBy()` method. Used during SQL generation for aggregate queries. |
| **$limit** | `string` | `public` | LIMIT clause. Set via `limit()` method. May contain one number or two numbers comma-separated (`"10, 20"`). |
| **$aggregates** | `array` | `public` | Array of aggregate functions. Populated via `aggregate()` method. Supported functions: SUM, MIN, MAX, AVG, STD, VARIANCE, GROUP_CONCAT, COUNT, DISTINCT, BIT_AND, BIT_OR, BIT_XOR, JSON_ARRAYAGG. |
| **$distinct** | `bool` | `public` | Flag for using DISTINCT in SELECT. Controlled via `distinct()` method. Ignored if aggregate functions are present. |
| **$joins** | `array` | `public` | Array of JOIN conditions. Populated via `join()`, `leftJoin()`, `rightJoin()`, `with()`, `autoJoinForField()` methods. Each element contains: `type`, `dbName`, `tableName`, `alias`, `on`. |
| **$union** | `array` | `public` | Array of UNION queries. Populated via `union()`, `unionAll()` methods. Each element contains: `builder` (QueryBuilder object), `type` (`UNION` or `UNION ALL`). |
| **$str_sql_query** | `string` | `public` | Last generated SQL query. Populated by `getSQL()` method. Used for debugging and logging. |
| **$get_query** | `bool` | `public` | Flag for returning SQL query instead of executing. Controlled via `getQuery()` method. If `true` — `all()`, `setall()` methods return SQL string. |
| **$asarray** | `bool` | `public` | Flag for result return format. If `true` — results returned as associative arrays, if `false` — as stdClass objects. |
| **$processedFields** | `array` | `private` | Cache of processed field names. Used by `processField()` method to prevent reprocessing identical fields. |
| **$joinedTables** | `array` | `private` | Cache of automatically added JOIN tables. Used by `autoJoinForField()` method to prevent JOIN duplication. |
| **$aggritems** | `static array` | `public static` | Static array of supported aggregate functions. Used for aggregation type validation in `aggregate()` method. |

## Methods

### Constructor

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **__construct** | `MySqlTable $table`, `MySql $owner` | — | Class constructor. Saves references to table and connection, initializes `$get_query` to `false`. Does not execute queries — only preparation for building. |

### Field Definition

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **select** | `mixed $fields` | `$this` | Sets the list of fields for selection. Accepts string (split by comma via `\LTS::explodestr()`) or array. Fields processed via `processField()`. Supports fluent interface. |
| **fields** | `mixed $fields` | `$this` | Alias for `select()`. Sets the list of fields for selection. Supports fluent interface. |

### Condition Definition

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **where** | `mixed $conditions`, `string $operator = 'AND'` | `$this` | Adds WHERE conditions. Supports: (1) String — added as-is in parentheses. (2) Array — iterates key-value pairs, calls `addCondition()`. Special keys: `'WHERE'` — raw condition, aggregate functions — ignored. Supports fluent interface. |
| **andWhere** | `mixed $conditions` | `$this` | Adds conditions with AND operator. Wrapper for `where($conditions, 'AND')`. Supports fluent interface. |
| **orWhere** | `mixed $conditions` | `$this` | Adds conditions with OR operator. Wrapper for `where($conditions, 'OR')`. Supports fluent interface. |
| **whereGroup** | `mixed $conditions`, `string $operator = 'AND'` | `$this` | Adds a group of conditions in parentheses. Creates temporary QueryBuilder, populates it with conditions, wraps result in parentheses. Supports callable (callback function) or array of conditions. Supports fluent interface. |
| **addCondition** | `string $name`, `mixed $value`, `string $operator` | — | Internal method for adding conditions. Checks `:left` (>=) and `:right` (<=) suffixes in field name. For `:right` adds time `23:59:59` if not specified. Calls `addWhereCondition()`. |

### Table Joins

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **with** | `string $relation` | `$this` | Automatically adds LEFT JOIN for related table. Finds field of type `'TABLE'` by name `$relation`, determines target table and adds JOIN on condition `{$this->table->name}.{$relation} = {$alias}.id`. Prevents duplication via `$joinedTables`. Supports fluent interface. |
| **join** | `mixed $table`, `string $on`, `string $type = 'INNER'`, `string $alias = null` | `$this` | Adds JOIN condition. `$table` can be MySqlTable object or table name. `$on` — join condition. `$type` — JOIN type (INNER, LEFT, RIGHT). `$alias` — table alias. Supports fluent interface. |
| **leftJoin** | `mixed $table`, `string $on`, `string $alias = null` | `$this` | Adds LEFT JOIN. Wrapper for `join()` with type `'LEFT'`. Supports fluent interface. |
| **rightJoin** | `mixed $table`, `string $on`, `string $alias = null` | `$this` | Adds RIGHT JOIN. Wrapper for `join()` with type `'RIGHT'`. Supports fluent interface. |

### Ordering and Grouping

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **orderBy** | `string $fields` | `$this` | Sets ORDER BY. Accepts string with comma-separated fields. Prefix `-` before field means DESC (e.g., `'-created_at'`). Fields processed via `processExpression()`. Supports fluent interface. |
| **groupBy** | `string $fields` | `$this` | Sets GROUP BY. Accepts string with comma-separated fields. Fields processed via `processExpression()`. Supports fluent interface. |
| **aggregate** | `string $type`, `string $fields` | `$this` | Adds aggregate function. `$type` — function name (SUM, MIN, MAX, COUNT, DISTINCT, AVG, STD, VARIANCE, GROUP_CONCAT, BIT_AND, BIT_OR, BIT_XOR, JSON_ARRAYAGG). `$fields` — comma-separated fields. Supports aliases via `AS` (`SUM(total) AS total_sum`). For `DISTINCT` generates `COUNT(DISTINCT field)`. Supports fluent interface. |
| **distinct** | `bool $enable = true` | `$this` | Enables/disables DISTINCT. Sets `$distinct`. Ignored if aggregate functions present. Supports fluent interface. |

### Nested Queries

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **subquery** | `callable $callback` | `QueryBuilder` | Creates new QueryBuilder for subquery. Calls `$callback($sub)` for configuration. Returns subquery object for use in `whereInSubquery()`, `whereEqualSubquery()`. |
| **whereInSubquery** | `string $field`, `callable $callback` | `$this` | Adds condition `field IN (subquery)`. Creates subquery via `subquery()`, wraps in `IN (...)`. Merges subquery parameters into `$params`. Supports fluent interface. |
| **whereEqualSubquery** | `string $field`, `callable $callback` | `$this` | Adds condition `field = (subquery)`. Creates subquery via `subquery()`, wraps in `= (...)`. Merges subquery parameters into `$params`. Supports fluent interface. |

### Limits

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **limit** | `mixed $limit` | `$this` | Sets LIMIT. Accepts number or string (`"10"`, `"10, 20"`). Saves to `$limit`. Supports fluent interface. |
| **first** | — | `mixed/false` | Returns first record. Sets `LIMIT 1`, calls `all()`, returns first array element or `false`. Convenient for getting single record without `get()`. |

### Control

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **getQuery** | — | `$this` | Sets `$get_query` flag to `true`. Subsequent `all()`, `setall()` calls will return SQL string instead of executing query. Supports fluent interface. |

### Query Building

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **all** | — | `array/false` | Executes SELECT query. Generates SQL via `getSQL('SELECT')`, gets parameters via `getParams()`, executes via `$owner->prepare()`, extracts results via `$owner->result()`. If `$get_query === true` — returns SQL string. Returns `false` on empty result or error. |
| **setall** | `array $data` | `bool` | Executes UPDATE query. Generates SQL via `getUpdateSQL()`, merges SET and WHERE parameters, executes via `$owner->prepare()`. If `$get_query === true` — outputs parameters and returns SQL. Returns `true` on success, `false` on error. |
| **getSQL** | `string $type = 'SELECT'` | `string` | Generates SQL query. Supports types: `'SELECT'`, `'DELETE'`. Forms: SELECT/DISTINCT/aggregates/fields, FROM, JOIN, WHERE, GROUP BY, ORDER BY, LIMIT, UNION. Saves to `$str_sql_query`. Returns SQL string. |
| **getUpdateSQL** | `array $data` | `string` | Generates UPDATE query. Forms: UPDATE, JOIN, SET (with `?` parameters), WHERE, ORDER BY, LIMIT. Does not execute query — only SQL generation. Used by `setall()` method. |
| **getParams** | — | `array` | Returns array of parameters for prepared statement. Used by external methods for query execution. |

### Private Methods

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **addRangeCondition** | `string $field`, `string $from`, `string $to` | — | Private method for adding range. Generates condition `field >= ? AND field <= ?`, adds two parameters to `$params`. Used for processing `day:`, `period:` prefixes. |
| **processField** | `string $f` | `string` | Private field processing method. Checks `$processedFields` cache. Processes `AS` aliases. Calls `processExpression()` for name qualification. Saves result to cache. |
| **processExpression** | `string $expr` | `string` | Private expression processing method. Automatically adds JOIN for fields with dot via `autoJoinForField()`. Adds backticks around table and field names. Qualifies field names via `{$this->table->name}.{$field}`. |
| **autoJoinForField** | `string $field` | — | Private automatic JOIN method. If field contains dot (`table.field`) — finds field of type `'TABLE'`, adds LEFT JOIN if not already added. Saves to `$joinedTables` to prevent duplication. |
| **quoteQualifiedField** | `string $field` | `string` | Private field qualification method. If dot present — returns `` `table`.`field` ``, otherwise `` `field` ``. Used during WHERE condition generation. |
| **addWhereCondition** | `string $field`, `string $op`, `mixed $value`, `string $operator` | — | Private condition addition method. Qualifies field, calls `autoJoinForField()`. Checks prefixes: `@` (raw SQL), `day:` (day range), `period:` (date range). Otherwise — calls `parseOperator()`. |
| **parseOperator** | `string $field`, `string $op`, `mixed $value` | `string/false` | Private operator parsing method. Supports: arrays (IN), two-character (`<=`, `>=`), one-character (`!`, `<`, `>`, `%`, `$`, `(`), empty string. Calls `esc()` for value escaping. Returns condition string or `false`. |
| **esc** | `mixed $v`, `int $stripPrefix = 0` | `string` | Private escaping method. If `$stripPrefix > 0` — removes prefix from string. Adds value to `$params`, returns `?` for prepared statement. |

## Special Value Prefixes in WHERE Conditions

| Prefix | Description | Example |
|--------|-------------|---------|
| **`!`** | Operator `!=` (not equal) | `'status' => '!active'` → `status != ?` |
| **`<`** | Operator `<` (less than) | `'age' => '<18'` → `age < ?` |
| **`>`** | Operator `>` (greater than) | `'price' => '>100'` → `price > ?` |
| **`<=`** | Operator `<=` (less than or equal) | `'count' => '<=10'` → `count <= ?` |
| **`>=`** | Operator `>=` (greater than or equal) | `'count' => '>=10'` → `count >= ?` |
| **`%`** | Operator `LIKE` (starts with) | `'name' => '%John'` → `name LIKE ?` |
| **`$`** | Operator `LIKE` (contains) | `'name' => '$John'` → `name LIKE '%John%'` |
| **`(`** | Operator `IN` (list) | `'id' => '(1,2,3)'` → `id IN (1,2,3)` |
| **`@`** | Raw SQL (no escaping) | `'date' => '@NOW()'` → `date = NOW()` |
| **`day:`** | Day range | `'created' => 'day:2024-01-15'` → `created >= '2024-01-15 00:00:00' AND created <= '2024-01-15 23:59:59'` |
| **`period:`** | Date range | `'created' => 'period:2024-01-01,2024-01-31'` → range between dates |
| **`:left`** | Suffix for `>=` | `'date:left' => '2024-01-01'` → `date >= ?` |
| **`:right`** | Suffix for `<=` | `'date:right' => '2024-01-31'` → `date <= ?` (automatically adds `23:59:59`) |

## Usage Examples

```php
// Creating builder
$query = new QueryBuilder($table, $db);

// Simple query
$result = $query->select(['id', 'name'])
    ->where(['active' => true])
    ->orderBy('name')
    ->limit(10)
    ->all();

// Query with JOIN
$result = $query->select(['users.name', 'roles.title'])
    ->join('roles', 'users.role_id = roles.id')
    ->where(['users.active' => true])
    ->all();
```

## Notes
*   Class is created via `MySqlTable::query()` — not recommended to create instances directly via `new QueryBuilder()`.
*   All query building methods return `$this` to support fluent interface and chained calls.
*   `with()` method automatically determines related tables by fields of type `'TABLE'` — no need to manually specify JOIN for foreign keys.
*   Automatic JOIN via `autoJoinForField()` prevents duplication — each join added only once via `$joinedTables` cache.
*   Prepared statements via `prepare()` protect against SQL injection — all values escaped via `esc()` and passed as parameters.
*   `$get_query` flag useful for debugging — allows viewing generated SQL before execution via `all()`, `setall()`.
*   `getSQL()` method supports two query types: `'SELECT'` (default) and `'DELETE'` — for UPDATE use `getUpdateSQL()`.
*   Aggregate functions defined via static array `$aggritems` — unknown functions ignored in `when()`.
*   For DISTINCT in aggregates use special syntax: `aggregate('DISTINCT', 'field')` generates `COUNT(DISTINCT field)`.
*   Subqueries via `subquery()` create independent QueryBuilder — parameters automatically merged into parent query.
*   `whereGroup()` method allows creating nested conditions in parentheses — useful for complex logic `(A AND B) OR (C AND D)`.
*   Value prefixes (`!`, `<`, `>`, `%`, `$`, `@`, `day:`, `period:`) simplify condition writing — no need to manually specify operators.
*   Raw SQL via `@` prefix disables escaping — use only for verified expressions (MySQL functions).
*   Date ranges via `day:` and `period:` automatically add time `00:00:00` and `23:59:59` — convenient for date filtering.
*   `:left` and `:right` suffixes in field names convert to `>=` and `<=` — convenient for ranges without explicit operators.
*   `first()` method sets `LIMIT 1` automatically — convenient for getting single record without `limit(1)->all()` chain.
*   Query parameters stored in `$params` — used during execution via `$owner->prepare($sql, $params)`.
*   `$processedFields` cache speeds up repeated field processing — each field processed only once per query.
*   `processExpression()` method adds backticks around names — protection against conflicts with MySQL reserved words.
*   For fields with aliases (`field AS alias`) `processField()` correctly processes both parts — alias not qualified with table name.
*   UNION queries via `union()` and `unionAll()` support chaining — can add multiple UNIONs to single query.
*   `setall()` method merges SET and WHERE parameters into single array — order important for prepared statement.
*   On execution error methods return `false` — requires checking before using result.
*   Class does not implement result caching — each `all()` call executes query to DB.
*   For optimizing large selections recommended to use `limit()` and `orderBy()` — prevents loading all records into memory.
*   `getUpdateSQL()` method does not execute query — only SQL generation for debugging or manual execution.
*   `$asarray` flag controls return format — can be set globally via `$owner->asarray` or locally in QueryBuilder.
*   When generating DELETE with JOIN uses special MySQL syntax: `DELETE table FROM table AS table JOIN ...`.
*   `join()` method supports MySqlTable objects — automatically extracts table name and database name from object.
*   For complex conditions recommended to use `whereGroup()` with callable — allows encapsulating grouping logic.
*   Class compatible with MySQL 5.7+ and MariaDB 10.0+ — uses standard SQL syntax.
*   When working with multiple databases need to pass correct `$owner` — each QueryBuilder bound to single connection.
*   `aggregate()` method supports aliases via `AS` — convenient for naming result columns.
*   For `*` fields in aggregates automatically substitutes `id` — `COUNT(*)` converted to `COUNT(id)`.
*   `orderBy()` method supports `-` prefix for DESC — no need to explicitly specify sort direction.
*   During SQL generation `getSQL()` method saves result to `$str_sql_query` — available for debugging after execution.
*   Class does not implement transactions — for operation atomicity use `$owner->transaction()`.
*   `all()` method returns `false` on empty result — not empty array, requires `empty()` or `=== false` check.
*   For mass updates recommended to use `setall()` with WHERE conditions — more efficient than multiple `update()` calls.
*   When using subqueries parameters automatically merged — no manual `$params` management required.
*   `quoteQualifiedField()` method ensures correct name escaping — protection against SQL injection via field names.
*   Class does not support named parameters — uses positional `?` parameters for prepared statements.
*   For working with MySQL JSON fields can use `JSON_ARRAYAGG` aggregate — supported in `$aggritems`.
*   `parseOperator()` method for arrays generates `IN (?, ?, ?)` — number of placeholders matches array size.
*   When generating UPDATE via `setall()` parameter order: first SET, then WHERE — important for correct binding.
*   Class does not implement soft delete — standard DELETE query used for deletion.
*   For debugging complex queries recommended to use `$query->getQuery()->all()` — returns SQL instead of executing.
*   All methods support method chaining via fluent interface pattern.
*   Field names with special characters or reserved words automatically wrapped in backticks for safety.

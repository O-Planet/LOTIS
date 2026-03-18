[English](MySqlField.md) | [Русский](MySqlField.ru.md)

🚀 [Quick Start](../QuickStart.md)

# MySqlField Class

## Purpose
A class representing a database table field in the LOTIS ORM framework. Designed for storing field metadata: name, type, size, nullable, default value, ENUM values, references to other tables. Used by the MySqlTable class to define table structure and generate SQL queries CREATE TABLE, ALTER TABLE. The class provides methods for configuring field properties via fluent interface and generating SQL field type definitions. MySqlField objects are created automatically when defining a table through MySqlTable methods (string(), int(), enum(), etc.) or when loading structure from DB via MySql::loadtable().

## Properties

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$name** | `string` | `public` | Field name in the table. Set in constructor. Used as key in `$table->fields` array. |
| **$type** | `string` | `public` | Field type (VARCHAR, INT, TEXT, ENUM, DECIMAL, DATETIME, TABLE, FILE, BOOL, etc.). Set in constructor. Determines SQL type and field behavior. |
| **$param** | `mixed/null` | `public` | Field type parameter. For VARCHAR/CHAR — length (int). For DECIMAL — array `['dd' => x, 'dr' => y]`. For FILE — array `['ext' => ..., 'dir' => ...]`. For other types — `null`. |
| **$table** | `string/MySqlTable/null` | `public` | Reference to table for fields of type `'TABLE'`. Can be table name (string) or MySqlTable object. Used for defining foreign keys. |
| **$value** | `mixed/null` | `public` | Current field value for insert/update. Set via `setvalue()`. Used when generating INSERT/UPDATE queries. |
| **$values** | `array/null` | `public` | Allowed values for ENUM type. Array of strings or associative array (key => value). Used for SQL generation and validation. |
| **$isset** | `bool` | `public` | Value set flag. Set to `true` when `setvalue()` is called. Used to determine which fields to include in INSERT/UPDATE. |
| **$default** | `mixed/null` | `public` | Default value for the field. Set directly or via MySqlTable. Used when generating CREATE TABLE. |
| **$nullable** | `bool/null` | `public` | NULL value allowed flag. `true` — NULL allowed, `false` — NOT NULL, `null` — not defined. Controlled by `nullable()` method. |
| **$out** | `mixed/null` | `public` | Additional field for user data. Not used in base ORM functionality. Can be used for extension. Intended for interpretation of received data. |

## Methods

### Constructor

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **__construct** | `string $name`, `string $type`, `mixed $param = null` | — | Class constructor. Saves field name and type. For VARCHAR/CHAR `$param` is length (int). For DECIMAL `$param` is array `['dd' => x, 'dr' => y]`. For FILE `$param` is array with storage settings. Initializes all properties to default values (`null`, `false`). |

### Value Setting

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **setvalue** | `mixed $val` | `$this` | Sets field value for subsequent insert/update. Saves to `$value` and sets `$isset` flag to `true`. Returns `$this` to support fluent interface. Called via `MySqlTable::value()` or directly. |
| **nullable** | `bool $val = true` | `$this` | Sets `$nullable` flag. If `$val === true` — field can accept NULL. If `false` — NOT NULL. Returns `$this` to support fluent interface. Called when defining table structure. |

### Information Retrieval

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **getnull** | — | `string` | Returns SQL representation of nullable flag. If `$nullable === null` — returns empty string. If `true` — `'NULL'`. If `false` — `'NOT NULL'`. Used when generating CREATE TABLE. |
| **gettype** | — | `string` | Generates SQL type definition for CREATE TABLE query. Returns string like `VARCHAR(100)`, `INT(11)`, `DECIMAL(15,2)`, `ENUM('val1','val2')`, `TINYINT(1)`. For type `'TABLE'` returns `'INT(11)'` (foreign key). For type `'FILE'` returns `'VARCHAR(255)'`. |

## Usage Examples

```php
$table->field('name')->nullable(false);
```

## Notes
*   The class is created automatically via MySqlTable methods (`string()`, `int()`, `enum()`, `table()`, etc.) — direct creation via `new MySqlField()` is not recommended.
*   All configuration methods return `$this` to support fluent interface and chaining.
*   Type `'TABLE'` denotes a foreign reference — when generating SQL creates an INT(11) field with `_id` suffix.
*   Method `gettype()` is used when creating table — generates correct field definition for MySQL.
*   For ENUM fields, `$values` property contains array of allowed values — used for validation and UI generation via `MySqlTable::generateinputs()`.
*   `$nullable` flag affects SQL generation — adds `NULL` or `NOT NULL` to field definition via `getnull()`.
*   Default value `$default` is not escaped in this class — escaping happens in MySqlTable::create().
*   `$isset` flag determines whether field is included in INSERT/UPDATE queries — only fields with `$isset === true` are processed.
*   For DECIMAL fields, `$param` property stores two parameters — `dd` (digits before decimal) and `dr` (digits after).
*   When loading structure via `MySql::loadtable()`, MySqlField objects are created automatically based on `information_schema.COLUMNS`.
*   `$table` property for reference fields can contain table name or object — name is used when generating SQL.
*   Method `setvalue()` is called via `MySqlTable::value()` — direct usage is not recommended.
*   Field types correspond to MySQL — VARCHAR, INT, TEXT, DECIMAL, DATETIME, ENUM, TINYINT, FILE, TABLE, POINT.
*   For boolean fields, type `'BOOL'` is used — converted to `TINYINT(1)` when generating SQL.
*   Method `gettype()` does not include field name — only type definition and modifiers.
*   When validating ENUM, check is performed against `$values` — case matters.
*   For TABLE fields, validation requires DB connection — checks record existence by ID.
*   MySqlField objects are cached in `$table->fields` — subsequent accesses return the same object.
*   When changing table structure, fields need to be recreated — cache is not updated automatically.
*   Method `nullable()` can be called multiple times — last value overrides previous.
*   For TEXT and BLOB fields, length is not specified — `$param` property is ignored in `gettype()`.
*   Default value `CURRENT_TIMESTAMP` for DATETIME is supported — passed as string in `$default`.
*   When generating SQL for DECIMAL, format `DECIMAL(dd, dr)` is used.
*   For ENUM, values are escaped when generating SQL — single quotes inside values are doubled.
*   The class doesn't implement custom types — only standard MySQL types.
*   `$name` property is used as key in `$table->fields` — field names are unique within table.
*   When loading from DB, field comments (`COLUMN_COMMENT`) are ignored — not saved in object.
*   For auto-increment fields there is no separate type — determined via EXTRA field in `information_schema`.
*   The class doesn't implement data validation — responsibility lies with developer or UI components.
*   When generating INSERT/UPDATE, only fields with `$isset === true` are included in query.
*   For FILE fields, `$param` can contain directory for file storage — checked in MySqlTable::file().
*   The class is compatible with MySQL 5.7+ and MariaDB 10.0+.
*   MySqlField objects have no methods for direct DB operations — all operations are performed via MySqlTable.
*   When loading table structure, `id` field is ignored — considered default primary key.
*   For reference fields, type `'TABLE'` is converted to `INT(11)` when generating SQL — foreign key is not created automatically.
*   For ENUM, associative arrays are converted to value list — keys are used as values in SQL.
*   The class doesn't support changing field type after creation — for modification use MySqlTable::update().
*   `$value` property is reset after INSERT/UPDATE via `MySqlTable::freevalues()` — `$isset` flag is set to `false`.

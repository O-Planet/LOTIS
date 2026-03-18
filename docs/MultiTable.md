[English](MultiTable.md) | [Русский](MultiTable.ru.md)

🚀 [Quick Start](../QuickStart.md)

# MultiTable Class

## Purpose
A class for managing documents with a "header-details" structure in the LOTIS framework. Designed for working with documents consisting of one header table and multiple subordinate detail tables. Provides a unified interface for reading, saving, and deleting complete documents including all tabular parts. Automatically manages relationships between header and details through link fields (e.g., `parent_{table}`), supports save strategies (replace/merge), and allows customization of selection conditions for each tabular part.

## Properties

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$header** | `MySqlTable/null` | `private` | Database table for document header. Set via constructor or `header()` method. Used for operations with document header. |
| **$details** | `array` | `private` | Array of detail table configurations. Each element contains: `table` (MySqlTable object), `field` (link field), `alias` (alias), `conditions` (selection conditions). Populated via `add()` method. |

## Methods

### Constructor

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **__construct** | `MySqlTable $header = null` | — | Class constructor. Calls `header($header)` to set header table. Initializes `$details` as empty array. |

### Initialization

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **header** | `MySqlTable $header` | — | Sets document header table. Saves to `$this->header`. Used for all operations with document header. |
| **add** | `MySqlTable $detailTable`, `string $name = null` | `$this` | Adds subordinate table to document. If `$name === null` — uses table name as alias. Determines link field as `"parent_{$header->name}"`. Saves configuration to `$details[$name]`. Supports fluent interface. |

### Configuring Relationships Between Master and Detail Tables

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **bindField** | `string|MySqlTable $name`, `string $field` | `$this` | Sets link field for detail table. If `$field === null` — link will be through conditions only. Checks detail existence via `resolveDetailName()`. Updates `$details[$detailName]['field']`. Supports fluent interface. |
| **bindCondition** | `string|MySqlTable $name`, `mixed $condition` | `$this` | Adds selection condition to detail table. Accepts string or array of conditions. Adds to `$details[$detailName]['conditions'][]`. Supports fluent interface. |
| **getHeader** | — | `MySqlTable` | Returns header table object. Direct getter for `$this->header`. |
| **getDetails** | — | `array` | Returns array of all detail tables. Direct getter for `$this->details`. |
| **getDetail** | `string $name` | `array/null` | Returns configuration of specific detail table by name. Returns `null` if not found. |
| **hasDetail** | `string $name` | `bool` | Checks if detail table exists by name. Uses `resolveDetailName()` and checks `isset($this->details[$detailName])`. |
| **removeDetail** | `string $name` | `$this` | Removes detail table from document. Uses `resolveDetailName()` and `unset($this->details[$detailName])`. Supports fluent interface. |

### Data Retrieval and Writing

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **get** | `int $headerId` | `array/false` | Gets document by header ID. Calls `all($headerId)`. Returns array `['header' => obj, 'details' => [name => [...]]]` or `false` if not found. |
| **all** | `int $headerId`, `array $customConditions = []` | `array/false` | Gets document with ability to customize conditions. Reads header via `$this->header->get()`. For each detail creates QueryBuilder, applies base conditions, link field, and custom conditions. Returns array with header and details or `false` if header not found. |
| **del** | `int $headerId` | `bool` | Deletes document and all detail tables. For each detail calls `delall()` with link conditions. Then deletes header via `$this->header->del()`. Returns `true` on success, `false` on error. |
| **save** | `array $data`, `string $saveMode = 'replace'` | `array/false` | Saves document and returns enriched structure with IDs. `$data` contains `['header' => [...], 'details' => [name => [...]]]`. `$saveMode`: `'replace'` — deletes missing rows, `'merge'` — only updates/adds. Saves header via `insert()/set()`, then details via `set()` with binding to header ID. Returns `['header' => [...], 'details' => [...]]` or `false` on error. |
| **query** | `string|MySqlTable $name` | `QueryBuilder` | Returns QueryBuilder for detail table with link conditions already applied. Does not automatically add link field — allows user to configure query. Throws exception if detail not found. |

### Private Methods

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **resolveDetailName** | `string|MySqlTable $name` | `string` | Private method for converting detail name. If MySqlTable object passed — searches by reference in `$details`, otherwise returns table name. If not found — uses `$name->name`. Returns string detail name. |
| **applyDetailConditions** | `QueryBuilder $query`, `array $detail`, `int $headerId` | — | Private method for applying conditions to detail QueryBuilder. Applies base conditions from `$detail['conditions']` and link field `$detail['field'] = $headerId`. Used in `del()` and `save()`. |

## Notes
*   The class doesn't inherit from `Construct` — it's a functional class without UI object lifecycle.
*   **Document Structure:** Document consists of one header and multiple details — each detail is a separate DB table.
*   **Link Field:** By default, link between header and detail is defined as `"parent_{header->name}"` — e.g., `parent_orders` for orders table.
*   **Save Modes:** `'replace'` — deletes detail rows missing from input data, `'merge'` — only updates existing and adds new.
*   **Custom Conditions:** Via `bindCondition()` you can add permanent selection conditions for each detail — applied to all read/write operations.
*   **Reading Document:** Method `all()` returns array with keys `'header'` (header object) and `'details'` (array of row arrays by detail name).
*   **Deleting Document:** Method `del()` first deletes all detail rows by link condition, then deletes header — ensures cascade deletion.
*   **Saving Header:** If `$data['header']` has `id` — `set()` is called, otherwise — `insert()` with new ID return.
*   **Saving Details:** For each detail row, link field is set to header ID — ensures correct binding.
*   **Deleting Detail Rows:** In `'replace'` mode, IDs of rows existing in DB but missing from input data are calculated — deleted via `delall()`.
*   **QueryBuilder for Detail:** Method `query()` returns QueryBuilder with base conditions applied — link field not added automatically for flexibility.
*   **Detail Identification:** Via `resolveDetailName()` — supports passing name as string or MySqlTable object.
*   **Data Array:** Input data for `save()` must contain `['header' => [...], 'details' => [name => [...]]]` — structure matches `all()` result.
*   **Return from save():** Returns enriched structure with actual IDs of all saved rows — convenient for subsequent work.
*   **Error Handling:** Returns `false` on DB connection error, failed header save, or missing header table.
*   **Delete by ID:** Method `del()` accepts only numeric header ID — for deletion by other conditions use QueryBuilder directly.
*   **Read by ID:** Method `get()` is a wrapper around `all()` without custom conditions — for simple document retrieval.
*   **Existence Check:** Method `hasDetail()` returns `true` if detail was added via `add()` — doesn't check actual DB existence.
*   **Removing Detail:** Method `removeDetail()` removes detail from configuration — doesn't delete data from DB.
*   **Field Binding:** Method `bindField()` allows overriding default link field — useful for non-standard DB schemas.
*   **Conditions in save():** Custom conditions from `bindCondition()` are applied when reading old rows to determine records to delete.
*   **Detail Row IDs:** In `$data['details'][name][]` each row can contain `id` — if present — updated, otherwise — new row inserted.
*   **Special Keys:** Key `ltsDELETED` in detail row marks row as deleted — skipped during save.
*   **QueryBuilder Return:** Method `query()` allows building complex queries to detail with JOIN, GROUP BY, etc. — without header binding.
*   **Getters:** Methods `getHeader()`, `getDetails()`, `getDetail()` provide direct access to internal structure — for debugging and extension.
*   **Constructor:** Accepts optional header table — can be set later via `header()`.
*   **Fluent Interface:** Methods `add()`, `bindField()`, `bindCondition()`, `removeDetail()` return `$this` for chaining.
*   **Private Methods:** `resolveDetailName()` and `applyDetailConditions()` are used internally — not intended for direct calls.
*   **Parameter Types:** Methods accept `string|MySqlTable` for detail name — automatically convert to string name.
*   **Conditions Array:** In `$details[$name]['conditions']` conditions are stored — applied via `QueryBuilder::where()` or `addCondition()`.
*   **ORM Relationship:** Uses `MySqlTable`, `QueryBuilder` classes — all operations performed through ORM framework.
*   **Transactions:** For atomicity, wrap `save()` and `del()` in `$header->owner->transaction()`.
*   **Data Validation:** Class doesn't validate input data — responsibility lies with developer before calling `save()`.
*   **Caching:** Doesn't cache data — each `all()` call executes DB query.
*   **Performance:** For large documents, use `FIELDS` in conditions to limit selected fields.
*   **Inheritance:** Class doesn't support inheritance — use composition or wrappers for extension.
*   **Static Methods:** Contains no static methods — requires instance creation to work.
*   **Dependencies:** Requires connected DB via `MySql` — all tables must have `$owner`.
*   **Naming:** Recommended to use standard link field naming `parent_{table}` for compatibility.
*   **Multiple Details:** Supports unlimited number of subordinate tables — each added via `add()`.
*   **Aliases:** Detail alias is used as key in `$details` and returned data — must be unique.
*   **Null Link Field:** If `$detail['field'] === null` — link defined only through `conditions` — for complex schemas.
*   **Return from all():** If header not found — returns `false`, if details empty — returns empty arrays in `'details'`.
*   **QueryBuilder Errors:** Method `query()` throws `InvalidArgumentException` if detail not found — requires check via `hasDetail()`.
*   **Save without details:** Can save header only without details — `$data['details']` can be empty or absent.
*   **Delete without details:** Method `del()` deletes details even if not passed in input data — by link condition.
*   **Merge Mode:** In `'merge'` mode detail rows are not deleted — only existing updated and new added.
*   **Replace Mode:** In `'replace'` mode rows not in input data are deleted — for full synchronization.
*   **ID from insert():** After header insert, new ID is used to bind all detail rows — ensures integrity.
*   **freevalues():** After save, `$table->freevalues()` is called — resets `$isset` flags for fields.
*   **Old Rows Array:** In `save()` old detail rows are loaded to determine deletable records — via `$this->dbtable->all($conditions)`.
*   **Delids Array:** Calculated via `array_diff($oldids, $myids)` — IDs existing in DB but missing from input data.
*   **Save Loop:** For each detail row `set($rowid)` is called — if `rowid` empty — `insert()`.
*   **True/False Return:** Method `save()` returns data array on success, `false` on error — requires check.
*   **Return from del():** Returns `true` if all operations successful, `false` on header or detail deletion error.
*   **Getter getDetail():** Returns configuration array or `null` — for checking existence and getting detail settings.
*   **Details Array:** Structure: `[$name => ['table' => ..., 'field' => ..., 'alias' => ..., 'conditions' => [...]]]`.
*   **Default Name:** If `$name === null` in `add()` — uses `$detailTable->name` as alias.
*   **Owner Check:** Methods check `$this->header->owner !== null` — requires connected DB to work.
*   **DB Connection:** If `$this->owner->link === null` — `connect()` is called — automatic connection on first query.
*   **Null Handling:** If `$data['header']` not set but `$data['headerid']` exists — uses existing ID for update.
*   **Special Fields:** `id` field in header processed separately — not passed to `value()` for insert.
*   **Rows Array:** In `save()` `$rows = $detailsData[$name]` — array of rows for specific detail from input data.
*   **ltsDataId Field:** Special field for client-side binding — not saved to DB, used for identification.
*   **SavedRows Return:** In `save()` for each detail returns array of saved rows with actual IDs.
*   **is_numeric Check:** In `get()` and `exists()` `is_numeric($id)` is checked — for SQL injection protection.
*   **LIMIT 1:** In `get()` and `exists()` `LIMIT 1` is used — for DB query optimization.
*   **prepare/execute:** All queries use prepared statements — SQL injection protection via `bind_param()`.
*   **fetch_object:** Results read via `fetch_object()` — returned as stdClass with `->property` access.
*   **stmt->close():** After query execution `$stmt->close()` is called — to free DB resources.

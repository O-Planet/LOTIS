[English](DataTable.md) | [Русский](DataTable.ru.md)

🚀 [Quick Start](../QuickStart.md)

# DataTable Class

## Purpose
Interactive data table representation class of the LOTIS framework, inheriting from `Element`. Designed for creating dynamic tables with support for sorting, filtering, cell editing, adding/deleting rows, and synchronizing data between server and client. Consists of a server-side part (DataTable.php) for defining structure and generating metadata, and a client-side part (DataTable.js) for managing the table in the browser. The class automatically generates JavaScript methods for working with the table via `LTS.get('{id}')`, supports hidden fields, customization of display via hooks, and integration with editing forms.

## Properties (Server Side, PHP)

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$head** | `array` | `public` | Associative array of column headers. Keys are field names, values are displayed titles. Set via `head()`. |
| **$rows** | `array` | `public` | Array of table row data. Each element is an associative array or object with row data. Set via `data()` or `append()`. |
| **$hidden** | `array` | `public` | Array of hidden field names. These fields are not displayed in the table but are saved in row data attributes for access on the client. Set via `hidden()`. |
| **$sorted** | `mixed` | `public` | Fields for sorting. Can be a string (field name) or an array of strings. Set via `sort()`. |
| **$norows** | `bool` | `public` | Flag for deferred row rendering. If `true` — the table is created without rows on load, data is added later via client methods. Default `true`. |
| **$out** | `string` | `public` | JavaScript hook code for customizing a row. Called when creating each table row. Set via `out()`. |

### Inherited Properties

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$id** | `string` | `public` | Inherited from `Construct`. Unique table identifier, used to link with the client object in `ltsDataTable`. |
| **$tagname** | `string` | `public` | Inherited from `Element`. Set to `'table'` in the constructor. |
| **$classname** | `string` | `public` | Inherited from `Element`. Automatically set to `'data-table'` in the constructor. |

## Methods

### Constructor

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **__construct** | `string $id = ''` | — | Class constructor. Calls parent `Element` constructor, sets `classname` to `'data-table'`, `tagname` to `'table'`, `norows` to `true`, `out` to an empty string. |

### Table Configuration

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **head** | `array $head` | `$this` | Sets column headers. Accepts an associative array (key is field name, value is header). Saves in `$head`. Used when generating `<thead>`. Supports fluent interface. |
| **data** | `array $rows` | `$this` | Sets table row data. Accepts an array of arrays or objects. Saves in `$rows`. Encoded to JSON during compilation and passed to the client. Supports fluent interface. |
| **hidden** | `string $hidden` | `$this` | Sets the list of hidden fields. Accepts a string with field names separated by commas. Splits via `\LTS::explodestr()`, saves in `$hidden`. Fields will be saved in row data attributes but not displayed in cells. Supports fluent interface. |
| **add** | `mixed $name`, `mixed $value = null` | `$this` | Overridden data addition method. Supports three modes: (1) If both parameters are arrays with numeric keys — first as `$head`, second as `$rows`. (2) If one array with numeric keys — as `$rows`. (3) Otherwise — calls parent `Element::add()`. Determines array type via `isArrayOfArraysWithNumericKeys()`. Supports fluent interface. |
| **append** | `array $values` | `$this` | Adds one row to the `$rows` array. Accepts an associative array of row data. Used for dynamically adding data on the server before rendering. Supports fluent interface. |
| **sort** | `string $name` | `$this` | Sets fields for sorting. Accepts a field name or an array of names. Saves in `$sorted`. Generates JS code for automatic sorting on load during compilation. Supports fluent interface. |
| **out** | `string $func` | `$this` | Sets a JavaScript hook for customizing a row. If the code does not start with `function` — wraps it in `function (row, data) { ... }`. The hook is called when creating each row, allowing modification of the DOM or adding handlers. Supports fluent interface. |

### Integration with Row Editing Form

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **rowclick** | `Form $form` | `$this` | Binds opening an edit form to a row click. Generates the client method `rowclick(row)` which: (1) Extracts `ltsDataId` from the row. (2) When clicking on the selection column — toggles the `sel` flag. (3) When clicking on other columns — loads row data into the form via `LTS({form}).values()`, saves `ltsDataId` in the form attribute, sends signal `{id}_RowClick`. Supports the `loadrowclick` hook for data preprocessing. |

### Additional Methods

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **isArrayOfArraysWithNumericKeys** | `array $array` | `bool` | Static helper method. Checks if an array is an array of arrays/objects with numeric keys. Used in `add()` to determine the data addition mode. |
| **compile** | — | — | Generation of client-side JavaScript methods. Encodes `$head`, `$rows`, `$hidden` to JSON. Calls `compilemethod()` to create methods: `ltsDataTable.add()`, `head()`, `attr()`, `rows()`, `Row()`, `findRows()`, `sort()`, `create()`, `values()`, `clear()`, `append()`, `filter()`, `fieldvalues()`, `out()`. If `$sorted` is set — adds JS sorting code in the `'ready'` area. If `$out` is set — adds the hook. At the end calls `ltsDataTable.create()` for table rendering. Calls `parent::compile()`. |

## Client Methods Available via LTS Object

These methods become available on the client via `LTS.get('{id}')` or via LTS(var), where var is a variable referencing an instance of the DataTable class. The actual logic is executed by the `ltsDataTable` object.

### Table Creation

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **LTS(table).head(data)** | `array data` | `array` | Sets or gets column headers. If `data` is passed — updates `ltsDataTable.heads[id]`. Otherwise — returns current headers. |
| **LTS(table).attr(data)** | `array data` | `array` | Sets or gets the list of hidden fields. If `data` is passed — updates `ltsDataTable.hidden[id]`. Otherwise — returns the current list. |
| **LTS(table).rows(data)** | `array data` | `array` | Sets or gets data of all rows. If `data` is passed — creates a deep copy via `ltsDataTable.deepCopyArray()` and saves in `ltsDataTable.rows[id]`. Otherwise — returns current data. |
| **LTS(table).create(data)** | `array data` | — | Creates the table in the DOM. If `data` is passed — sets the rows. Otherwise — clears `ltsDataTable.rows[id]`. Calls `ltsDataTable.create(id)` to generate `<thead>` and `<tbody>`. |

### Working with Table Rows

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **LTS(table).values(attr, values)** | `mixed attr`, `object values` | `array` | Gets or updates row values. Supports 7 modes: (1) Without parameters — returns all rows. (2) HTML element — filters by `data-id`, updates if `values` is passed. (3) jQuery object — filters by `data-id`, updates if `values` is passed. (4) Array of objects — filters by `ltsDataId`, updates if `values` is passed. (5) Object `{field: value}` — filters by field values, updates if `values` is passed. (6) Only `values` — updates all rows. (7) Only `attr` — returns filtered rows. When updating, modifies cells by classes `.Column_{field}` and hidden attributes. |
| **LTS(table).out(f)** | `function f` | — | Sets a hook for customizing rows. Saves the function in `ltsDataTable.hucks[id]`. The hook is called when creating each row with parameters `($row, $data)`. |
| **LTS(table).clear(attr)** | `mixed attr` | — | Deletes rows from the table. If `attr` is not passed — deletes all rows, clears `ltsDataTable.rows[id]` and `dataids[id]`. Otherwise — filters rows for deletion (HTML element, jQuery, array of objects, object-filter), deletes from `ltsDataTable.rows[id]` and from the DOM. |
| **LTS(table).append(values)** | `array values` | — | Adds new rows to the table. Accepts an array of row objects. For each row creates a new `ltsDataId`, adds to `ltsDataTable.rows[id]`, generates HTML `<tr>` and adds to `<tbody>`. Calls the `hucks[id]` hook if set. |
| **LTS(table).sort(name, napr)** | `mixed name`, `int napr` | — | Sorts the table. If `name` is an array — sets sorting settings in `ltsDataTable.sorted[id]`. If a string — sorts by field with direction `napr` (1 = ASC, -1 = DESC). Determines data type (int, float, date, string) from the `data-type` attribute of the header. Re-sorts `ltsDataTable.rows[id]` and updates the DOM via `reorderTbody()`. |
| **LTS(table).filter(values)** | `object values` | — | Filters row display. Accepts an object of filter rules `{field: value}`. Calls `ltsDataTable.filterAndApply()` — hides rows that failed the filter via `display: none`, shows those that passed. Empty filter shows all rows. |
| **LTS(table).fieldvalues(field, selectedrows)** | `string field`, `mixed selectedrows` | `array` | Returns unique values of a field from selected rows. If `selectedrows` is not passed — from all rows. `selectedrows` can be: an array of objects, a jQuery object, an HTML element. Extracts values, removes duplicates via `Set`, returns an array. |

### Integration with jQuery Object

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **LTS(table).Row(id)** | `mixed id` | `jQuery` | Returns a jQuery object of a row by ID. `id` can be a number (`ltsDataId`) or a row object (extracts `ltsDataId`). Selector: `#{id} tbody tr[data-id="{id}"]`. |
| **LTS(table).findRows(attr)** | `mixed attr` | `jQuery` | Finds rows by filter. Supports: (1) HTML element `tr` — returns one row. (2) jQuery object — filters by `data-id`. (3) Array of objects — filters by `ltsDataId`. (4) Object `{field: value}` — filters by field values. Returns a jQuery object of found rows. |

### Integration with Row Editing Form

When integrating with the `Form` object, it is necessary to add the `ltsDataId` field (type hidden) to the form to store the internal reference to the row in the ltsDataTable row storage. Also, if editing data related to a record from the `MySqlTable` database table, you need to add the id field (type hidden) to the form to store the record id.

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **LTS(table).rowclick(row)** | `HTMLElement row` | — | Row click handler (generated by the `rowclick()` method). Extracts `ltsDataId`, when clicking on the selection column — toggles `sel`, when clicking on other columns — loads data into the form, saves `ltsDataId` in the form attribute, sends signal `{id}_RowClick`. |

## Special Classes and Attributes

| Class/Attribute | Description |
|---------------|----------|
| **`.data-table`** | Base table class — added automatically. |
| **`.Column_{fieldname}`** | Cell and header class — used to access the column. |
| **`.ltsSorted`** | Sortable header class — adds a click handler for sorting. |
| **`.ltsEditable`** | Editable cell class — adds inline editing on click. |
| **`.ltsRowDelbutton`** | Row delete button class — calls `ltsDataTable.clear()` or a custom handler. |
| **`data-id`** | Row attribute — stores `ltsDataId` for identification. |
| **`data-field`** | Cell attribute — stores the field name. |
| **`data-type`** | Header attribute — stores the data type for sorting (int, float, date, string). |
| **`data-sort-order`** | Header attribute — stores the sort direction (1 = ASC, -1 = DESC). |
| **`ltsDataId`** | Row property — unique row ID in the client storage. |

## ltsDataTable Object (Client Side, JS)

The object is universal for storing data for all created tables. It defines methods for working with tables that are used by LTS table objects.

### Global ltsDataTable Properties

| Property | Type | Description |
|----------|-----|----------|
| **rows** | `object` | Row data storage by table ID. `rows[id]` — array of row objects. |
| **heads** | `object` | Column header storage by table ID. `heads[id]` — associative array. |
| **hidden** | `object` | Hidden field storage by table ID. `hidden[id]` — array of field names. |
| **sorted** | `object` | Sorting settings storage by table ID. `sorted[id]` — array of fields with types. |
| **hucks** | `object` | Row customization hooks storage. `hucks[id]` — function. |
| **dataids** | `object` | Row ID counters by tables. `dataids[id]` — last assigned `ltsDataId`. |

### Global ltsDataTable Methods

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **ltsDataTable.add(id, head, rows, hidden)** | `string id`, `array head`, `array rows`, `array hidden` | — | Registers a table in storage. Fills `heads[id]`, `rows[id]`, `hidden[id]`, `sorted[id]`, `hucks[id]`, `dataids[id]`. Called automatically during compilation. |
| **ltsDataTable.create(id, norows)** | `string id`, `bool norows` | — | Creates an HTML table in the DOM. Generates `<thead>` with headers, `<tbody>` with rows. Adds classes `Column_{fieldname}` to cells, `ltsSorted` to sortable headers, `ltsEditable` to editable cells. Calls the `hucks[id]` hook for each row. |
| **ltsDataTable.append(id, dataObject)** | `string id`, `array dataObject` | — | Adds rows to an existing table. Creates deep copies via `deepCopyArray()`, assigns `ltsDataId`, adds to `rows[id]` and to the DOM. |
| **ltsDataTable.lastdataid(id)** | `string id` | `int/false` | Returns the last assigned `ltsDataId` for the table. |
| **ltsDataTable.sort(id, name, direction)** | `string id`, `string name`, `int direction` | — | Sorts table rows by field. Determines data type from the `data-type` attribute, parses values (int, float, date, string), sorts the `rows[id]` array, updates the DOM via `reorderTbody()`. |
| **ltsDataTable.reorderTbody(id)** | `string id` | — | Rebuilds `<tbody>` according to the order of rows in `rows[id]`. Saves rows in a map by `data-id`, clears `<tbody>`, restores in the new order. |
| **ltsDataTable.deepCopyArray(originalArray)** | `array originalArray` | `array` | Creates a deep copy of an array. Uses `structuredClone()` if available, otherwise `jQuery.extend(true, ...)`, otherwise `JSON.parse(JSON.stringify(...))`. |
| **ltsDataTable.filter(id, rules)** | `string id`, `object rules` | `array` | Filters rows by rules. Returns an array of rows that passed the filter. Supports: empty strings (always true), RegExp, dates, substrings, numbers, strict comparison. |
| **ltsDataTable.applyFilter(id, filtered)** | `string id`, `array filtered` | — | Applies the filter to the DOM. Shows rows from `filtered` via `show()`, hides others via `hide()`. |
| **ltsDataTable.filterAndApply(id, rules)** | `string id`, `object rules` | — | Combines filtering and application. If the filter is empty — calls `clearFilter()`. Otherwise — filters and applies. |
| **ltsDataTable.clearFilter(id)** | `string id` | — | Resets the filter — shows all table rows. |

## Data Loading and Sorting

If row sorting is enabled in the table columns, then when using `DataSync`, manual processing of received data is required, since sorting is performed exclusively on the array of data currently loaded in the `ltsDataTable` row storage for the current table. Therefore, when changing the sort, it is necessary to (1) reset the current loading position, (2) change the client sorting condition, (3) clear the current table, (4) when receiving the next array of data, add it to the table.

## Usage Examples

```php
// Creating a basic table
$table = LTS::DataTable()
    ->head(['name' => 'Name', 'date' => 'Date'])
    ->data([
        ['id' => 1, 'name' => 'Item 1', 'date' => '2023-01-01'],
        ['id' => 2, 'name' => 'Item 2', 'date' => '2023-02-01']
    ])
    ->hidden(['id']) // Hide the ID field
    ->sort('name');  // Sort by name

// Configuring row click handling
$table->rowclick($form);

// Adding new rows
$table->append([
    ['id' => 3, 'name' => 'Item 3', 'date' => '2023-03-01']
]);
```

### Example of working with the table on the client

```JS
// Getting table rows by filter conditions
const rows = LTS(table).values({ date: '2023-02-01'});

// Setting values in rows by filter conditions
LTS(table).values({ date: '2023-02-01'}, { date: '2023-02-05'});

// Adding rows
LTS(table).append([{id: 3, name: 'Item 3', date: '2026-02-05'}]);

// Getting the jQuery object corresponding to the table row
const jQueryRow = LTS(table).Row(rows[0]);

// Getting jQuery objects corresponding to table rows satisfying filter conditions
const jQueryRows = LTS(table).findRows({ date: '2026-02-05' });

// Getting the table row from storage by the jQuery object corresponding to it
const row = LTS(table).values(jQueryRow)[0];

// Changing the sort
LTS(table).sort('name', -1);

// Deleting rows by filter conditions
LTS(table).clear({ date: '2026-02-05' });

// Deleting a row by its jQuery object
LTS(table).clear(jQueryRow);

// Deleting a row by its internal identifier
LTS(table).clear({ ltsDataId: rows[0].ltsDataId });

// Creating and filling the table on the client with clearing previous data
LTS(table).create([{id: 4, name: 'Item 4', date: '2026-02-06'}, {id: 5, name: 'Item 5', date: '2026-02-07'}]);

// Show only those rows that match filter conditions
LTS(table).filter({date: '2026-02-06'});

// Passing row data for editing
const row = LTS(table).values({ id: 4})[0];
LTS(myform).values(row);

// Updating the table row after editing
LTS(table).values(row, LTS(myform).values());
```

## Notes
*   Class extends `Element`, therefore inherits all methods for managing children, styles, attributes, and events.
*   Class extends `Quark` and `Construct`, therefore supports full object lifecycle: `compile()`, `shine()`, `childs()`, `addinspace()`, as well as hooks (`$check`, `$before`, `$on`, etc.).
*   **Dependency on DataTable.js:** For correct operation of client methods, the `DataTable.js` file must be connected, which contains the implementation of the `ltsDataTable` object. The PHP class `DataTable` only generates configuration and method calls.
*   All table configuration methods return `$this` to support fluent interface and method chaining.
*   The `$norows` flag defaults to `true` — the table is created without rows, data is added later via client methods. This is useful for large tables to avoid overloading the initial load.
*   Hidden fields via `hidden()` are saved in row data attributes — available on the client via `LTS(table).values()` but not displayed in cells.
*   The `rowclick()` method automatically generates a click handler — when clicking on a row, data is loaded into the specified form, `ltsDataId` is saved for subsequent updates.
*   The `loadrowclick` hook can be defined on the client for preprocessing data before loading into the form — called inside `rowclick()`.
*   The `compile()` method generates 13 client methods via `compilemethod()` — all become available via `LTS.get('{id}')` or `LTS(var)` on the client.
*   Sorting via `sort()` automatically determines the data type from the `data-type` attribute — supports int, float, date, string.
*   Empty values during sorting are moved to the end of the list regardless of direction.
*   The `values()` method supports 7 modes of operation — from getting all rows to filtering and updating by complex conditions.
*   When updating via `values()`, cells are modified by classes `.Column_{fieldname}` — HTML content is updated via `.html()`.
*   The `filter()` method uses smart comparison — empty strings always match, dates are parsed in `DD.MM.YYYY` or `YYYY-MM-DD` format, RegExp is supported.
*   Deep copying via `deepCopyArray()` uses `structuredClone()` if available — ensures correct cloning of nested objects.
*   The `reorderTbody()` method does not recreate rows — only changes the order in the DOM, which is more efficient for large tables.
*   Event handlers for sorting, editing, and deletion are registered via `$(document).on()` — work with dynamically added rows.
*   When editing a cell with the `.ltsEditable` class — an `<input>` is created on the fly, on `blur` or `Enter` — saved via `ltsDataTable.values()` and signal `{id}_ElementSave` is sent.
*   When deleting a row via `.ltsRowDelbutton` — checks for the presence of the `rowsdelete` handler at DataView, otherwise calls `ltsDataTable.clear()` and signal `{id}_RowsDel`.
*   Signals `{id}_RowClick`, `{id}_ElementSave`, `{id}_RowsDel` are used for integration with other framework components.
*   For working with large tables, it is recommended to use `$norows = true` and load data via `LTS(table).append()` as needed.
*   The `fieldvalues()` method returns unique values — useful for generating filters and statistics.
*   The class does not implement server-side pagination — for large data sets, filtering and loading in portions via Events is needed.
*   During compilation, data `$head`, `$rows`, `$hidden` is encoded to JSON — complex data types are serialized automatically.
*   For customizing row display, the `out()` hook is used — the function is called for each row with parameters `($row, $data)`.
*   The `add()` method automatically determines the data type — arrays with numeric keys are considered rows, with associative — headers.
*   For integration with editing forms, the `rowclick()` method is used — automatically links the table with the form by `ltsDataId`.
*   Date sorting supports formats `DD.MM.YYYY HH:MM:SS` and `YYYY-MM-DD` — parsed via `tryParseDate()`.
*   When deleting rows via `clear()`, data is deleted from both `ltsDataTable.rows[id]` and the DOM — synchronization of storage and display is ensured.
*   For working with selected rows, the `sel` property is used — toggled when clicking on the selection column.
*   The class is compatible with DataView — DataTable is used as a list records component within DataView.
*   The `filterAndApply()` method automatically resets the filter when values are empty — shows all rows.
*   To access a row by `ltsDataId`, the `Row()` method is used — returns a jQuery object for manipulations.
*   When adding rows via `append()`, a new `ltsDataId` is automatically generated — the counter is stored in `ltsDataTable.dataids[id]`.
*   For inline cell editing, the `.ltsEditable` class is used — creates an input on click, saves on `blur` or `Enter`.
*   Event handlers use delegation via `$(document).on()` — work with dynamically added elements.
*   For customizing headers, the `head()` method is used — accepts an associative array `{fieldname: caption}`.
*   The `findRows()` method supports multiple filter types — from HTML elements to objects with conditions.
*   During sorting, the direction is saved in the `data-sort-order` attribute — visually displayed via CSS.
*   For working with files and binary data in cells, it is recommended to use hidden fields via `hidden()`.
*   The class does not implement data export — for this, client methods `rows()` and server processing via Events should be used.
*   The `valuesCompare()` method is used for filtering — supports comparison of dates, strings, numbers, RegExp.
*   When updating via `values()`, the `hucks[id]` hook is called for each updated row — allows applying custom logic.
*   The class supports multiple tables on a page — each table has independent storage in `ltsDataTable`.
*   For integration with the Events system, signals `{id}_RowClick`, `{id}_ElementSave`, `{id}_RowsDel` can be used.
*   The `clearFilter()` method shows all rows — does not delete data from storage, only changes display.
*   During compilation, a call to `ltsDataTable.create()` is generated in the `'ready'` area — the table is rendered after DOM load.
*   The class does not implement row grouping — for this, custom logic in the `out()` hook is needed.
*   The `sort()` method supports multiple sorting — an array of fields with directions can be passed.
*   For working with selected rows, the `fieldvalues()` method is used — extracts values from filtered rows.
*   When deleting rows via `clear()` with a filter — only rows that passed the filter are deleted.
*   When working with the table via `values()` with an update — the original objects in the `rows[id]` storage of the `ltsDataTable` object are modified.
*   The `findRows()` method returns a jQuery object — supports all jQuery methods for row manipulations.
*   For working with large tables, it is recommended to use `norows = true` and load data in portions via `append()`.
*   For customizing display, CSS classes `.Column_{fieldname}` can be used — styling individual columns.
*   For working with multiple row selection, the `sel` property is recommended — toggled when clicking on the selection column.
*   When deleting rows, signal `{id}_RowsDel` is sent — can be subscribed to via `LTS.onSignal()`.

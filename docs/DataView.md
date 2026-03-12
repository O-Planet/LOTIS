[English](DataView.md) | [Русский](DataView.ru.md)

🚀 [Quick Start](../QuickStart.md)

# DataView Class

## Purpose
Central component of the LOTIS framework for creating interactive documents with multiple tabular sections. Inherits from `Grid` and combines three interface elements into a single component: `DataTable` (list of records with sorting and selection), `Form` (form for creating/editing records), and `FilterForm` (filter and search panel). Designed for rapid creation of CRUD interfaces with support for subordinate tables, event-based save/delete model, and adaptive grid layout.

## Properties

### Integrated Objects

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$table** | `DataTable` | `public` | Table object for displaying the list of records. Created in the constructor with ID `"{$this->id}_list"`. |
| **$element** | `Form` | `public` | Form object for viewing and editing records. Created in the constructor with ID `"{$this->id}_element"`. |
| **$filter** | `FilterForm` | `public` | Search and filter form object in the list. Created in the constructor with ID `"{$this->id}_filter"`. |
| **$dbtable** | `MySqlTable` | `public` | Database table for the main entity. Set via `dbtable()` or `bindtodb()`. |
| **$events** | `Events` | `public` | Events object for server-client communication. Created in the constructor. |
| **$operdialog** | `Dialog` | `public` | Dialog window for confirmation requests. Created in the constructor with ID `"{$this->id}_operdialog"`. |

### Interface Buttons

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$newbutton** | `Button` | `public` | Button for creating a new record. Created in the constructor with ID `"{$this->id}_newbutton"`. |
| **$delbutton** | `Button` | `public` | Button for deleting selected records. Created in the constructor with ID `"{$this->id}_delbutton"`. |
| **$filterbutton** | `Button` | `public` | Button for opening/closing the filter panel. Created in the constructor with ID `"{$this->id}_filterbutton"`. |

### Overridable Query Functions

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$datarequest** | `callable/null` | `public` | Function for reading records from the DB. Default — query to `$dbtable->all()`. Overridden via `datarequest()`. |
| **$saverequest** | `callable` | `public` | Function for saving records. Default — insert/update via `$dbtable->insert()/set()`. Overridden via `saverequest()`. |
| **$deleterequest** | `callable` | `public` | Function for deleting records. Default — deletion via `$dbtable->del()`. Overridden via `deleterequest()`. |
| **$loadsubtablesrequest** | `callable/null` | `public` | Function for reading subordinate tables. Called when opening a record to load subtable data. |
| **$savesubtablesrequest** | `callable/null` | `public` | Function for saving subordinate tables. Called after saving the main record. |

### Special Hooks

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$onsubtablesave** | `callable/null` | `public` | Hook after saving the tabular section. Called with parameters `($args, $oldrows, $rows)`. |

### Query Text Strings

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$noselectalert** | `string` | `public` | Message when attempting an operation without selected rows. Default: `'No rows selected in the table...'`. |
| **$delquery** | `string` | `public` | Deletion confirmation query text. Default: `'Selected rows will be deleted. Are you sure?'`. |

## Methods

### Constructor

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **__construct** | `string $id = ''` | — | Class constructor. Creates objects `$table`, `$element`, `$filter`, `$operdialog`, `$events`, `$newbutton`, `$delbutton`, `$filterbutton`. Registers event handlers `save` and `delete` with client and server logic. |

### Complex Builders

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **bindtodb** | `MySqlTable $dbtable`, `array $param = null` | `$this` | Binds the component to a database table with full configuration. Parameters: `head` (headers), `sort` (sorting), `fields` (selection fields), `conditions` (conditions), `norows` (deferred loading), `datarequest` (read function), `element` (form), `inputs` (field configuration), `cells` (form layout), `filter` (filter configuration), `new`/`delete` (buttons), `bar`/`content`/`bararea`/`contentarea`/`elementarea`/`filterarea` (Grid areas). Automatically generates form from `$dbtable->generateinputs()` if `inputs` is not specified. Returns `$this`. |
| **subtable** | `string $name = ''`, `MySqlTable $dbtable = null`, `array $param = null` | `DataView` | Creates a subordinate table (document tabular section). Creates a new `DataView` with simplified modes via `setmodessubtable`. Sets `fieldmap` for linking with the parent record (`parent_{table}` → `id`). Registers in `$this->subtables`. Automatically creates `loadsubtablesrequest` and `savesubtablesrequest` if not set. Disables standard `delete` events for the subtable. Returns the subtable object. |

### Data Management

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **dbtable** | `MySqlTable $dbtable` | `$this` | Binds the database table to the component. Saves in `$dbtable`. Used in `bindtodb()` and queries. Supports fluent interface. |
| **datarequest** | `callable $f` | `$this` | Overrides the function for reading records. Default — `$dbtable->all($conditions)`. Can use custom logic (cache, external APIs, etc.). Binds context via `bindTo()`. Supports fluent interface. |
| **saverequest** | `callable $f` | `$this` | Overrides the function for saving records. Default — insert/update via `$dbtable`. Should return `['result' => bool, 'data' => array]`. Binds context via `bindTo()`. Supports fluent interface. |
| **deleterequest** | `callable $f` | `$this` | Overrides the function for deleting records. Default — `$dbtable->del($id)`. Should return an array of deleted record IDs. Binds context via `bindTo()`. Supports fluent interface. |
| **loadsubtablesrequest** | `callable $f` | `$this` | Sets the function for loading subordinate tables. Called when opening a record. Returns an array of subtable data by ID. Binds context via `bindTo()`. Supports fluent interface. |
| **savesubtablesrequest** | `callable $f` | `$this` | Sets the function for saving subordinate tables. Called after saving the main record. Processes the array of subtables from `$args['subtables']`. Binds context via `bindTo()`. Supports fluent interface. |

### Manual Interface Building

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **createtable** | `array $head`, `callable $func` | `$this` | Creates a table with data from a function. Calls `$func([])` to get records, sets `$datarequest`, adds `sel` field for selection, sets table headers and data, disables `$norows`, sets sorting. Supports fluent interface. |
| **bar** | `string $areaName`, `...$extraButtons` | `$this` | Places buttons in the panel area. Adds `$filterbutton`, `$newbutton`, `$delbutton` to the specified Grid area. Adds extra buttons from `$extraButtons`. Supports fluent interface. |
| **content** | `string $areaName` | `$this` | Places the table in the content area. Adds `$table` to the specified Grid area. Supports fluent interface. |
| **elementgenerate** | `string $areaName`, `array $arr` | `$this` | Generates an editing form from configuration. Calls `$this->element->generate($arr)`, adds form to the specified Grid area. Supports fluent interface. |
| **elementcells** | `array $arr` | `Cells` | Organizes form fields into a table structure. Calls `$this->element->cells($arr)`. Returns a `Cells` object. Supports fluent interface. |
| **filterassign** | `string $areaName`, `array $arr` | `$this` | Binds the filter form to the table and places it in the area. Calls `$this->filter->assign($this->table, $arr)`, adds filter to the specified Grid area. Supports fluent interface. |

### Hook Management

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **checksave** | `callable/string $f` | `$this` | Registers a validator hook before saving. If a string — wraps in `function (args) { ... }`. If callable — binds context via `bindTo()`. Adds a `check{eventname}` handler to form events. Supports fluent interface. |
| **beforesave** | `callable/string $f` | `$this` | Registers a preprocessing hook before saving. Can modify arguments before sending to the server. Adds a `before{eventname}` handler to form events. Supports fluent interface. |
| **onsave** | `callable/string $f` | `$this` | Registers a post-processing hook after saving. Receives the save result. Adds an `on{eventname}` handler to form events. Supports fluent interface. |
| **onsubtablesave** | `callable $f` | `$this` | Sets a hook after saving the tabular section. Called with parameters `($args, $oldrows, $rows)`. Binds context via `bindTo()`. Supports fluent interface. |

### Grid Management

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **setmodesdefault** | — | `$this` | Sets default Grid modes: `'default'` (list with filter), `'element'` (edit form), `'filter'` (filter panel). Configures `header`, `menu`, `content`, `bar` areas for desktop and mobile. Supports fluent interface. |
| **setmodessubtable** | — | `$this` | Sets Grid modes for a subordinate table. Simplified configuration without `menu` area. Configures `bar`, `content`, `filter`, `element` areas. Supports fluent interface. |

### Additional Methods

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **_id** | `mixed $name` | `string` | Returns the object ID or signal. If `$name` is an object — returns `$name->id`, otherwise — `"{$this->id}_{$name}"`. Used for generating event and signal names. |
| **question** | `string $name`, `string $quest`, `string $onok`, `string $onno` | `$this` | Creates a method for confirmation request. Generates a client method with name `$name`, which sets `ondialogok`/`ondialogno` handlers, sets the question text, and opens `$operdialog`. Supports fluent interface. |

### Lifecycle

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **compile** | — | — | Generation of client-side JavaScript methods. If `$filter` — generates methods `filtershow()`/`filterhide()` for switching Grid modes. If `$element` — generates methods `elementshow()`/`elementhide()`, registers the `RowClick` signal for opening the form on row click, adds form close handler. For subtables — generates methods `loadtable()`, `cleartable()`, `values()`, adds code for collecting subtable data in `beforesave`. Generates methods `clearsubtables()`, `dialogrequest()`, sets messages `$delquery` and `$noselectalert`. Adds `$events` if there is a table or subtables. Calls `parent::compile()`. |

## Events

| Event | Parameters | Returns | Description |
|---------|-----------|------------|----------|
| **save** | `array $args` with form data | `array ['result' => bool, 'data' => array, 'code' => int]` | Server handler for saving. Calls `$saverequest`, processes subtables via `$savesubtablesrequest`, returns data for updating the table. If `code == 1` — redirect to login, if `code == 1000` — to confirm. |
| **delete(rows)** | `array $args` with key `'rows'` (array of records) | `array` (array of deleted record IDs) | Server handler for deletion. Calls `$deleterequest`, deletes records from subtables via `$subtable->dbtable->delall()`. Returns an array of objects with `ltsDataId` for deletion from DataTable. |
| **loadsubtables(row)** | `array $args` with key `'row'` (row data) | `array` (subtable data by ID) | Server handler for loading subtables. Calls `$loadsubtablesrequest`, returns an array of subtable data. Used when opening a record to load related data. |

## Client Methods Available via LTS Object

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **filtershow()** | — | — | Switches Grid to `'filter'` mode (displaying filter panel) and sends signal `'FilterShow'`. Available only if `$this->filter` is set. |
| **filterhide()** | — | — | Switches Grid to `'default'` mode (hiding filter panel) and sends signal `'FilterHide'`. Available only if `$this->filter` is set. |
| **elementshow()** | — | — | Switches Grid to `'element'` mode (displaying edit form) and sends signal `'ElementShow'`. Available only if `$this->element` is set. |
| **elementhide()** | — | — | Switches Grid to `'default'` mode (hiding edit form) and sends signal `'ElementHide'`. Available only if `$this->element` is set. |
| **clearsubtables()** | — | — | Clears all subordinate tables: calls `cleartable()` and `mode('default')` for each subtable. Generated only if there are subtables. |
| **dialogrequest(mes)** | `string mes` | — | Sets the message text in the confirmation dialog (`#{$this->id}_opermess`). Used before opening `$operdialog`. |
| **loadtable(data)** | `array data` | — | **For subtables only.** Loads data into the subtable. Calls `LTS({subtable->table->id}).create(data)`. Generated for each subtable in the `compile()` loop. |
| **cleartable()** | — | — | **For subtables only.** Clears the subtable. Calls `LTS({subtable->table->id}).clear()`. Generated for each subtable in the `compile()` loop. |
| **values(values)** | `array values` | `array` | **For subtables only.** Gets or sets values in the subtable. Calls `LTS({subtable->table->id}).values(values)`. Generated for each subtable in the `compile()` loop. |

### Additional LTS Object Properties

| Property | Type | Description |
|-------|----------|-----------|
| **delquery** | `string` (property) | Property for storing the deletion confirmation query text. Set during compilation from `$this->delquery`. |
| **noselectalert** | `string` (property) | Property for storing the error message when no rows are selected. Set during compilation from `$this->noselectalert`. |
| **ondialogok** | `function` (property) | Property for storing the dialog confirmation handler. Set before opening `$operdialog`, cleared after execution. |
| **ondialogno** | `function` (property) | Property for storing the dialog cancel handler. Set before opening `$operdialog`, cleared after execution. |
| **selectedrows** | `array` (property) | Property for storing the array of selected rows before deletion. Used in the delete button handler. |
| **filteropened** | `bool` (property) | Flag indicating the filter panel state (open/closed). Toggled in the `$filterbutton` handler. |

## Signals

| Signal | Description |
|--------|----------|
| **{id}_ElementSave** | Sent after successful record saving. |
| **{id}_RowsDel** | Sent after record deletion. |
| **{id}_ElementShow** | Sent when opening the edit form. |
| **{id}_ElementHide** | Sent when closing the edit form. |
| **{id}_FilterShow** | Sent when opening the filter panel. |
| **{id}_FilterHide** | Sent when closing the filter panel. |
| **{id}_ElementNew** | Sent when creating a new record. |
| **{id}_ElementLoad** | Sent when loading record data into the form. |
| **{id}_RowClick** | Sent when clicking on a table row. |
| **{id}_CloseAll** | Sent before opening a new window/form. |
| **{id}_LoadSubtables** | Sent after loading subtables. |

## Usage Examples

```php
// Creating DataView and setting standard Grid layout
$maindiv = LTS::DataView()->setmodesdefault();

// Binding data
$maindiv->bindtodb($techmap, [
    'head' => [
        'sel'   => '',
        'name'  => 'Product',
        'quantity' => 'Qty'
    ],
    'inputs' => [
        ['name' => 'id',  'type' => 'hidden'],
        ['name' => 'goods', 'type' => 'table', 'dbtable' => $goods, 'caption' => 'Product'],
        ['name' => 'quantity', 'type' => 'numeric', 'caption' => 'Quantity'],
        ['name' => 'comment',  'caption' => 'Comment'],
        ['name' => 'save', 'caption' => 'Save', 'type' => 'button'],
        ['name' => 'close', 'caption' => 'Cancel', 'type' => 'button']
    ],
    'fields' => 'id, goods, goods.name as name, quantity, comment',
    'cells' => ['save, close', 'goods', 'quantity', 'comment'],
    'filter' => [['name' => 'goods', 'type' => 'table', 'dbtable' => $goods, 'caption' => 'Product']],
    'sort' => ['name']
]);

// === Tabular section goods ===
$goodsordertable = $maindiv->subtable('goodsordertable', $techmapgoods, [
    'head' => [
        'sel'   => '',
        'name' => 'Materials',
        'quantity' => 'Qty',
        'del' => ''
    ],
    'area' => 'goods',
    'fields' => 'id, goods, goods.name as name, quantity',
    'inputs' => [
        ['name' => 'goods', 'type' => 'table', 'dbtable' => $goods, 'caption' => 'Material'],
        ['name' => 'quantity', 'type' => 'numeric', 'caption' => 'Quantity'],
        ['name' => 'ltsDataId', 'type' => 'hidden'],
        ['name' => 'save', 'type' => 'button', 'caption' => 'Ok'],
        ['name' => 'close', 'type' => 'button', 'caption' => 'Cancel']
    ],
    'cells' => ['save, close', 'goods', 'quantity'],
    'bar' => [$worksbutton],
    'filter' => null
]);

// Fields for LookupField
$elementgoods = $maindiv->element->field('goods');
$elementgoods->head(['name' => 'Name'])
    ->fieldmap(['id' => 'goods', 'name' => 'name']);

$subtablegoods = $goodsordertable->element->field('goods');
$subtablegoods->head(['name' => 'Name'])
    ->fieldmap(['id' => 'goods', 'name' => 'name']);

// Hooks for row output
$maindiv->table->out(
<<<JS
    function (row, obj) { 
        row.find('td.Column_sel').text(obj.sel ? '✅' : '☐'); 
    }
JS
        );
$goodsordertable->table->out(
<<<JS
function (row, obj) {
    row.find('td.Column_sel').text(obj.sel ? '✅' : '☐'); 
    row.find('td.Column_del').html('<input type="button" class="ltsRowDelbutton" value="x">');
} 
JS
);

// Standard Grid layout for subordinate table
$goodsordertable->setmodessubtable();

// Hooks for saving data
// Adding the name of the selected product from LookupField to the form data
$maindiv->checksave('args.set("name", LTS(elementgoods).selected.name); return true');
$maindiv->checksave(function ($args) { 
    // Checking if all required fields are filled
    if(empty($args['goods']))
        return 'Product must be selected!';
    if(empty($args['quantity']))
        return 'Quantity not specified for the product for which the technical map is being entered!';
    return true;
});
// Adding the name of the selected product from LookupField to the row edit form data
$goodsordertable->js()->add(
<<<JS
  LTS(goodsordertable).checkrowsave = function (values) {
    values.name = LTS(subtablegoods).selected.name; return true;
  }
JS
);

// Overriding the element template, adding an area for the subordinate table
$maindiv->setMode('element')
     ->device('desktop')
        ->areas(["element", "goods"])
        ->rows("auto 1fr")
        ->columns("1fr")
     ->device('mobile')
        ->areas(["element", "goods"])
        ->rows("auto 1fr")
        ->columns("1fr");

// Set the mode on opening
$maindiv->js('ready')->add('LTS(maindiv).mode("default")');

// Connect styles
$maindiv->css()->add('index.css');

// Build the page
LTS::Space()->build($maindiv);
```

## Notes
*   Class extends `Grid`, therefore inherits all adaptive grid layout methods (`setMode()`, `device()`, `areas()`, `rows()`, `columns()`, `area()`, etc.).
*   Class extends `Div`, `Element`, `Quark`, `Construct`, therefore supports full object lifecycle and hooks (`$check`, `$before`, `$on`).
*   **Three in one:** DataView combines `DataTable` (list), `Form` (editing), `FilterForm` (filter) into a single component with shared save/delete logic.
*   **Grid modes:** Methods `setmodesdefault()` and `setmodessubtable()` configure 3 display modes: `'default'` (list), `'element'` (form), `'filter'` (filter panel) — switching via `LTS({id}).mode()`.
*   **Subtables:** The `subtable()` method creates subordinate DataViews for document tabular sections — data is loaded/saved automatically when opening/saving the main record.
*   **DB binding:** The `bindtodb()` method automatically generates a form from `$dbtable->generateinputs()`, sets up filter, table, buttons — minimal code for a CRUD interface.
*   **Save events:** The `save` handler on the server calls `$saverequest`, then `$savesubtablesrequest` — ensures atomic saving of the main record and subtables.
*   **Delete events:** The `delete` handler deletes records from the main table and all subtables via `fieldmap` and `conditions` — cascade deletion.
*   **Subtable loading:** When clicking on a row — the `RowClick` signal calls `loadsubtables()` — subtable data is loaded and displayed in the corresponding Grid areas.
*   **Confirmation dialog:** `$operdialog` is used for confirmation requests — text is set via `$delquery`, handlers via `ondialogok`/`ondialogno`.
*   **Save hooks:** Methods `checksave()`, `beforesave()`, `onsave()` register lifecycle hooks for saving — validation, preprocessing, postprocessing.
*   **Control buttons:** `$newbutton`, `$delbutton`, `$filterbutton` are created automatically — can be overridden via `bindtodb()` parameters.
*   **Grid areas:** Methods `bar()`, `content()`, `elementgenerate()`, `filterassign()` place components in Grid areas — controlled via `bararea`, `contentarea`, `elementarea`, `filterarea` parameters.
*   **Signals:** DataView sends 11+ signals for integration with other components.
*   **Form data:** When saving, form data is passed to `$saverequest` via `$args` — includes all form fields and subtables in `subtables`.
*   **Table update:** After saving — the row is updated via `DataTable.values()` if there is an `ltsDataId`, or added via `append()` if it's a new record.
*   **Row selection:** The `sel` field is automatically added to all records — used for multiple deletion via `$delbutton`.
*   **Filter:** `$filter` is bound to `$table` via `assign()` — filtering is applied to table rows on the client.
*   **Subtables in form:** Subtable data is collected in `beforesave` via `LTS({subtableid}).values()` and passed to `$args['subtables']`. This hook cannot be overridden.
*   **Error handling:** If `result.code == 1` — redirect to `login.php`, if `code == 1000` — to `confirm.php`, otherwise — `alert(result.error)`.
*   **Method _id():** Unifies ID retrieval for events and signals — accepts an object or a string.
*   **createtable():** Alternative to `bindtodb()` for tables without a DB — data from `$func`, sorting by all fields.
*   **question():** Creates methods for dialog requests — sets text and handlers, opens `$operdialog`.
*   **Event registration:** `$events` is added to children only if there is a table or subtables — optimization for simple cases.
*   **Subtables without deletion:** Standard `delete` events are disabled for subtables — deletion via the `rowsdelete()` method.
*   **Subtable fieldmap:** Default — `["parent_{table}" => 'id']` — link via the parent field in the subtable.
*   **onsubtablesave:** Hook called after saving all rows of the subtable — parameters `($args, $oldrows, $rows)` for comparing changes.
*   **loadsubtablesrequest:** Returns an array `{subtableId => data}` — data is distributed to subtables via `loadtable()`.
*   **savesubtablesrequest:** Processes `$args['subtables']` — each subtable saves its data via `$subtable->saverequest`.
*   **elementcells():** Organizes form fields into a grid — passes field names to `Form::cells()`.
*   **filterassign():** Binds the filter to the table — filter fields correspond to table fields for automatic filtering.
*   **noselectalert:** Message when deleting without selection — can be overridden before compilation.
*   **delquery:** Deletion confirmation text — can be overridden before compilation.
*   **Modes for subtables:** `setmodessubtable()` simplifies layout — only `bar` and `content` without `menu`.
*   **Mode switching:** Via `LTS({id}).mode('element')` or `LTS(var)` — form is shown in the `element` area, list is hidden.
*   **Form closing:** The `close` button in the form automatically registers the `elementhide()` handler during compilation.
*   **Row click:** `DataTable::rowclick($this->element)` — when clicked, row data is loaded into the form, `element` mode is opened.
*   **Creating new:** `$newbutton` clears the form, resets `ltsDataId`, clears subtables, sends signal `ElementNew`, opens the form.
*   **Deletion:** `$delbutton` checks selection via `values({sel: 1})`, opens a confirmation dialog, calls `delete(rows)` on confirmation.
*   **Old subtable rows:** When saving — `$oldrows` are loaded via `$subtable->dbtable->all($conditions)` — to determine deleted rows.
*   **Deleted subtable rows:** Calculated via `array_diff($oldids, $myids)` — deleted via `$subtable->dbtable->delall()`.
*   **Function context:** All callback functions are bound via `bindTo($this, $this)` — `$this` DataView is accessible inside.
*   **bindtodb parameters:** The `$param` array controls all aspects — `head`, `sort`, `fields`, `conditions`, `inputs`, `cells`, `filter`, `new`, `delete`, `bar`, `content`, and areas.
*   **Auto-generation of inputs:** If `inputs` is not specified in `bindtodb()` — `$dbtable->generateinputs()` is called — fields are generated from the DB structure.
*   **fields in bindtodb:** If specified — used in the `FIELDS` condition, automatically generates `$head` from the list of fields considering `AS` aliases.
*   **norows in bindtodb:** If `true` — table is created without data — loaded via `datarequest` on opening.
*   **element in bindtodb:** If specified — uses a custom form, otherwise — generated from `inputs`.
*   **filter in bindtodb:** If `false` — `$filterbutton` is disabled, otherwise — generated from the field configuration array.
*   **bar in bindtodb:** Array of buttons for the panel — if not specified — standard `$filterbutton`, `$newbutton`, `$delbutton`.
*   **area() for subtables:** The `area` parameter in `subtable()` places the subtable in the specified Grid area of the parent.
*   **rowsave() for subtables:** Method generated for subtables — saves a row without a server request, updates the table, sends a signal.
*   **Save button in subtable:** A click handler is registered — calls `rowsave(values)` instead of the standard `save` event.
*   **delbutton in subtable:** Calls `rowsdelete(selectedrows)` — deletes rows from the table without a server request.
*   **rowsdelete() for subtables:** Method generated — clears rows from DataTable, sends `RowsDel` signal.
*   **clearsubtables():** Method generated — clears all subtables via `cleartable()` and `mode('default')`.
*   **dialogrequest(mes):** Method generated — sets the question text in `$operdialog` via `jQuery('#{id}_opermess').text(mes)`.
*   Methods `loadtable()`, `cleartable()`, `values()` are generated **only for subtables** in the `foreach($subtables as $subtableid => $subtable)` loop.
*   These methods are available via `LTS.get('{subtableid}')` where `{subtableid}` is the subtable ID (not the parent DataView) or via LTS(var), where var is a variable holding a reference to the subordinate table.
*   The `clearsubtables()` method calls `cleartable()` for all subtables — used when creating a new record in the parent form.
*   The `loadtable(data)` method is used in the `loadsubtables(row)` event handler — loads subtable data when opening the parent record.
*   The `values(values)` method is used when saving — collects data from all subtables in `args['subtables']` via the `beforesave` hook.
*   All three subtable methods delegate calls to the subtable's `DataTable` via `LTS({subtable->table->id})`.
*   The `cleartable()` method also switches the subtable mode to `'default'` — hides the subtable edit form.

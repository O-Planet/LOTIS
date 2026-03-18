[English](QuickStart.md) | [Русский](QuickStart.ru.md)

[Read me](README.md) 

# LOTIS Framework: Quick Start

## Installation

```bash
# Place the framework in a separate project directory
mkdir newlotis && cd newlotis
# Entry point — lotis.php file
```

---

## Architecture

- `src/`
  - `lotis.php` — [`lotis.php`](docs/lotis.md) — Entry point
  - `LTS/` — Core
    - [`DataView.php`](docs/DataView.md) — Main form: unified interface for working with "header + line items" documents
    - [`DataTable.php`](docs/DataTable.md) — Tables with sorting and filtering
    - [`FilterForm.php`](docs/FilterForm.md) — Table filter form
    - [`Stock.php`](docs/Stock.md) — Centralized accounting
    - [`Space.php`](docs/Space.md) — UI assembly
    - [`Events.php`](docs/Events.md) — Events via AJAX
    - [`Form.php`](docs/Form.md) — Input form: base container for fields
    - [`Input.php`](docs/Input.md) — Text input field
    - [`LookupField.php`](docs/LookupField.md) — Search field with dropdown and autocomplete
    - [`JS.php`](docs/JS.md) — JavaScript logic generation on PHP side
    - [`CSS.php`](docs/CSS.md) — Dynamic CSS rules management
    - [`Div.php`](docs/Div.md) — Universal container
    - [`Vars.php`](docs/Vars.md) — Global variables system
    - [`Grid.php`](docs/Grid.md) — Grid layout (table-like)
    - [`Dialog.php`](docs/Dialog.md) — Modal window (popup) for confirmations and forms
    - [`MySql.php`](docs/MySql.md) — MySQL connection
    - [`MySqlField.php`](docs/MySqlField.md) — Table field representation
    - [`MySqlTable.php`](docs/MultiTable.md) — Table operations: select, insert, update, delete
    - [`QueryBuilder.php`](docs/QueryBuilder.md) — SQL query builder
    - [`MultiTable.php`](docs/MySql.md) — Binding child tables to the parent table
    - [`Button.php`](docs/Button.md) — Button with actions: submit, signal, open form
    - [`Cells.php`](docs/Cells.md) — Element grid: row and column layout
    - [`Columns.php`](docs/Columns.md) — Flexible column layout (CSS grid analog)
    - [`Accordion.php`](docs/Accordion.md) — Expandable blocks for form grouping
    - [`Construct.php`](docs/Construct.md) — Dynamic UI structure creation
    - [`DataSync.php`](docs/DataSync.md) — Client-server data synchronization
    - [`Debug.php`](docs/Debug.md) — Debug tools: logging, variable output
    - [`Element.php`](docs/Element.md) — Base class for all UI elements
    - [`Ether.php`](docs/Ether.md) — Broadcast messaging system (pub/sub)
    - [`Html.php`](docs/Html.md) — HTML tag handling: label, span, div, etc.
    - [`Lang.php`](docs/Lang.md) — Multi-language support
    - [`LayerSlider.php`](docs/LayerSlider.md) — Interface layer switching (step-by-step forms)
    - [`Logger.php`](docs/Logger.md) — Action and error logging
    - [`ProgressBar.php`](docs/ProgressBar.md) — Visual task progress indicator
    - [`Quark.php`](docs/Quark.md) — Mini object model: events, subscriptions, calls
    - [`Select.php`](docs/Select.md) — Dropdown list
    - [`SimpleChart.php`](docs/SimpleChart.md) — Simple charts: bar, line, pie
    - [`Span.php`](docs/Span.md) — Inline container
    - [`Tabs.php`](docs/Tabs.md) — Tabs for section switching
    - [`Video.php`](docs/Video.md) — Video embedding (local or web)
  - `JS/` — Client-side part
    - [`lts.js`](docs/lts.js.md) — Client core
    - `Form.js`, `Events.js`, `DataTable.js`...
  - `CSS/` — Predefined styles
  - `examples/` — Project examples

## Object-Oriented Approach

LOTIS builds web applications using classical OOP principles. The framework provides three types of objects:

| Type | Examples | Purpose |
|:---|:---|:---|
| **UI Components** | `Div`, `Grid`, `Tabs`, `Button`, `Dialog`, `Form` | Visual interface elements |
| **Functional Objects** | `JS`, `CSS`, `Vars`, `Events` | Managing scripts, styles, state, and interaction |
| **Domain Objects** | `DataTable`, `DataView`, `DataSync` | Working with data and business logic |

### Creating Objects

All objects are created through the factory class `LTS`:

```php
$form = LTS::Form();
$grid = LTS::Grid();
```

### Hierarchical Structure

Each object must be nested within a parent container:

```php
$main = LTS::Div('main');      // Explicit ID
$form = LTS::Form();           // Automatic ID
$main->add($form);
```

### Application Assembly

```php
// Pass the root object to Space for rendering
LTS::Space()->build($main);
```

---

## The "Desktop" Principle

LOTIS eliminates the gap between server-side and client-side development:

- **Single file** — all application code in one PHP file
- **Sequential execution** — commands interpreted top-down
- **Unified namespace** — same variable names on server and client

---

## Client-Server Integration

### Class `JS`: Embedding Scripts

```php
$form = LTS::Form();
$form->js('ready')->add(<<<JS
    console.log('Form is ready');
JS);
```

Available contexts: `script` (in `<head>`), `ready` (after DOM load).

### Variable Unity

PHP variables are automatically available in client-side code:

```php
$form = LTS::Form();
$table = LTS::DataTable();
$btn = LTS::Button()->capt('Add')->click(<<<JS
    const rowdata = LTS(form).values();  // Access object by variable name
    LTS(table).append(rowdata);
    $(btn).hide();                       // jQuery wrapper
JS);
```

> **Important:** A variable can be declared in PHP *after* its use in a JS block, but *before* the `build()` call.

---

## Events: Two-Way Communication

The `Events` class connects client and server:

```php
$events = LTS::Events();

// Client-side response handler
$events->client('calculate(a, b)', <<<JS
    alert(result);
JS);

// Server-side handler
$events->server('calculate', function($args) {
    return $args['a'] + $args['b'];
});

// Trigger from interface
$btn = LTS::Button()->capt('Sum')->click(<<<JS
    LTS(events).calculate(5, 3);
JS);
```

---

## Global State: Class `Vars`

Variables that persist between requests within a session:

```php
$vars = LTS::Vars();

// Check first launch
if (!$vars->get('initialized')) {
    $div->capt('Welcome!');
} else {
    $div->capt('Welcome back!');
}

// Set flag on client
$div->js('ready')->add(<<<JS
    LTS.vars().set('initialized', true).store();
JS);
```

---

## Extending Functionality

### Custom Object Methods

```php
$panel = LTS::Div();
$panel->method('highlight(color)', <<<JS
    $(panel).css('border-color', color);
JS);

$btn = LTS::Button()->capt('Highlight')->click(<<<JS
    LTS(panel).highlight('#ff0000');
JS);
```

### Lifecycle Hooks

For events and methods, injection points are available:

| Hook | Purpose | Return Value |
|:---|:---|:---|
| `check` | Validation of execution possibility | `true`/`false` |
| `before` | Pre-processing arguments | Modified arguments |
| `on` | Post-processing result | — |

```php
// Example: client-side check for event savefile(name)
$events->method('checksavefile(name)', <<<JS
  if(! name) alert('Filename cannot be empty!');
  return name ? true : false;
JS);

// Example: automatic renaming on server for filename collision in event savefile
$events->add('beforesavefile', function($args) {
    $name = $args['name'];
    if (file_exists($name)) {
        $args['name'] = uniqid() . '_' . $name;
    }
    return $args;
});
```

---

## Signals and Subscriptions

Pub/Sub pattern implementation for client-side:

```php
$container = LTS::Div();
$container->signal('theme:dark', <<<JS
    $(container).addClass('dark-theme');
JS);

// Trigger signal from anywhere
$btn = LTS::Button()->capt('Dark Theme')->click(<<<JS
    LTS.signal('theme:dark');
JS);
```

---

## Object Lifecycle

```
┌─────────────┐    ┌────────────┐    ┌───────────┐    ┌──────────┐    ┌──────────────┐
│ __construct │  → │ compile    │  → │  shine    │  → │  childs  │  → │  addinspace  │
│             │    │ (checks)   │    │(rendering)│    │(children)│    │(registration)│
└─────────────┘    └────────────┘    └───────────┘    └──────────┘    └──────────────┘
```

### Lifecycle Injection Points

```php
$obj->check       = function() { /* ... */ return true; /* or false */ };
$obj->before     = function() { $this->prepareData(); };
$obj->on         = function() { $this->logCreation(); };
$obj->checkchilds = function() { /* ... */ return true; /* or false */ };
$obj->beforechilds = function() { $this->prepareChildsData(); };
$obj->onchilds   = function() { $this->logChildsCreation(); };
```

---

## Class `Div`

Universal container with full CSS Flexbox support.

```php
$app = LTS::Div('app')
    ->flex()
    ->column()
    ->align('stretch') 
    ->gap('20px');
```

---

## Class Grid

Adaptive CSS Grid layout with multi-mode support.

```php
$grid = LTS::Grid('layout');

// Named modes
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

// Place objects in grid
$grid->area('content')->add($table);
```

Mode switching

```js
LTS(grid).mode('element');
```

---

## Working with Tables: `DataTable`

```php
$dataTable = LTS::DataTable('users')
    ->head(['name', 'email'])
    ->data([
        ['1', 'Alice', 'alice@example.com'],
        ['2', 'Bob', 'bob@example.com']
    ])
    ->sort(['name']);
```

### Client-side available methods:

```js
LTS(dataTable).head(prot)            // set header
LTS(dataTable).rows(data)            // set rows
LTS(dataTable).create(values)        // build table
LTS(dataTable).values(attr, values)  // filter + update rows
LTS(dataTable).append(values)        // add new rows
LTS(dataTable).clear(attr)           // clear by filter
LTS(dataTable).filter(values)        // show rows by filter
LTS(dataTable).sort(name, direction) // sort rows
LTS(dataTable).fieldvalues(field, selectedrows)        // all column values by selected rows
LTS(dataTable).findRows(attr)        // jQuery rows by filter 
LTS(dataTable).Row(values)           // jQuery row by data  
```

---

## Working with Forms: `Form`, `Input`

```php
$form = LTS::Form('login')
    ->text('username', 'Login')
    ->password('password', 'Password')
    ->button('send', 'Sign In');

// Event on button click
$form->event('send', function ($args) {
    if ($args['username'] == 'admin' && $args['password'] == 'secret') {
        return 'You are logged in';
    }
    return 'Invalid login or password';
})
->event('send', <<<JS
  alert(result);
JS);
```

### By Template

```php
$form->generate([
    ['name' => 'id',  'type' => 'hidden'],
    ['name' => 'date',  'caption' => 'Date', 'type' => 'date'],
    ['name' => 'user', 'type' => 'table', 'dbtable' => $users, 'caption' => 'Employee'],
    ['name' => 'comment',  'caption' => 'Comment'],
    ['name' => 'total',  'caption' => 'Amount', 'type' => 'number', 'readonly' => 1],
    ['name' => 'save', 'caption' => 'Save', 'type' => 'button'],
    ['name' => 'close', 'caption' => 'Cancel', 'type' => 'button']
]);
```

### Client-side:

```js
LTS(form).data();       // get values as FormData
LTS(form).values(vals); // get or set values
LTS(form).value('username', 'trump'); // get or set field value
LTS(form).clear();      // clear form
```

---

## Localization: `Lang`

Created automatically by filename:

```php
$lang = LTS::Lang('de');
echo $lang->say('Welcome'); // → Hallo!, if translation exists
```

File: `index.de`

```
Welcome: Hallo!
description: Das ist Website-Programmierung
All rights reserved: Alle Rechte vorbehalten
```

---

## ORM: `MySql`, `MySqlTable`, `MySqlField`, `QueryBuilder`

```php
$base = new MySql('testdb', 'localhost', 'root', '');
$users = $base->table('users');
$users->string('name', 100);
$users->string('phone', 12);
$users->string('password', 60);
$users->enum('role', ['admin' => 'Administrator', 'user' => 'User']);
$users->bool('disabled');
$users->index('phone');
```

### Writing to table:

```php
$users->value('name', 'Donald Trump')
  ->value('phone', '1-222-333-444')
  ->insert();
```

### Database queries
```php
$all = $users->all(['role' => 'user', 'disabled' => 1]);
```

### Complex queries using QueryBuilder

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

## Class `DataView`

Domain-oriented component for managing tabular data: record list with filtering, edit form, nested tables.

DataView combines three interface elements into a single component:

| Element      | Class        | Function                                               |
| :----------- | :----------- | :---------------------------------------------------- |
| **List**     | `DataTable`  | Tabular display of records with sorting and selection |
| **Card**     | `Form`       | Form for creating/editing a record                  |
| **Filter**   | `FilterForm` | Selection and search panel                          |

```php
// Minimal configuration
$view = LTS::DataView()
    ->setmodesdefault()                          // default display modes
    ->bindtodb($emplpay, [
    'head' => [
        'sel'   => '',
        'date' => 'Date'
    ],
    'inputs' => [
        ['name' => 'id',  'type' => 'hidden'],
        ['name' => 'date', 'type' => 'date', 'caption' => 'Date'],
        ['name' => 'save', 'caption' => 'Save', 'type' => 'button'],
        ['name' => 'close', 'caption' => 'Cancel', 'type' => 'button']
    ],
    'fields' => 'id, date',
    'cells' => ['save, close', 'date'],
    'filter' => [
        ['name' => 'date', 'type' => 'date', 'caption' => 'Date']
    ],
    'sort' => ['date as date']
]);

LTS::Space()->build($view);
```

### Nested Tables

```php
$subtable = $view->subtable('emplpaysubtable', $emplpaytable, [
    'head' => [
        'name' => 'Employee',
        'pay' => 'Pay'
    ],
    'fields' => 'employee, employee.name as name, pay',
    'area' => 'table',
    'inputs' => [
        ['name' => 'ltsDataId', 'type' => 'hidden'],
        ['name' => 'employee', 'type' => 'select', 'values' => $employeeslist, 'caption' => 'Employee'],
        ['name' => 'pay', 'type' => 'numeric', 'caption' => 'Pay'],
        ['name' => 'save', 'type' => 'button', 'caption' => 'OK'],
        ['name' => 'close', 'type' => 'button', 'caption' => 'Cancel']
    ],
    'cells' => ['save, close', 'employee', 'pay'],
    'filter' => null
]);
```

---

## Class `Stock`

System for managing operations and synchronizing tabular data. Ensures atomic record updates with automatic recalculation of related collectors (balances, sums, statistics).

### Purpose

Solves the task of coordinated updates of two related entities:
- **Operations table** — detailed document lines (receipt, expense, transfer)
- **Balances table** — aggregated values by key fields

When operations change, balances are automatically adjusted without risk of data inconsistency.

```php
// 1. Create stock for operations table
$stock = LTS::Stock($operationsTable);

// 2. Connect goods balance collector
$stock->collector($goodsTable, 'goods', ['total' => 'quantity']);

// 3. Commit document changes
$stock->update(
    ['doc_id' => 123],                           // keys for fetching old values
    [                                            // new document lines
        ['goods' => 1, 'quantity' => 10],
        ['goods' => 2, 'quantity' => 5]
    ]
);
```

---

### How It Works

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│  Old rows       │────→│   Matching      │←────│  New rows       │
│  (from DB)      │     │   by key fields │     │  (from form)    │
└─────────────────┘     └────────┬────────┘     └─────────────────┘
                                 │
              ┌──────────────────┼──────────────────┐
              ↓                  ↓                  ↓
        ┌─────────┐        ┌─────────┐        ┌─────────┐
        │ Delete  │        │ Update  │        │ Insert  │
        │ (old)   │        │ (changed)│       │ (new)   │
        └────┬────┘        └────┬────┘        └────┬────┘
             │                  │                  │
             └──────────────────┼──────────────────┘
                                ↓
                    ┌─────────────────────┐
                    │ Recalculate         │
                    │ collectors          │
                    │ (delta: ±values)    │
                    └─────────────────────┘
```

---

## ▶️ What's Next?

- Explore [full examples](Examples.md)
- Ask questions in [Telegram](https://t.me/OPlanet)
- Create your own components and share them!

---

💰 Support the project with crypto:

🔹 **Bitcoin (BTC):** `bc1q0f8vwtstaevw542gn7uzvt5j69v75fp5ky927h`

🔹 **Ethereum (ETH):** `0x9843cC2985B2fCc995852fe1956DC799be5967d3`

🔹 **USDT (TRC-20):** `TFGXe8NfNv2KzkDEpuekZ9p91AU8ommjTM`

🔹 **TRX:** `TFGXe8NfNv2KzkDEpuekZ9p91AU8ommjTM`

🔹 **TON:** `UQDt433lyVgotQQW0Hj2VecJqXpXRoyR7spTl0A4idEziO99`

🔹 **Polygon (MATIC):** `0x9843cC2985B2fCc995852fe1956DC799be5967d3`

⚠️ **Double-check the network before sending!**

 [English](Form.md) | [Русский](Form.ru.md)

🚀 [Quick Start](../QuickStart.md)

# Form Class

## Purpose
A view class for data input forms in the LOTIS framework, inheriting from `Element`. Designed for creating interactive forms with support for various input field types, handling submission events, validation, and synchronization of data between server and client. The class automatically creates an `Events` object for server-client communication, provides methods for generating fields from configuration, organizing fields into grids (Cells/Grid), and managing form values through client-side methods. Integrates with `Input`, `LookupField`, and `DataTable` for creating complex input forms.

### Main Features

* **Creating fields** of various types
* **Managing values** of fields
* **Handling events** for submission
* **Data validation**
* **Organizing structure** of the form

### Field Types

The class supports creating the following field types:

* **Text fields** (text, email, tel, url)
* **Numeric fields** (number, range)
* **Date and time** (date, time)
* **Special types** (color, file, hidden)
* **Buttons** (submit, reset, button)
* **Checkboxes and radio** (checkbox, radio)
* **Lists** (select)
* **Text areas** (textarea)
* **Lookup fields with database fetching** (LookupField)

## Properties

| Property | Type | Visibility | Description |
|----------|------|------------|-------------|
| **$events** | `Events` | `public` | Event object for server-client communication. Created in the constructor with ID `"{$this->id}_events"`. Used for handling button clicks and form data submission. |

### Inherited Properties

| Property | Type | Visibility | Description |
|----------|------|------------|-------------|
| **$id** | `string` | `public` | Inherited from `Construct`. Unique identifier of the form, used as HTML attribute `id` and `name`. |
| **$type** | `string` | `public` | Inherited from `Element`. Set to `'html'`. Defines the object type for filtering and processing. |
| **$tagname** | `string` | `public` | Inherited from `Element`. Set to `'form'` in the constructor. Defines the HTML tag of the element during rendering. |
| **$storage** | `array` | `public` | Inherited from `Quark`. Storage contains sections `'childs'` (child fields), `'element'` (DOM element), `'attr'` (HTML attributes), `'clicks'` (button handlers). |

## Methods (Server Side, PHP)

### Constructor

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **__construct** | `string $id = ''` | — | Class constructor. Calls parent `Element` constructor, creates `$events` object with ID `"{$this->id}_events"`, sets `tagname` to `'form'`. If `$id` is not empty — sets the `name` attribute of the form. |

### Form Management Methods

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **generate** | `mixed $objects` | `$this` | Generates form fields from an array of configurations. Accepts an array or JSON string with field descriptions (`name`, `caption`, `type`, `value`, `values`, `id`, `readonly`, etc.). Creates corresponding `Input` or `LookupField` for each element. Supports fluent interface. |
| **cells** | `string $names = ''`, `int $captionscol = 1`, `int $inputscol = 2` | `Cells` | Organizes form fields into a table structure via `Cells` object. Accepts a list of field names, column number for captions, and column number for input fields. Returns `Cells` object for further configuration. |
| **grid** | `array $regions`, `Grid $grid = null` | `Grid` | Places form fields into a `Grid` by areas. Accepts an array `{areaName => [fieldNames]}`. If `$grid` is not passed — creates a new one. Returns `Grid` object with the form inside. |

### Field Creation Methods

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **field** | `string $name` | `Element/false` | Finds a form field by the `name` attribute. Iterates through `html` type children, checks `attr('name')` or `input->attr('name')` for `LookupField`. Returns field object or `false` if not found. |
| **input** | `string $type`, `string $name`, `string $caption = ''` | `Input` | Creates an input field of the specified type. Creates `Input` object, calls `setinput()`, sets `$form` reference, adds to children. Returns `Input` object. |
| **button** | `string $name`, `string $caption = ''` | `Input` | Creates a button. Creates `Input` object, calls `button()`, sets `$form` reference, adds to children. Returns `Input` object. |
| **table** | `string $name`, `MySqlTable $dbtable`, `string $caption = ''` | `LookupField` | Creates a lookup field bound to a database table. Creates `LookupField` object, sets `$dbtable`, headers, field name, label. Adds to form. Returns `LookupField` object. |
| **select** | `string $name`, `string $caption = ''`, `mixed $values = null` | `Input` | Creates a dropdown list. Creates `Input` object, calls `select()`, sets `$form` reference, adds to children. Returns `Input` object. |
| **textarea** | `string $name`, `string $caption = ''` | `Input` | Creates a text area. Creates `Input` object, calls `textarea()`, sets `$form` reference, adds to children. Returns `Input` object. |
| **checkbox** | `string $name`, `string $caption = ''` | `Input` | Creates a checkbox. Creates `Input` object, calls `checkbox()`, sets `$form` reference, adds to children. Returns `Input` object. |
| **file** | `string $name`, `string $caption = ''` | `Input` | Creates a file upload field. Creates `Input` object, calls `file()`, sets `$form` reference, adds to children. Returns `Input` object. |
| **hidden** | `string $name`, `string $caption = ''` | `Input` | Creates a hidden field. Creates `Input` object, calls `hidden()`, sets `$form` reference, adds to children. Returns `Input` object. |
| **image** | `string $name`, `string $caption = ''` | `Input` | Creates an image button. Creates `Input` object, calls `image()`, sets `$form` reference, adds to children. Returns `Input` object. |
| **password** | `string $name`, `string $caption = ''` | `Input` | Creates a password field. Creates `Input` object, calls `password()`, sets `$form` reference, adds to children. Returns `Input` object. |
| **radio** | `string $name`, `string $caption = ''`, `array $values = null` | `Input` | Creates a group of radio buttons. Creates first `Input` object, calls `radio()`, for remaining values creates additional `Input` objects. Sets `$form` reference, adds to children. Returns first `Input` object. |
| **reset** | `string $name`, `string $caption = ''` | `Input` | Creates a form reset button. Creates `Input` object, calls `reset()`, sets `$form` reference, adds to children. Returns `Input` object. |
| **submit** | `string $name`, `string $caption = ''` | `Input` | Creates a form submit button. Creates `Input` object, calls `submit()`, sets `$form` reference, adds to children. Returns `Input` object. |
| **text** | `string $name`, `string $caption = ''` | `Input` | Creates a text field. Creates `Input` object, calls `text()`, sets `$form` reference, adds to children. Returns `Input` object. |
| **email** | `string $name`, `string $caption = ''` | `Input` | Creates an email field (HTML5). Creates `Input` object, calls `email()`, sets `$form` reference, adds to children. Returns `Input` object. |
| **tel** | `string $name`, `string $caption = ''` | `Input` | Creates a phone field (HTML5). Creates `Input` object, calls `tel()`, sets `$form` reference, adds to children. Returns `Input` object. |
| **url** | `string $name`, `string $caption = ''` | `Input` | Creates a URL field (HTML5). Creates `Input` object, calls `url()`, sets `$form` reference, adds to children. Returns `Input` object. |
| **date** | `string $name`, `string $caption = ''` | `Input` | Creates a date field (HTML5). Creates `Input` object, calls `date()`, sets `$form` reference, adds to children. Returns `Input` object. |
| **color** | `string $name`, `string $caption = ''` | `Input` | Creates a color picker field (HTML5). Creates `Input` object, calls `color()`, sets `$form` reference, adds to children. Returns `Input` object. |
| **time** | `string $name`, `string $caption = ''` | `Input` | Creates a time field (HTML5). Creates `Input` object, calls `time()`, sets `$form` reference, adds to children. Returns `Input` object. |
| **number** | `string $name`, `string $caption = ''` | `Input` | Creates a number field (HTML5). Creates `Input` object, calls `number()`, sets `$form` reference, adds to children. Returns `Input` object. |
| **range** | `string $name`, `string $caption = ''`, `int $min = 1`, `int $max = 100` | `Input` | Creates a range slider (HTML5). Creates `Input` object, calls `range()` with `$min` and `$max`, sets `$form` reference, adds to children. Returns `Input` object. |

### Event Handling Methods

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **getEvent** | — | `Events` | Returns the form's event object. Used for direct registration of handlers via `$form->getEvent()->server()`. |
| **eventname** | `mixed $button` | `string` | Returns the event name for a button. Accepts ID (string) or button object, extracts `$button->id`. Used internally by `event()`. |
| **event** | `mixed $button`, `callable/string/false $func` | `$this` | Registers a button click handler. Accepts button (ID or object) and function/JS code. If `$func === false/null` — disables the event. If string — adds JS code. If callable — adds server-side handler. Sets flag in `'clicks'` section. Supports fluent interface. |
| **checkevent** | `mixed $button`, `callable/string $func` | `$this` | Registers a hook-validator before executing the event. Accepts button and function/string. If string — adds client-side code that must return `true` to continue or `false` to cancel. If callable — server function returning `true` or error message. Supports fluent interface. |
| **beforeevent** | `mixed $button`, `callable/string $func` | `$this` | Registers a data preprocessing hook before submission. Accepts button and function/string. Can modify arguments before sending to server. If returns `FormData` or object — data is replaced. Supports fluent interface. |
| **onevent** | `mixed $button`, `callable/string $func` | `$this` | Registers a post-processing hook after event execution. Accepts button and function/string. Receives result of main handler for additional logic. Supports fluent interface. |
| **error** | `mixed $button`, `string $body` | `$this` | Registers an error handler for a button. Accepts button (ID or object) and JS handler code. Calls `$events->error()`. Supports fluent interface. |

### Lifecycle Methods

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **compile** | — | — | Generates client-side JavaScript methods. If there are button handlers in `'clicks'` — generates code for each: creates `value(name, val)`, `values(vals)`, `data()`, `clear()` methods. For buttons with handlers — adds click handler that sends data via `LTS.get(formId).data()`. Calls `parent::compile()`. |

## Methods (Client Side, generated during compile)

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **LTS(form).value(name, val)** | `string name`, `mixed val` | `mixed` | Gets or sets a field value by name. If `val` is not passed — returns current value. For checkboxes returns `1`/`0`, for radio — value of selected button, for `LookupField` — ID of selected record. |
| **LTS(form).values(vals)** | `object vals` | `object` | Gets or sets values of all form fields. If `vals` is passed — sets values and returns `this`. Otherwise — returns object with all field values. For `LookupField` uses `fieldmap` for field mapping. |
| **LTS(form).data()** | — | `FormData` | Returns form data in `FormData` format. Used for sending files through `Events`. Includes all fields except `button`, `submit`, `reset`. For checkboxes adds `1`/`0`, for radio — only selected value, for `file` — file from `input.files[0]`. |
| **LTS(form).clear()** | — | `object` | Clears all form fields. Calls `values({})` — sets all values to empty strings, checkboxes to `false`. |

## ltsForm Object (Client Side, JS)

| Property | Type | Description |
|----------|------|-------------|
| **values** | `function` | Method for getting/setting form values. Returns object with field data. |
| **data** | `function` | Method for getting form data in `FormData` format. Used for sending files. |
| **clear** | `function` | Method for clearing all form fields. |
| **value** | `function` | Method for getting/setting value of a specific field by name. |
| **normalizeInputValue** | `function` | Method for converting string value to the format of the specified HTML5 field type. |

## normalizeInputValue Method (Client Side, JS)

| Field Type | Input Format | Output Format | Description |
|------------|--------------|---------------|-------------|
| **date** | `YYYY-MM-DD` | `YYYY-MM-DD` | Validates date, returns `null` if invalid. |
| **time** | `HH:MM` or `HH:MM:SS` | `HH:MM` or `HH:MM:SS` | Checks hour range (0-23), minutes (0-59), seconds (0-59). |
| **datetime-local** | `YYYY-MM-DDTHH:MM` | `YYYY-MM-DDTHH:MM` | Validates date and time. |
| **month** | `YYYY-MM` | `YYYY-MM` | Checks month range (1-12). |
| **week** | `YYYY-Www` | `YYYY-Www` | Checks week range (1-53). |
| **number/range** | Number | Number string | Parses via `parseFloat()`, returns `null` if `NaN`. |
| **url** | URL with protocol | Full URL | If no protocol — adds `http://`. Returns `null` if invalid. |
| **email** | email@domain.tld | Email string | Simple check for `@` and dot in domain. |
| **color** | `#RRGGBB` | `#rrggbb` | Validates HEX format, converts to lowercase. |
| **text/password/search/tel/hidden** | Any string | String | Returns as is after `trim()`. |

## Usage Examples

```php
// Creating a form
$myform = LTS::Form();

// Adding fields
$myform->text('username', 'Login')
     ->password('password', 'Password')
     ->email('email', 'Email');

// Adding buttons
$myform->button('close', 'Close')->click('$(myform).hide()');
$clickbutton = $myform->button('clickme', 'Click Me');

// Binding event to button
$myform->event($clickbutton,
<<<JS
    alert(result);
JS
  )
  ->event($clickbutton, function($values) {
    return 'Hello, ' . $args['username'];
});

// Assigning hooks
$myform->checkevent($clickbutton,
<<<JS
    function (args) {
        if(args.get('username') == '' || args.get('password') == '') {
          alert('Login and password must not be empty!');
          return false;
        }
        return true;
    };
JS
);

$myform->beforeevent($clickbutton, function($args) {
    // Modifying data before sending
    $args['username'] = strtoupper($args['username']);
    return $args;
});

// Organizing fields into Cells grid
$myform->cells([
    'username',
    'password',
    'email',
    'clickme, close'
]);
```

Form can be generated from an array description:

```php
// Creating a form
$myform = LTS::Form();
// Array of form field descriptions 
$inputs = [
        ['name' => 'works', 'type' => 'table', 'dbtable' => $works, 'caption' => 'Works'],
        ['name' => 'quantity', 'type' => 'numeric', 'caption' => 'Quantity'],
        ['name' => 'ltsDataId', 'type' => 'hidden'],
        ['name' => 'save', 'type' => 'button', 'caption' => 'OK'],
        ['name' => 'close', 'type' => 'button', 'caption' => 'Cancel']
    ];

// Generating form
$myform->generate($inputs);

// Definitions for LookupField
$worksfield = $myform->field('works');
$worksfield->head(['name' => 'Name'])
    ->fieldmap(['id' => 'works', 'name' => 'name']);

// Handler for close button click
$myforms->field('close')->click(
<<<JS
     $(myform).hide();
JS
);

// Binding event to save button
$myform->event('save',
<<<JS
     if(result.result)
          alert(result.data);
     else
          alert('Failed to save data!');
JS
  )
  ->event('save', function($values) {
     // Code that saves data
     return { 'result' => true, 'data' => 'Data saved'};
});

// Hooks for save button
$myform->checkevent('save', function($args) {
    if(empty($args['quantity']) || $args['quantity'] <= 0) {
        return 'Quantity must be greater than zero';
    }
    return true;
});

$myform->beforeevent('save', function($args) {
    $args['quantity'] = (int)$args['quantity'];
    $args['total'] = $args['quantity'] * $args['price'];
    return $args;
});

$myform->onevent('save',
<<<JS
function (result) {
    if(result.result) {
        LTS(myform).signal('DataSaved');
    }
    return $result;
}
JS
);
```

## Notes
*   The class extends `Element`, therefore inherits all methods for managing children, styles, attributes, and events.
*   The class extends `Quark` and `Construct`, therefore supports the full object lifecycle: `compile()`, `shine()`, `childs()`, `addinspace()`, as well as hooks (`$check`, `$before`, `$on`, etc.).
*   The `$events` object is created automatically in the constructor — no explicit creation required for handling buttons.
*   The `generate()` method supports JSON string or array of configurations — convenient for dynamic form generation from metadata.
*   Fixed configuration keys: `id`, `type`, `name`, `caption`, `values`, `value`, `min`, `max`, `class`, `dbtable`.
*   For fields with `class` in configuration — `addclass()` is called after creation.
*   The `cells()` method returns a `Cells` object — allows additional grid configuration before rendering.
*   The `cells()` method creates rows with classes `Row_{inputnames}` — for CSS styling.
*   When organizing in `Cells`, label and input are placed in different columns — controlled via `$captionscol` and `$inputscol`.
*   The `grid()` method adds the form to `Grid` after filling areas — `$grid->add($this)` — useful for complex layouts with multiple areas.
*   The `field()` method searches fields by `name` attribute — not by `$id` of `Input` object. Returns `false` if field not found — requires check before use.
*   For `LookupField`, the `field()` method checks `$child->input->attr('name')` — accounts for nested structure.
*   The `event()` method supports three modes: `false/null` (disable), string (JS code), callable (server function).
*   Button handlers are stored in `'clicks'` section — used during compilation to generate JS code.
*   The `compile()` method generates 4 client methods: `value()`, `values()`, `data()`, `clear()` — available via `LTS.get('{id}')` or `LTS(var)`.
*   For `submit`/`reset` buttons, the `compile()` method generates `click()` method — allows assigning handlers via Events.
*   The `data()` method returns `FormData` — required for sending files through `Events`.
*   The `values()` method supports field mapping for `LookupField` via `$lookup->fieldmap`.
*   For checkboxes, `values()` returns `1`/`0` — not `true`/`false`.
*   For radio buttons, `values()` returns value of selected button — unselected ones are ignored.
*   For `LookupField`, `values()` sets `selected` object and value in input via `normalizeInputValue()`.
*   The `normalizeInputValue()` method supports 10+ HTML5 field types — ensures correct value formatting.
*   When setting value via `value()` for `date`/`time` — format validity is checked.
*   For `url` without protocol — `http://` is automatically added.
*   For `color` — value is converted to lowercase (`#aabbcc`).
*   The `clear()` method sets all fields to empty values — does not remove fields from form.
*   `submit` and `reset` buttons have standard browser behavior — can be overridden via `event()`.
*   For buttons with handlers — standard behavior is canceled, data is sent via `Events`.
*   The `error()` method registers error handlers via `$events->error()` — executed on client when response fails.
*   During compilation, click handlers are generated for buttons — collects data via `LTS.get(formId).data()` and sends via `Events`.
*   Form automatically gets `name` attribute equal to `$id` — required for identification on client.
*   For `radio` with `$values` — multiple `Input` objects with same `name` are created — browser ensures mutual exclusion.
*   All field creation methods return the field object — allows chain configuration via fluent interface.
*   The `generate()` method skips elements without `name` key — requires explicit field name specification.
*   For fields with `readonly` in configuration — `readonly='1'` attribute is set.
*   The `cells()` method collects fields from `$this->get('childs')` — must be created before calling `cells()`.
*   The `grid()` method searches fields via `getchild()` — supports search by `tagname` and predicate function.
*   When placing in `Grid`, the form becomes a child of the area — `$this->owner` is set to area ID.
*   The `getEvent()` method returns `$events` — allows direct registration via `$form->getEvent()->server()`.
*   Event handlers execute in form context — `$args` contains data from all fields.
*   For file fields, data is passed via `FormData` — not via JSON.
*   The `values()` method for `LookupField` uses `fieldmap` — maps table field names to form fields.
*   When clearing via `clear()` for `LookupField` — `selected = null` is set and input is cleared.
*   For `datetime-local`, formats with `T` and space between date and time are supported.
*   For `week`, format is `YYYY-Www` — week from 01 to 53.
*   For `month`, format is `YYYY-MM` — month from 01 to 12.
*   For radio buttons, `value()` sets `checked` on button with matching value.
*   For `file` fields, `data()` adds first file from `input.files[0]`.
*   When sending via `Events`, data is automatically serialized — arrays and objects to JSON with `<JSON>` prefix.
*   The `eventname()` method extracts ID from object or string — universal interface.
*   When disabling event via `event($button, false)` — handler is removed from `'clicks'`.
*   **Event hooks:** Methods `checkevent()`, `beforeevent()`, `onevent()` register lifecycle handlers: validation → preprocessing → execution → postprocessing.
*   **Execution order:** When button is clicked: `checkevent` → `beforeevent` → `event` → `onevent`. If `checkevent` returns `false` — execution is aborted.
*   **Data modification:** `beforeevent` can return `FormData` or object to replace arguments before sending to server.
*   **Client hooks:** If string is passed — code executes on client, must be in format `function(args) { ... }` for `checkevent`/`beforeevent` or `function(result) { ... }` for `onevent`.
*   **Server hooks:** If callable function is passed — executes on server with parameters `($args)` for `checkevent`/`beforeevent` or `($args, $result)` for `onevent`.
*   **Return from checkevent:** Must return `true` to continue or `false`/string to cancel with error message.
*   **Return from beforeevent:** Can return modified `$args` (array, object, or `FormData`) or nothing to use original data.
*   **Return from onevent:** Receives result of main handler — can modify or log, return value is passed to client.

[English](FilterForm.md) | [Русский](FilterForm.ru.md)

🚀 [Quick Start](../QuickStart.md)

# FilterForm Class

## Purpose
Data filtering form class of the LOTIS framework, inheriting from `Form`. Designed for creating interactive filtering panels linked to a `DataTable` object. Allows the user to filter table rows by form field values with automatic filter application upon value changes. The class automatically creates field clear buttons and buttons for selecting values from a list of unique field values from the table. Supports deferred filter application (debounce 300ms) to optimize performance during frequent changes.

## Properties

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$datatable** | `DataTable/null` | `public` | Reference to the DataTable object for filtering. Set via the `assign()` method. Used for applying the filter to table rows. |

### Inherited Properties

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$events** | `Events` | `public` | Inherited from `Form`. Events object for server-client communication. Created in the form constructor. |
| **$id** | `string` | `public` | Inherited from `Construct`. Unique identifier of the form, used for linking with the client object and DataTable. |
| **$tagname** | `string` | `public` | Inherited from `Form`. Set to `'form'`. Defines the HTML tag of the element during rendering. |
| **$storage** | `array` | `public` | Inherited from `Quark`. Storage contains sections `'childs'` (child fields), `'element'` (DOM element), `'attr'` (HTML attributes). |
| **$type** | `string` | `public` | Inherited from `Element`. Set to `'html'`. Defines the object type for filtering and processing. |

## Methods (Server Side, PHP)

### Constructor

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **__construct** | `string $id = ''` | — | Class constructor. Calls parent `Form` constructor, adds CSS class `'filterform'` via `addclass()`. |

### Basic Assignment

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **assign** | `DataTable $datatable`, `mixed $objects`, `bool $auto = true` | `Cells` | Binds the form to a DataTable and generates filter fields. Accepts a DataTable object, an array of field configurations, and an auto-configuration flag. If `$auto == true` — adds the `'filterfield'` class to all fields and the `data-table` attribute with the table ID. Returns a `Cells` object with an organized field grid. |

### Lifecycle

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **compile** | — | — | Generation of client-side JavaScript methods. Creates the `filter(values)` method. Calls `parent::compile()` at the end. |

## LTS Object Client Methods

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **filter** | `Object values` | — | Method that integrates the specific LTS object with the functionality of `ltsForm` and `ltsDataTable` objects. (1) Checks if all filter values are empty. (2) If all are empty — calls `ltsDataTable.clearFilter()` to show all rows. (3) If there are values — calls `ltsDataTable.filter()` and `ltsDataTable.applyFilter()` to apply the filter |

### Additional Events, Methods

Client-side logic is implemented in a separate file `FilterForm.js` and registered via `$(document).ready()`.

| Event/Method | Parameters | Returns | Description |
|---------------|-----------|------------|----------|
| **change on .filterfield** | `Event e` | — | Handler for filter field value changes. Uses debounce 300ms via `setTimeout`/`clearTimeout`. Upon change, collects all form values via `ltsForm.values()`, calls `LTS.get(formId).filter(values)` to apply the filter to the DataTable. |
| **click on .filterdelbutton** | `Event e` | — | Handler for clicking the field clear button. Extracts `data-form`, `data-field` from the button attribute. Clears the field value via `ltsForm.value(formId, fieldName, '')`. Collects all form values, calls `LTS.get(formId).filter(values)` to update the filter (or `LTS(var).filter(values)`, where var is a variable referencing an instance of the `FilterForm` class). |
| **click on .filterlistbutton** | `Event e` | — | Handler for clicking the value list button. Extracts `data-table`, `data-form`, `data-field` from the button attribute. Gets unique field values via `ltsDataTable.fieldvalues()`. Creates a dynamic dropdown-menu with positioning relative to the button. Adds handlers for closing on click outside and pressing ESC. |
| **click on .dropdown-menu li a** | `Event e` | — | Handler for selecting a value from the dropdown. Extracts `data-form`, `data-field`, `data-value` from the link attribute. Finds the input by name in the form, sets the value via `.val(value).trigger('change')`. Closes the dropdown via `closeDropdown()`. |
| **bindOutsideClick()** | — | — | Helper function. Registers a click handler on `document` for closing the dropdown when clicking outside its boundaries. Checks via `is()` and `has()` that the click is not on the dropdown. Calls `closeDropdown()`. |
| **bindEscapeKey()** | — | — | Helper function. Registers a key press handler on `document`. When `Escape` is pressed, calls `closeDropdown()`. |
| **closeDropdown()** | — | — | Helper function. Removes the dropdown-menu from the DOM via `.remove()`. Removes temporary handlers via `$(document).off('click.dropdown-close')` and `$(document).off('keydown.dropdown-close')`. |

## Special CSS Classes

| Class | Description |
|-------|----------|
| **`.filterform`** | Base class of the filter form — added automatically in the constructor. |
| **`.filterfield`** | Class of filter fields — added automatically via `assign()` if `$auto == true`. |
| **`.filterdelbutton`** | Class of the field clear button — created for each field except buttons. |
| **`.filterlistbutton`** | Class of the value list button — created for fields with `addlist` in the configuration. |
| **`.dropdown-menu`** | Class of the dropdown value list — created dynamically when clicking on `.filterlistbutton`. |

## Special Data Attributes

| Attribute | Description |
|---------|----------|
| **`data-form`** | ID of the filter form — added to `.filterdelbutton` and `.filterlistbutton` buttons. |
| **`data-field`** | Name of the form field — added to buttons for identifying the field to clear/fill. |
| **`data-table`** | ID of the DataTable for filtering — added to buttons for linking with the table. |
| **`data-value`** | Value to set — added to dropdown-menu elements. |

## Usage Example

```php
// Creating a filter form
$filterForm = LTS::FilterForm();

// Binding to a table and configuring filters
$filtercells = $filterForm->assign(
    $datatable,
    [
        ['name' => 'dateotg', 'caption' => 'Shipment Date', 'type' => 'date'],
        ['name' => 'name', 'caption' => 'Name'],
        ['name' => 'type', 'type' => 'select', 'caption' => 'Type', 'values' => ['material' => 'materials', 'goods' => 'goods']]
    ]
);

// Binding the filter Cells to CSS
$filtercells->addclass('myclass');
```

## Notes
*   Class extends `Form`, therefore inherits all field creation methods (`text()`, `select()`, `date()`, etc.), organization methods (`cells()`, `grid()`), and event handling methods (`event()`, `error()`).
*   Class extends `Element`, `Quark`, `Construct`, therefore supports full object lifecycle: `compile()`, `shine()`, `childs()`, `addinspace()`, as well as hooks (`$check`, `$before`, `$on`, etc.).
*   **Debounce 300ms:** Filter field changes are not applied immediately — a 300ms delay is used via `setTimeout` to prevent frequent table redraws during rapid input.
*   **Clear button:** For each field (except buttons), a `.filterdelbutton` button with a ❌ icon is automatically created — clicking clears the field value and updates the filter.
*   **List button:** For fields with the `'addlist'` key in the configuration, a `.filterlistbutton` button with a 🔻 icon is created — clicking shows a dropdown with unique values from the DataTable.
*   **Unique values:** The `ltsDataTable.fieldvalues()` method extracts unique values of the specified field from all table rows — used to populate the dropdown.
*   **Dynamic dropdown:** The dropdown list is created dynamically on click — positioned relative to the button via `offset().top` and `offset().left`.
*   **Dropdown closing:** The dropdown closes when clicking outside its boundaries or pressing the Escape key — handlers are registered only after opening.
*   **Empty filter:** If all filter values are empty — `ltsDataTable.clearFilter()` is called to show all table rows.
*   **Filter application:** When values are present — `ltsDataTable.filter()` is called for filtering and `ltsDataTable.applyFilter()` for updating the display.
*   **Linking with DataTable:** Via the `assign()` method, the form is linked to a DataTable — the filter is applied to the rows of this table.
*   **Auto-configuration:** When `$auto == true` — all fields receive the `'filterfield'` class and the `data-table` attribute with the table ID.
*   **Field organization:** The `assign()` method returns a `Cells` object — fields are organized in a table structure with labels and input fields in different columns.
*   **Field configuration:** Accepts an array or JSON string with field descriptions — similar to `Form::generate()`.
*   **addlist key:** If a field configuration contains the `'addlist'` key — a `.filterlistbutton` button is created for selection from the list.
*   **change handler:** Registered via `$(document).on()` — works with dynamically created fields.
*   **Value collection:** When a field changes, the entire form is collected via `ltsForm.values()` — the filter is applied to all fields simultaneously.
*   **Field clearing:** When clicking `.filterdelbutton` — the value is set to an empty string, `change` is triggered to apply the filter.
*   **Selection from list:** When clicking a dropdown element — the value is set in the input, `change` is triggered to apply the filter.
*   **Temporary handlers:** Handlers for closing the dropdown are registered with the `.dropdown-close` namespace — removed when closing.
*   **Dropdown positioning:** Calculated dynamically via `offset().top + outerHeight()` — appears below the button.
*   **Dropdown removal:** When closing, the dropdown is removed from the DOM via `.remove()` — recreated on the next opening.
*   **Outside check:** When clicking outside the dropdown, checked via `is()` and `has()` — closes only if the click is not on the dropdown.
*   **Escape key:** The `keydown` handler checks `e.key === 'Escape'` — closes the dropdown.
*   **filter() method:** Generated during compilation via `compilemethod()` — available via `LTS.get('{id}').filter()`.
*   **Empty check:** Uses `Object.values().every()` to check that all values are empty (null, undefined, empty string).
*   **clearFilter():** Shows all table rows — does not delete data, only changes display via `.show()`.
*   **applyFilter():** Hides rows that failed the filter via `.toggle()` — preserves data in memory.
*   **fieldvalues():** Returns unique values via `Set` — automatically removes duplicates.
*   **data-attributes:** Used to link buttons with fields and the table — extracted via `.data()`.
*   **trigger('change'):** After setting the value from the dropdown — the change event is triggered to apply the filter.
*   **preventDefault():** For links in the dropdown — prevents navigation to href='#'.
*   **stopPropagation():** For clicking the list button — prevents immediate dropdown closing.
*   **CSS classes:** `.filterform`, `.filterfield`, `.filterdelbutton`, `.filterlistbutton`, `.dropdown-menu` — for styling.
*   **Icons:** ❌ for clearing, 🔻 for the list — UTF symbols are used in `capt()`.
*   **Button width:** 60px for clear and list buttons — set via `width()`.
*   **Button owner:** Set to the Cells cell ID via `$listbutton->owner` — for positioning in the grid.
*   **Configuration array:** Supports the same keys as `Form::generate()` — `name`, `caption`, `type`, `addlist`, etc.
*   **JSON string:** If `$objects` is a string — parsed via `json_decode()` into an array.
*   **Returns Cells:** The `assign()` method returns a `Cells` object — allows additional field grid configuration.
*   **Columns:** By default, labels in column 1, input fields in column 2 — controlled via `cells()`.
*   **filterfield class:** Added to fields for identification in the change handler — via jQuery selector.
*   **data-table attribute:** Added to buttons for linking with DataTable — used in `fieldvalues()`.
*   **data-form attribute:** Added to buttons for form identification — used in `ltsForm.value()`.
*   **data-field attribute:** Added to buttons for field identification — used for clearing/filling.
*   **data-value attribute:** Added to dropdown elements — contains the value to set in the field.
*   **document handler:** All handlers are registered on `document` via `.on()` — work with dynamic elements.
*   **Event namespace:** Closing handlers use the `.dropdown-close` namespace — for selective removal.
*   **Display preservation:** When hiding the wrapper, the original `display` is preserved in `grid.wrapperDisplay` — for restoration.
*   **Display restoration:** When showing the wrapper, the preserved `display` is used or `'block'` by default.
*   **valuesCompare() method:** Used in `ltsDataTable.filter()` — supports comparison of dates, strings, numbers, RegExp.
*   **Empty string:** In `valuesCompare()`, an empty string always matches — shows all rows for this field.
*   **RegExp:** If the filter value is a RegExp — `test()` is used for checking.
*   **Dates:** Parsed via `tryParseDate()` — `DD.MM.YYYY` and `YYYY-MM-DD` formats are supported.
*   **Substrings:** For strings, `includes()` is used — checking for substring inclusion.
*   **tryParseDate():** Supports multiple date formats — returns a `Date` object or `null`.
*   **filterAndApply():** Combines filtering and application — checks filter emptiness.
*   **Automatic application:** When any field changes — the filter is applied to all fields simultaneously.
*   **Optimization:** Debounce 300ms prevents frequent redraws during rapid input.
*   **Dependencies:** Requires `lts.js`, `Form.js`, `DataTable.js` — for basic functionality.

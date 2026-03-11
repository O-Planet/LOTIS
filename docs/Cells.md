[English](Cells.md) | [Русский](Cells.ru.md)

🚀 [Quick Start](../QuickStart.md)

# Cells Class

## Purpose
Table-cell layout class of the LOTIS framework, inheriting from `Div`. Designed for creating structured grids with organization by rows and columns. Unlike `Grid` (CSS Grid) and `Columns` (Flexbox), the `Cells` class implements a two-dimensional coordinate system where each element has an address by row and column. Automatically creates rows (`Columns` objects) and cells (`Div` objects) upon access, supporting dynamic addition, insertion, and content management through data arrays. The class is useful for creating forms with tabular structure, toolbars, dropdown lists, and other interfaces requiring a strict grid.

## Properties

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$rowscount** | `int` | `public` | Counter of created rows. Increases when creating each new row via `line()` or `insert()` methods. Used to determine the next available row position. |

### Inherited Properties

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$id** | `string` | `public` | Inherited from `Construct`. Unique object identifier, used as HTML `id` attribute. |
| **$type** | `string` | `public` | Inherited from `Element`. Set to `'html'`. Defines the object type for filtering and processing. |
| **$tagname** | `string` | `public` | Inherited from `Div`. Default `'div'`. Defines the HTML tag of the element during rendering. |
| **$classname** | `string` | `public` | Inherited from `Element`. The class `'Cells'` is automatically added in the constructor. Managed by methods `addclass()`, `removeclass()`, `hasclass()`. |
| **$storage** | `array` | `public` | Inherited from `Quark`. Storage contains sections `'childs'` (child rows), `'element'` (DOM element), `'attr'` (attributes). |

## Methods

### Constructor

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **__construct** | `string $id = ''` | — | Class constructor. Calls parent `Div` constructor, adds CSS class `'Cells'`, initializes `$rowscount` to `0`. |

### Row Management

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **insert** | `int $numb` | `Columns` | Inserts a new row at the specified position. If `$numb` is greater than the current number of rows — calls `line($numb)`. Creates a `Columns` object with classes `'Row Row{numb}'`. Recalculates classes of all subsequent rows (shifts numbers by +1). Updates the children array `$storage['childs']` accounting for the new position. Increments `$rowscount`. |
| **line** | `int $numb = null` | `Columns` | Returns or creates a row by number. If `$numb` is not specified — creates the next row. If the requested number is greater than the current counter — automatically creates all intermediate rows. Each row receives classes `'Row'` and `'Row{N}'` where N is the row number. Returns a `Columns` object for adding cells. |
| **cell** | `int $linenumb`, `int $columnnumb` | `Div` | Returns or creates a cell by coordinates (row, column). Calls `line($linenumb)` to get the row, then `$row->cell($columnnumb)` to get the cell. Convenient method for direct cell access without a chain of calls. |
| **add** | `mixed $name`, `mixed $value = null` | `$this` | Overridden content addition method. Supports three operation modes: (1) If `$name` is an array of arrays (records) — creates a row for each record via `line()->add($val)`. (2) If `$name` is a simple array — adds all elements to one row via `line()->add($name)`. (3) Otherwise — calls parent `Div::add()`. Automatically determines array type by data structure. Supports fluent interface. |

### Usage Examples

```php
// Creating a basic table
$cells = LTS::Cells();

// Adding rows
$cells->line(1)->add('First row');
$cells->line(2)->add('Second row');

// Inserting a row at a specific position
$cells->insert(1); // Will insert a new first row

// Working with cells
$cells->cell(1, 1)->capt('Cell 1-1');
$cells->cell(1, 2)->capt('Cell 1-2');

// Bulk row addition
$cells->add([
    ['Cell 1-1', 'Cell 1-2'],
    ['Cell 2-1', 'Cell 2-2']
]);

// Adding elements to cells
$cells->cell(1, 1)->add(LTS::Button()->capt('Button')->click('alert("I was pressed!")'));
$cells->cell(2, 1)->add(LTS::Div()->capt('Text'));
```

## Notes
*   Class extends `Div`, therefore inherits all Flexbox methods (`flex()`, `row()`, `column()`, `content()`, `align()`, `gap()`, etc.), child management (`add()`, `del()`, `getchilds()`, `move()`), and styling (`css()`, `width()`, `height()`).
*   Class extends `Element`, `Quark`, `Construct`, therefore supports full object lifecycle: `compile()`, `shine()`, `childs()`, `addinspace()`, as well as hooks (`$check`, `$before`, `$on`, etc.).
*   All rows automatically receive the CSS class `'Row'` for general styling and `'Row{N}'` for individual (where N is the row number).
*   The `insert()` method recalculates classes of all subsequent rows — this ensures correct numbering after insertion.
*   The `line()` method automatically creates intermediate rows if the requested number is greater than the current counter — similar to the `cell()` method in the `Columns` class.
*   The `cell()` method is a convenient shorthand for `$cells->line($row)->cell($col)` — allows accessing a cell by two-dimensional coordinates.
*   The `add()` method with a data array automatically determines the structure: if the array contains nested arrays — each nested array becomes a separate row.
*   For associative arrays, nested elements are distributed across columns within a row.
*   All cells are `Div` objects — any child elements can be added to them via the standard `add()` method.
*   For styling rows, it is recommended to use CSS classes `'Row'` (common styles) and `'Row{N}'` (individual styles for a specific row).
*   For styling cells, classes `'Column'` and `'Column{N}'` are used — they are assigned automatically via the `Columns` object.
*   Class has no predefined client methods: all logic is executed on the server.
*   Class does not implement its own `shine()`, `compile()`, `childs()` methods — they are inherited from `Div` and `Quark` without changes.
*   For creating adaptive structures, it is recommended to combine with Flexbox methods: `$cells->flex()->column()->wrap()`.
*   The `line()` method returns a `Columns` object — this allows immediately adding cells via a chain: `$cells->line(1)->cell(2)->add($element)`.
*   When filling from an array, empty values still create rows — this ensures structure preservation even with partial data.
*   Class is useful for creating forms with multiple rows and columns, toolbars, tabular layouts, and other layout tasks requiring two-dimensional organization.
*   Unlike `Grid`, the `Cells` class does not support named areas and mode switching — this is a pure server structure with a fixed grid.
*   Unlike `Columns`, the `Cells` class manages two levels of nesting (rows → columns) instead of one (columns).
*   The `insert()` method is useful for dynamically adding rows to an existing structure without recreating the entire object.
*   When inserting a row via `insert()`, all subsequent rows shift down — their classes are updated automatically (Row3 → Row4, etc.).
*   All methods return `$this` or the corresponding object (`Columns`, `Div`) to support fluent interface and method chaining.

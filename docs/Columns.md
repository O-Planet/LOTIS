[English](Columns.md) | [Русский](Columns.ru.md)

🚀 [Quick Start](../QuickStart.md)

# Columns Class

## Purpose
Column layout class of the LOTIS framework, inheriting from `Div`. Designed for creating a flexible column structure with automatic cell management. Allows adding content to specific columns by number or name, automatically creating missing columns when necessary. The class provides a convenient interface for filling columns both individually and in bulk through data arrays. All columns automatically receive CSS classes for styling and can contain any child objects of the framework.

## Properties

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$columnscount** | `int` | `public` | Counter of created columns. Increases when creating each new cell via the `cell()` method. Used to determine the next available column position. |

### Inherited Properties
| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$id** | `string` | `public` | Inherited from `Construct`. Unique object identifier, used as HTML `id` attribute. |
| **$type** | `string` | `public` | Inherited from `Element`. Set to `'html'`. Defines the object type for filtering and processing. |
| **$tagname** | `string` | `public` | Inherited from `Div`. Default `'div'`. Defines the HTML tag of the element during rendering. |
| **$classname** | `string` | `public` | Inherited from `Element`. The class `'Columns'` is automatically added in the constructor. Managed by methods `addclass()`, `removeclass()`, `hasclass()`. |
| **$storage** | `array` | `public` | Inherited from `Quark`. Storage contains sections `'childs'` (child columns), `'element'` (DOM element), `'keys'` (column keys for access by number). |

## Methods

### Constructor

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **__construct** | `string $id = ''` | — | Class constructor. Calls parent `Div` constructor, adds CSS class `'Columns'`, initializes `$columnscount` to `0`. |

### Column Management Methods

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **cell** | `mixed $columnnumb = null` | `Div` | Returns or creates a cell (column) by number or name. If `$columnnumb` is not specified — creates the next column. If an integer is passed — creates or returns the column by number (1-based). If a string is passed — creates or returns a named column. Automatically creates intermediate columns if the requested number is greater than the current counter. Each column receives classes `'Column'` and `'Column{N}'` where N is the number or name. |
| **add** | `mixed $name`, `mixed $value = null` | `$this` | Overridden method for adding content to columns. Supports three operation modes: (1) If `$name` is an array or object without `shineobject`, and `$value` is not passed — fills multiple columns from the array (keys determine the column number for associative arrays or sequential numbers for indexed arrays). (2) If `$value` is not passed — adds content to the next available column. (3) If both parameters are passed — adds `$value` to the column with number/name `$name`. For objects, calls `add()` on the cell, for scalars — sets `$caption`. Supports fluent interface. |

## Usage Examples

```php
// Creating a basic column container
$columns = LTS::Columns();

// Adding columns by number
$columns->cell(1)->capt('First column');
$columns->cell(2)->capt('Second column');

// Adding columns by name
$columns->cell('left')->capt('Left column');
$columns->cell('right')->capt('Right column');

// Bulk column addition
$columns->add([
    1 => 'Content of the first column',
    2 => 'Content of the second column',
    'left' => 'Left column',
    'right' => 'Right column'
]);

// Adding elements to columns
$columns->add('left', LTS::Button()->capt('Hide')->click('$(columns).hide()'));
$columns->add(2, LTS::Div()->capt('Text'));
```

## Notes
*   Class extends `Div`, therefore inherits all Flexbox methods (`flex()`, `row()`, `column()`, `content()`, `align()`, `gap()`, etc.), child management (`add()`, `del()`, `getchilds()`, `move()`), and styling (`css()`, `width()`, `height()`).
*   Class extends `Element`, `Quark`, `Construct`, therefore supports full object lifecycle: `compile()`, `shine()`, `childs()`, `addinspace()`, as well as hooks (`$check`, `$before`, `$on`, etc.).
*   All columns automatically receive the CSS class `'Column'` for general styling and `'Column{N}'` for individual (where N is the column number or name).
*   The `cell()` method supports two types of addressing: numeric (1, 2, 3...) and named ('header', 'sidebar', 'content'...).
*   When requesting a column with a number greater than `$columnscount` — all intermediate columns are automatically created. For example, if 2 columns are created and the 5th is requested — columns 3, 4, and 5 will be created.
*   Column keys are stored in the `'keys'` section of the `$storage` — this allows mapping column numbers to their actual keys in the children array.
*   The `add()` method with a data array is convenient for quickly filling columns — each array element is placed in the corresponding column.
*   For associative arrays, keys are used as column numbers, for indexed arrays — keys are converted to 1-based numbers.
*   Objects without the `shineobject` property (e.g., configuration arrays) are processed as data for filling, not as child elements.
*   All methods return `$this` to support fluent interface and method chaining.
*   Columns are `Div` objects — any child elements can be added to them via the standard `add()` method.
*   For styling columns, it is recommended to use CSS classes `'Column'` (common styles) and `'Column{N}'` (individual styles for a specific column).
*   Class has no predefined client methods: all logic is executed on the server.
*   Class does not implement its own `shine()`, `compile()`, `childs()` methods — they are inherited from `Div` and `Quark` without changes.
*   For creating an adaptive column structure, it is recommended to combine with Flexbox methods: `$columns->flex()->row()->wrap()`.
*   The `cell()` method returns a `Div` object — this allows immediately adding content to the column via a chain: `$columns->cell(1)->add($element)`.
*   When filling from an array, empty values still create columns — this ensures structure preservation even with partial data.

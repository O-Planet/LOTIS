[English](Div.md) | [Русский](Div.ru.md)

🚀 [Quick Start](../QuickStart.md)

# Div Class

## Purpose
Universal container class of the LOTIS framework, inheriting from `Element`. Designed for creating block elements with full CSS Flexbox support. Is the main building block for interface layout — allows organizing child elements into rows or columns, managing their alignment, spacing, and display order. The class provides a fluent interface for chained style configuration and contains ready-made methods for common layout scenarios (rowbox, columnbox, gapCentered, etc.). All methods return `$this` to support sequential calls.

### Main Features

* **Flexbox layout** with full property support
* **Various display types** (block, inline, flex)
* **Ready-made presets** for typical layouts
* **Spacing management** between elements

## Properties

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$_reverse** | `bool` | `private` | Flag for reverse element order on the main axis. Set by the `reverse()` method. Used when generating `flex-direction: row-reverse` or `column-reverse`. |
| **$_wrapreverse** | `bool` | `private` | Flag for reverse row order when wrapping. Set by the `wrapreverse()` method. Used when generating `flex-wrap: wrap-reverse`. |

### Inherited Properties

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$id** | `string` | `public` | Inherited from `Construct`. Unique object identifier, used as HTML `id` attribute. |
| **$type** | `string` | `public` | Inherited from `Element`. Set to `'html'`. Defines the object type for filtering and processing. |
| **$tagname** | `string` | `public` | Inherited from `Element`. Default `'div'`. Defines the HTML tag of the element during rendering. |
| **$classname** | `string` | `public` | Inherited from `Element`. CSS classes of the element, managed by methods `addclass()`, `removeclass()`, `hasclass()`. |
| **$caption** | `string` | `public` | Inherited from `Element`. Text content of the container. |
| **$storage** | `array` | `public` | Inherited from `Quark`. Storage contains sections `'childs'` (children), `'element'` (DOM element), `'attr'` (attributes), `'options'` (options). |

## Methods

### Constructor

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **__construct** | `string $id = ''` | — | Class constructor. Calls parent `Element` constructor, initializes private flags `$_reverse` and `$_wrapreverse` to `false`. |

### Display Types

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **block** | — | `$this` | Sets `display: block`. Block display mode. |
| **none** | — | `$this` | Sets `display: none`. Hides the element. |
| **inline** | — | `$this` | Sets `display: inline`. Inline display mode. |
| **flex** | — | `$this` | Sets `display: flex`. Activates Flexbox mode for the container. |

### Ready-made Presets

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **rowbox** | — | `$this` | Ready-made configuration for a horizontal container: `flex` + `row` + `wrap` + `content('center')` + `align('center')`. |
| **columnbox** | — | `$this` | Ready-made configuration for a vertical container: `flex` + `column` + `wrap(false)` + `content('center')` + `align('center')`. |
| **gapCentered** | — | `$this` | Ready-made configuration: centers the container and sets `gap: 10px`. Calls `content('center')`, `align('center')`, `gap('10px')`. |
| **gapStack** | — | `$this` | Ready-made configuration for a vertical stack: `column()` + `gap('10px')`. |
| **gapInline** | — | `$this` | Ready-made configuration for a horizontal row: `row()` + `gap('10px')`. |

### Flexbox Direction

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **primaryaxis** | `string $axis` | `$this` | Sets the main axis. If `'row'` — calls `row()`, if `'column'` — calls `column()`. |
| **row** | — | `$this` | Sets `flex-direction: row` or `row-reverse` (if `$_reverse` is active). Horizontal main axis. |
| **column** | — | `$this` | Sets `flex-direction: column` or `column-reverse` (if `$_reverse` is active). Vertical main axis. |
| **wrap** | `bool $w = true` | `$this` | Sets `flex-wrap`. If `true` — `'wrap'` or `'wrap-reverse'` (if `$_wrapreverse` is active). If `false` — `'nowrap'`. |
| **reverse** | `bool $r = true` | `$this` | Sets the `$_reverse` flag and recalculates `flex-direction`. Changes the element order to opposite on the main axis. |
| **wrapreverse** | `bool $r = true` | `$this` | Sets the `$_wrapreverse` flag and recalculates `flex-wrap`. Changes the row order to opposite when wrapping. |

### Flexbox Alignment

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **content** | `string $justify` | `$this` | Sets `justify-content`. Valid values: `'start'`, `'end'`, `'center'`, `'between'`, `'around'`, `'evenly'`. Aliases: `'beg'`, `'begin'` = `'start'`. |
| **align** | `string $align` | `$this` | Sets `align-items`. Valid values: `'start'`, `'end'`, `'center'`, `'stretch'`, `'baseline'`. Aliases: `'beg'`, `'begin'` = `'start'`. |
| **aligncontent** | `string $cont` | `$this` | Sets `align-content`. Applied when splitting into multiple rows. Values: `'start'`, `'end'`, `'center'`, `'stretch'`, `'between'`, `'around'`. |
| **contentalign** | `string $cont` | `$this` | Alias for `aligncontent()`. |
| **primarycontent** | `string $cont` | `$this` | Sets alignment on the main axis. Wrapper over `content()`. |
| **secondalign** | `string $align` | `$this` | Sets alignment on the cross axis. Wrapper over `align()`. |
| **secondcontent** | `string $cont` | `$this` | Sets row alignment on the cross axis. Wrapper over `aligncontent()`. |

### Spacing Management

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **gap** | `string $value` | `$this` | Sets the CSS property `gap`. Defines the spacing between all child elements. Example: `'10px'`, `'1em 20px'`. |
| **rowGap** | `string $value` | `$this` | Sets the CSS property `row-gap`. Vertical spacing between rows. |
| **columnGap** | `string $value` | `$this` | Sets the CSS property `column-gap`. Horizontal spacing between columns. |

### Configuration Check

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **isrow** | — | `bool` | Checks if the main axis is horizontal. Returns `true` if `flex-direction` equals `'row'` or `'row_reverse'`. |
| **iscolumn** | — | `bool` | Checks if the main axis is vertical. Returns `true` if `flex-direction` equals `'column'` or `'column_reverse'`. |

## Usage Examples

```php
// Creating a basic container
$div = LTS::Div('container')
    ->flex()
    ->row()
    ->gap('10px');

// Vertical container
$column = LTS::Div()
    ->columnbox()
    ->width('100%');

// Container with centering
$centered = LTS::Div()
    ->gapCentered()
    ->height('100vh');

// Example with full set of settings
$layout = LTS::Div()
    ->flex()
    ->column()
    ->wrap(true)
    ->content('space-between')
    ->align('center')
    ->gap('20px');
```

## Notes
*   Class extends `Element`, therefore inherits all methods for managing children (`add()`, `del()`, `getchilds()`, `move()`, etc.), styles (`css()`, `width()`, `height()`), attributes (`attr()`, `removeattr()`), and events (`on()`, `click()`, `signal()`).
*   Class extends `Quark` and `Construct`, therefore supports full object lifecycle: `compile()`, `shine()`, `childs()`, `addinspace()`, as well as hooks (`$check`, `$before`, `$on`, etc.).
*   All Flexbox configuration methods return `$this` to support fluent interface and method chaining.
*   Private flags `$_reverse` and `$_wrapreverse` affect CSS property generation in methods `row()`, `column()`, `wrap()` — when the flag changes, the corresponding method is called again to recalculate the style.
*   Methods `rowbox()` and `columnbox()` provide ready-made presets for common layout scenarios — content centering on both axes with automatic wrapping (or without).
*   Methods `gapCentered()`, `gapStack()`, `gapInline()` simplify the creation of typical layout constructions with spacing between elements.
*   Value aliases (`'beg'`, `'begin'` = `'start'`) allow using more intuitive names when configuring alignment.
*   Methods `isrow()` and `iscolumn()` check the current `flex-direction` value via the CSS object — return the actual state after all changes.
*   The `gap` property is supported by modern browsers for Flexbox containers — for older browsers, a polyfill or alternative layout may be required.
*   Separate methods `rowGap()` and `columnGap()` allow independently configuring vertical and horizontal spacing.
*   Child elements are added via methods `add()`, `addmany()`, `addin()` — the addition order determines the display order in the DOM (can be changed via `flex-direction: row-reverse` or `column-reverse`).
*   Class does not implement its own methods `shine()`, `compile()`, `childs()` — they are inherited from `Element` and `Quark` without changes.
*   For creating nested structures, chained calls are recommended: `$div->add(LTS::Div()->columnbox()->add(...))`.
*   The `display()` method is common for all values — specialized methods `block()`, `none()`, `inline()`, `flex()` provide convenient wrappers for frequent cases.

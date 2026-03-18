[English](ProgressBar.md) | [Русский](ProgressBar.ru.md)

🚀 [Quick Start](../QuickStart.md)

# ProgressBar Class

## Purpose
A visual progress indicator class of the LOTIS framework, inheriting from `Div`. Designed for displaying operation execution progress through the jQuery UI Progressbar widget. Supports configuring minimum/maximum values, current progress value, and text label. The class automatically generates client-side JavaScript code for widget initialization and provides methods for programmatically updating progress value, enabling/disabling the indicator. Integrates with the LOTIS event system for reacting to state changes.

### Key Features

* **Visual display** of progress
* **Range configuration** of values
* **Display text** customization
* **State control** (enable/disable)
* **Dynamic value** updates

## Inherited Properties

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$id** | `string` | `public` | Inherited from `Construct`. Unique object identifier, used as HTML `id` attribute and for linking with the client-side object. |
| **$type** | `string` | `public` | Inherited from `Element`. Set to `'html'`. Determines object type for filtering and processing. |
| **$tagname** | `string` | `public` | Inherited from `Div`. Default is `'div'`. Determines HTML tag of the element when rendering. |
| **$classname** | `string` | `public` | Inherited from `Element`. Class `'ProgressBar'` is automatically added in constructor. Managed by `addclass()`, `removeclass()`, `hasclass()` methods. |

## Methods

### Constructor

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **__construct** | `string $id = ''` | — | Class constructor. Calls parent `Div` constructor, adds CSS class `'ProgressBar'` via `addclass()`. |

### Configuration Methods

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **value** | `mixed $val` | `$this` | Sets current progress value. Calls `option('value', $val)`. Saves value in `'options'` section. Supports fluent interface. |
| **min** | `mixed $val` | `$this` | Sets minimum progress value. Calls `option('min', $val)`. Saves in `'options'` section. Supports fluent interface. |
| **max** | `mixed $val` | `$this` | Sets maximum progress value. Calls `option('max', $val)`. Saves in `'options'` section. Supports fluent interface. |
| **text** | `string $val = ''` | `$this` | Sets progress bar text label. Initializes `options` via `option('value', 0)`, saves text in `'text'` section. If `$val` not passed — uses empty string. Supports fluent interface. |

### Lifecycle

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **compile** | — | — | Generation of client-side JavaScript methods. Extracts parameters from `'options'` section, encodes via `json_encode()`. Extracts text from `'text'` section, generates `progresschange` handler code for label updates. Creates JS object with methods: `value(val)`, `disable`, `enable`. Adds JS initialization code to `'ready'` area: creates jQuery UI progressbar, adds `.progress-label` element for text display, registers progress change handler. Calls `parent::compile()` at the end. |

## LTS Object Client Methods

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **LTS(progressbar).value(val)** | `mixed val` | — | Updates progress value on client. Calls `jQuery('#{id}').progressbar('option', 'value', val)`. |
| **LTS(progressbar).disable()** | — | — | Disables progress bar. Calls `jQuery('#{id}').progressbar('disable')`. |
| **LTS(progressbar).enable()** | — | — | Enables progress bar. Calls `jQuery('#{id}').progressbar('enable')`. |

## Usage Examples

```php
// Creating a progress bar
$progress = LTS::ProgressBar('myProgress');

// Basic configuration
$progress->min(0)
        ->max(100)
        ->value(50);

// Adding custom text
$progress->text('Loading');

// Additional settings
$progress->width('100%')
        ->height('20px');
```

Dynamic updates

```JS
// Updating value via JavaScript
LTS('myProgress').value(75);

// Disabling progress bar
LTS('myProgress').disable();

// Enabling progress bar
LTS('myProgress').enable();
```

## Notes
*   The class extends `Div`, therefore inherits all Flexbox, child management, and styling methods.
*   The class extends `Element`, `Quark`, `Construct`, therefore supports full object lifecycle and hooks.
*   **jQuery UI Dependency:** ProgressBar uses jQuery UI Progressbar — requires `jquery-ui.css` and `jquery-ui.js` to be loaded in the `Space`.
*   All configuration methods return `$this` to support fluent interface and chaining.
*   Method `text()` initializes `options` via `option('value', 0)` — ensures correct operation when setting text before value.
*   Text label is displayed through `.progress-label` element — positioned absolutely at center of progress bar.
*   Handler `progresschange` updates label text when value changes — shows `{text}: {value}%` or `{value}%` if text not set.
*   Method `compile()` generates 3 client methods: `value()`, `disable()`, `enable()` — available via `LTS.get('{id}')`.
*   During compilation, parameters are encoded via `json_encode()` with `JSON_UNESCAPED_UNICODE` — Cyrillic in text is preserved without escaping.
*   For appearance customization — use CSS classes `.ProgressBar`, `.progress-label`, `.ui-progressbar`.
*   Progress value must be within `min`–`max` range — default is `0`–`100`.
*   For indeterminate progress — set `value` to `false` (jQuery UI indeterminate mode).
*   Method `disable()` blocks interaction — useful when operation completes.
*   Method `enable()` unblocks progress bar — for reuse.
*   Event `progresschange` triggers on every value change — can be used for additional logic.
*   Element `.progress-label` is created dynamically on initialization — added via `appendTo()`.
*   `.progress-label` styles are set inline via JS — `position: absolute`, `top: 50%`, `left: 50%`, `transform: translate(-50%, -50%)`.
*   Label font is `bold 12px Arial`, color is `#555` — can be overridden via CSS.
*   To update value on client — use `LTS(progressbar).value(newValue)`.
*   When changing value via JS — `progresschange` handler updates text automatically.
*   To reset progress — set `value(0)` — returns indicator to initial state.

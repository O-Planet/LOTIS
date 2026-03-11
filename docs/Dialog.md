[English](Dialog.md) | [Русский](Dialog.ru.md)

🚀 [Quick Start](../QuickStart.md)

# Dialog Class

## Purpose
Modal dialog window class of the LOTIS framework, inheriting from `Div`. Designed for creating interactive pop-up windows based on jQuery UI Dialog. Supports configuration of title, show/hide effects, modal mode, action buttons, and positioning. The class automatically generates client-side JavaScript code for dialog initialization and provides methods for programmatic opening/closing of the window. Integrates with the LOTIS event system for handling button clicks.

## Properties

### Inherited Properties

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$id** | `string` | `public` | Inherited from `Construct`. Unique object identifier, used as HTML `id` attribute and for linking with the client object in `ltsDialog`. |
| **$type** | `string` | `public` | Inherited from `Element`. Set to `'html'`. Defines the object type for filtering and processing. |
| **$tagname** | `string` | `public` | Inherited from `Div`. Default `'div'`. Defines the HTML tag of the element during rendering. |
| **$classname** | `string` | `public` | Inherited from `Element`. The class `'Dialog'` is automatically added in the constructor. Managed by methods `addclass()`, `removeclass()`, `hasclass()`. |
| **$storage** | `array` | `public` | Inherited from `Quark`. Storage contains sections `'childs'` (children), `'element'` (DOM element), `'options'` (dialog parameters), `'buttons'` (action buttons). |

## Methods

### Constructor

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **__construct** | `string $id = ''` | — | Class constructor. Calls parent `Div` constructor, adds CSS class `'Dialog'` via `addclass()`. |

### Display Configuration Methods

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **capt** | `string $str` | `$this` | Sets the dialog title. Sets the `title` attribute to the value of `$str`. Used by jQuery UI Dialog to display the window title. Supports fluent interface. |
| **autoopen** | `bool $open = true` | `$this` | Sets the `autoOpen` parameter of the dialog. If `true` — the dialog opens automatically on initialization. If `false` — requires explicit call to `open()`. Supports fluent interface. |
| **modal** | `bool $val = true` | `$this` | Sets the modal mode of the dialog. If `true` — blocks interaction with the rest of the page while the dialog is open. Saves in the `'options'` section. Supports fluent interface. |
| **show** | `string $effect = 'blind'`, `int $duration = 1000` | `$this` | Sets the dialog show effect. Accepts the name of a jQuery UI effect (`'blind'`, `'fade'`, `'slide'`, etc.) and duration in milliseconds. Saves in the `'options'` section as an array `['effect' => $effect, 'duration' => $duration]`. Supports fluent interface. |
| **hide** | `string $effect = 'explode'`, `int $duration = 1000` | `$this` | Sets the dialog hide effect. Accepts the name of a jQuery UI effect and duration in milliseconds. Saves in the `'options'` section as an array `['effect' => $effect, 'duration' => $duration]`. Supports fluent interface. |
| **option** | `string $name`, `mixed $val = null` | `mixed/$this` | Universal method for managing dialog parameters. If `$val === null` — returns the value of the parameter from the `'options'` section. If passed — sets the parameter. Supports fluent interface. |

### Positioning Methods

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **left** | `mixed $val` | `$this` | Sets the horizontal position of the dialog. Can be a number (pixels) or a string (`'center'`, `'left'`, `'right'`). Saves in the `'options'` section. Supports fluent interface. |
| **top** | `mixed $val` | `$this` | Sets the vertical position of the dialog. Can be a number (pixels) or a string (`'center'`, `'top'`, `'bottom'`). Saves in the `'options'` section. Supports fluent interface. |
| **width** | `mixed $val` | `$this` | Sets the dialog width. Can be a number (pixels) or a string (`'auto'`, `'500px'`). Saves in the `'options'` section. Supports fluent interface. |
| **height** | `mixed $val` | `$this` | Sets the dialog height. Can be a number (pixels) or a string (`'auto'`, `'300px'`). Saves in the `'options'` section. Supports fluent interface. |

### Button Management Methods

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **button** | `string $caption`, `string $onClick = ''` | `$this` | Adds an action button to the dialog. Accepts the button text and JavaScript click handler code. Saves in the `'buttons'` section with the key `$caption`. During compilation, generates methods `button1()`, `button2()`, etc. for each handler. Supports fluent interface. |

### Lifecycle

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **compile** | — | — | Generation of LTS object client methods. Extracts buttons from the `'buttons'` section, generates click handlers (`button1()`, `button2()`, etc.). Converts the button array to jQuery UI Dialog format. Extracts parameters from the `'options'` section, encodes via `JS::paramobject()`. Adds JS initialization code to the `'ready'` area: sets `buttonN` methods, creates jQuery dialog object. Generates client methods `open()` and `close()` via `compilemethod()`. |

### LTS Object Client Methods

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **LTS(dialog).open()** | — | `this` | Opens the dialog window. Uses jQuery UI Dialog method `dialog('open')`. Supports method chaining. |
| **LTS(dialog).close()** | — | `this` | Closes the dialog window. Uses jQuery UI Dialog method `dialog('close')`. Supports method chaining. |
| **LTS(dialog).buttonN()** | — | — | Action button handlers. Generated automatically during compilation for each button via `button()`. Call the JavaScript code passed in `$onClick`. Methods are numbered sequentially: `button1()`, `button2()`, `button3()`, etc. Support method chaining. |

## Usage Examples

```php
// Creating a dialog window
$dialog = LTS::Dialog();
$dialog->capt('Action Confirmation')
    ->modal(true)
    ->width(400)
    ->height(200)
    ->button('OK', "alert('Action completed')")
    ->button('Cancel', "LTS(dialog).close()")
    ->autoopen(false);

// Configuring animation
$dialog->show('slide', 500)
    ->hide('explode', 1000);
```

## Notes
*   Class extends `Div`, therefore inherits all Flexbox methods (`flex()`, `row()`, `column()`, `content()`, `align()`, `gap()`, etc.), child management (`add()`, `del()`, `getchilds()`, `move()`), and styling (`css()`, `width()`, `height()`).
*   Class extends `Element`, `Quark`, `Construct`, therefore supports full object lifecycle: `compile()`, `shine()`, `childs()`, `addinspace()`, as well as hooks (`$check`, `$before`, `$on`, etc.).
*   **jQuery UI Dependency:** Dialog uses jQuery UI Dialog — requires `jquery-ui.css` and `jquery-ui.js` to be loaded in the `Space`.
*   All dialog configuration methods return `$this` to support fluent interface and method chaining.
*   The `capt()` method sets the `title` attribute — jQuery UI Dialog uses it for the window title.
*   The `autoopen()` method defaults to `true` — the dialog opens immediately after initialization. For deferred opening, set to `false` and call `LTS(dialog).open()`.
*   The `show()` and `hide()` effects use jQuery UI Effects — available: `'blind'`, `'bounce'`, `'clip'`, `'drop'`, `'explode'`, `'fade'`, `'fold'`, `'highlight'`, `'puff'`, `'pulsate'`, `'scale'`, `'shake'`, `'slide'`, `'transfer'`.
*   Effect durations are specified in milliseconds — default `1000` (1 second).
*   The `button()` method adds buttons to the `'buttons'` section — converted to jQuery UI Dialog format during compilation.
*   Button handlers are numbered sequentially — `button1()`, `button2()`, `button3()` — the order is determined by the order of addition via `button()`.
*   Button handler JavaScript code is passed as a string — may contain any valid JS.
*   The `modal()` method blocks interaction with the page — useful for confirmations and critical actions.
*   Positioning via `left()` and `top()` — can be a number (pixels from window edge) or a string (`'center'`, `'left'`, `'right'`, `'top'`, `'bottom'`).
*   Sizes via `width()` and `height()` — can be a number (pixels), a string (`'500px'`, `'auto'`), or a percentage (`'80%'`).
*   The `option()` method — universal access to jQuery UI Dialog parameters — can set any supported options.
*   The `compile()` method generates 2 client methods: `open()` and `close()` — available via `LTS.get('{id}')` and `LTS(var)`, where var is a PHP variable referencing an instance of the Dialog class.
*   For customizing appearance — use the CSS class `.Dialog` and jQuery UI Dialog classes (`ui-dialog`, `ui-dialog-titlebar`, `ui-dialog-content`, etc.).
*   jQuery UI Dialog events (`beforeClose`, `close`, `create`, `drag`, `dragStart`, `dragStop`, `focus`, `open`, `resize`, `resizeStart`, `resizeStop`) — can be assigned via `option('eventName', handler)`.
*   To get a parameter value — use `option('name')` without the second argument.
*   The `button()` method with empty `$onClick` — creates a button without a handler — can be assigned later via client-side JS.
*   The dialog supports dragging — enabled by default, can be disabled via `option('draggable', false)`.
*   The dialog supports resizing — enabled by default, can be disabled via `option('resizable', false)`.
*   For modal dialogs — the background is dimmed via jQuery UI Overlay — can be customized via CSS.
*   Closing by clicking outside the dialog — disabled by default for modal, can be enabled via `option('closeOnEscape', true)`.
*   Closing by the Escape key — controlled via `option('closeOnEscape', true/false)`.
*   For programmatic closing — use `LTS(dialog).close()` — triggers the jQuery UI `close` event.
*   For programmatic opening — use `LTS(dialog).open()` — triggers the jQuery UI `open` event.
*   Dialog events can be handled via LOTIS hooks — `check`, `before`, `on` for the `open()` and `close()` methods.
*   The dialog automatically centers when positioned at `'center'` — jQuery UI calculates the position relative to the window.
*   Minimum width/height — controlled via `option('minWidth', value)` and `option('minHeight', value)`.
*   Maximum width/height — controlled via `option('maxWidth', value)` and `option('maxHeight', value)`.
*   To prevent closing — use `option('closeOnEscape', false)` and do not add a close button.
*   The close button (cross) — displayed by default — can be hidden via CSS `.ui-dialog-titlebar-close { display: none; }`.
*   For dynamically changing the title — use `$(var).dialog('option', 'title', 'New Title')`.
*   For dynamically changing sizes — use `$(var).dialog('option', 'width', 600)`.
*   For checking state — use `$(var).dialog('isOpen')` — returns `true`/`false`.
*   For deferred initialization — set `autoopen(false)` and call `open()` manually.
*   For clearing content — use `$(var).empty()` before adding new content.

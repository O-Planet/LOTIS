[English](Grid.md) | [Русский](Grid.ru.md)

🚀 [Quick Start](../QuickStart.md)

# Grid Class

## Purpose
Adaptive CSS Grid layout class of the LOTIS framework, inheriting from `Div`. Designed for creating complex grid layouts with support for multiple display modes for different devices (mobile, desktop, etc.). Allows defining named areas (areas), rows (rows), columns (columns), and switching between layout configurations on the client without page reload. The class automatically generates JavaScript code for initializing the `ltsGrid` object (from the `Grid.js` file), manages device priorities and lifecycle hooks. Supports ready-made templates (cards, layout) for typical layout scenarios.

### Internal Structure

* **Display modes** — sets of configurations for different devices
* **Devices** — media queries and their processing
* **Grid configuration** — parameters of rows, columns, and areas
* **Priority system** — order of device application

### Key Features

#### Basic Configuration

* **Named areas** for placing elements
* **Adaptive settings** for different devices
* **Display modes** with different configurations
* **Automatic alignment** of elements

#### Client Capabilities

* **Dynamic adaptation** to window size
* **Mode switching**
* **Visibility management** of elements
* **Event handling** of configuration changes

## Properties

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$modes** | `array` | `private` | Multi-dimensional array of mode configurations. Structure: `[mode][device] = ['areas' => [...], 'rows' => ..., 'columns' => ...]`. Stores all defined layouts for each mode and device. |
| **$currentDevice** | `string` | `private` | Current device context during mode configuration. Default `'desktop'`. Used by methods `device()`, `areas()`, `rows()`, `columns()` for writing configuration to the correct `$modes` section. |
| **$activeMode** | `string/null` | `private` | Name of the currently active mode. Set by the `setMode()` method. All subsequent calls to `areas()`, `rows()`, `columns()` are written to this mode. |
| **$devices** | `array` | `private` | Associative array of device detection functions. Key — device name, value — JavaScript function for `matchMedia`. Default: `'mobile'` and `'desktop'` with a threshold of 768px. |
| **$priorityOrder** | `array` | `private` | Priority order of modes. Array of the form `['mobile' => 0, 'desktop' => 1]`. Used by the client script `ltsGrid` to determine which mode to apply when multiple conditions match. |
| **$defaultmode** | `string/null` | `private` | Name of the default mode. If set — automatically activated on page load via JS code in the `'ready'` area. |

### Inherited Properties

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$id** | `string` | `public` | Inherited from `Construct`. Unique object identifier, used as HTML `id` attribute and for linking with the client object in `lts.js`. |
| **$tagname** | `string` | `public` | Inherited from `Div`. Default `'div'`. Defines the HTML tag of the element during rendering. |
| **$classname** | `string` | `public` | Inherited from `Element`. CSS classes of the element, managed by methods `addclass()`, `removeclass()`, `hasclass()`. |

## Methods (Server Side, PHP)

### Constructor

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **__construct** | `string $id = ''` | — | Class constructor. Calls parent `Div` constructor, sets `display: grid`, initializes `$devices` arrays with mobile/desktop detection functions, calls `priority('mobile, desktop')`. |

### Configuration Setup

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **setMode** | `string $name` | `$this` | Sets the active mode for subsequent configuration. All calls to `areas()`, `rows()`, `columns()` until the next `setMode()` will be written to this mode. Supports fluent interface. |
| **device** | `string $device` | `$this` | Sets the current device context for writing configuration. Used in a chain: `setMode()->device()->areas()`. Supports fluent interface. |
| **areas** | `array $areas` | `$this` | Defines named grid areas for the current mode and device. Accepts an array of strings, where each string is a row of areas (e.g., `["header header", "menu content", "footer footer"]`). Generates CSS `grid-template-areas`. Supports fluent interface. |
| **rows** | `string $rows` | `$this` | Sets grid row sizes. Accepts a string in CSS Grid format (e.g., `"auto 1fr auto"`). Generates CSS `grid-template-rows`. Supports fluent interface. |
| **columns** | `string $columns` | `$this` | Sets grid column sizes. Accepts a string in CSS Grid format (e.g., `"200px 1fr"`). Generates CSS `grid-template-columns`. Supports fluent interface. |

### Content Placement

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **area** | `string $name` | `Div` | Returns or creates a wrapper object for a named area. Creates a `Div` with ID `"{$this->id}_area_{$name}"`, class `'GridWrapper'`, and attribute `data-grid-area`. Allows adding elements to a specific grid area via `$grid->area('content')->add($element)`. |
| **add** | `string $name`, `mixed $value = null` | `$this` | Overridden method for adding a child. If `$value` is an object with a `gridarea()` method — automatically assigns the area via `$value->gridarea($name)`. Otherwise — calls parent `Div::add()`. Supports fluent interface. |
| **to** | `string $name`, `...$elements` | `$this` | Adds multiple elements to the specified area. For each element calls `add($name, $element)`. Convenient wrapper for mass placement. Supports fluent interface. |

### Device Management

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **deviceQuery** | `string $name`, `string $jsFunction` | `$this` | Registers a custom device detection function. `$jsFunction` — JavaScript function code returning `true`/`false`. Example: `'() => window.matchMedia("(min-width: 1024px)").matches'`. Supports fluent interface. |
| **priority** | `string $orderString` | `$this` | Sets the priority order of modes. Accepts a string of device names separated by commas. Fills `$priorityOrder` with indices in order. Used by the client script `ltsGrid` to select the active mode. Supports fluent interface. |

### Visual Settings

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **justify** | `string $justify` | `$this` | Sets element alignment on the horizontal axis (`justify-items`). Valid values: `'start'`, `'end'`, `'center'`, `'stretch'`. Aliases: `'beg'`, `'begin'` = `'start'`. Supports fluent interface. |
| **flow** | `string $flow` | `$this` | Sets automatic element placement (`grid-auto-flow`). Valid values: `'row'`, `'column'`, `'row dense'`, `'column dense'`. Supports fluent interface. |
| **center** | `string $target = 'items'` | `$this` | Centering elements or container. `$target`: `'items'` — centers cell content, `'container'` — centers the entire grid in the parent, `'both'` — both options. Supports fluent interface. |
| **autoRows** | `string $size` | `$this` | Sets automatic row sizes (`grid-auto-rows`). Example: `'100px'`, `'minmax(50px, auto)'`. Supports fluent interface. |
| **autoColumns** | `string $size` | `$this` | Sets automatic column sizes (`grid-auto-columns`). Example: `'100px'`, `'minmax(50px, auto)'`. Supports fluent interface. |
| **centerInParent** | — | `$this` | Centers the grid in the parent container via `margin: 0 auto`. Supports fluent interface. |

### Layout Templates

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **defaultMode** | `string $mode` | `$this` | Sets the default mode. On page load, the client script automatically switches the grid to this mode via `LTS(grid).mode()`. Supports fluent interface. |
| **cards** | `string $minWidth = '250px'` | `$this` | Ready-made template for adaptive card grid. Sets `grid-template-columns: repeat(auto-fit, minmax({$minWidth}, 1fr))` and `gap: 10px`. Automatically rebuilds when screen width changes. Supports fluent interface. |
| **layout** | — | `$this` | Ready-made template for application layout. Creates a `'default'` mode with areas: header, menu, content, bar. Rows: `"auto 1fr auto"`, columns: `"200px 1fr"`, gap: `"10px"`. Supports fluent interface. |

### Checks

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **isrow** | — | `bool` | Checks if the grid fills by rows. Returns `true` if `grid-auto-flow` is not set or contains `'row'`. |
| **iscolumn** | — | `bool` | Checks if the grid fills by columns. Returns `true` if `grid-auto-flow` contains `'column'`. |

### Lifecycle

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **compile** | — | — | Generation of client-side JavaScript methods. Encodes `$modes`, `$priorityOrder`, `$devices` to JSON. Calls `compilemethod()` to create methods: `ltsGrid.init()`, `setMode()`, `deviceQuery()`, `mode()`, `priority()`, `grid()`, `check()`, `before()`, `on()`. If `$defaultmode` is set — adds JS code for mode activation to the `'ready'` area. Calls `parent::compile()`. |

## Client Methods Available via LTS Object

These methods become available on the client via `LTS.get('{id}')` or `LTS(var)` after compilation. Actual logic is executed by the `ltsGrid` object from the `Grid.js` file.

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **LTS(grid).setMode(mode)** | `string $mode` | `this` | Switches the grid to the specified mode on the client. Calls `ltsGrid.setMode()` with the grid ID and mode name. Applies the `areas`, `rows`, `columns` configuration for the current device. Supports method chaining. |
| **LTS(grid).deviceQuery(device, func)** | `string $device`, `function $func` | `this` | Registers a custom device detection function on the client. `$func` — JavaScript function returning `true`/`false`. Updates the `$devices` array in the client configuration storage. Supports method chaining. |
| **LTS(grid).mode(name)** | `string $name` | `this` | Alias for `setMode()`. Switches mode by name. Calls `ltsGrid.mode()` with the grid ID. Supports method chaining. |
| **LTS(grid).priority(order)** | `string $order` | `this` | Changes the priority order of modes on the client. Accepts a string of device names separated by commas. Recalculates `$priorityOrder` and applies new logic for selecting the active mode. Supports method chaining. |
| **LTS(grid).grid** | — | `object` | Returns the internal grid object from `ltsGrid` for direct access to properties and methods. Used for debugging or advanced configuration. |
| **LTS(grid).check(f)** | `function $f` | `this` | Registers a validator hook for mode switching. `$f` — JavaScript function accepting mode data. If returns `false` — mode switching is canceled. Supports method chaining. |
| **LTS(grid).before(f)** | `function $f` | `this` | Registers a preprocessing hook before mode switching. `$f` — JavaScript function, can modify mode data. The return value is used for application. Supports method chaining. |
| **LTS(grid).on(f)** | `function $f` | `this` | Registers a postprocessing hook after mode switching. `$f` — JavaScript function, called after configuration is applied. Can be used for additional initialization. Supports method chaining. |

## Usage Examples

```php
// Creating a Grid
$grid = LTS::Grid();

// Defining an additional device
$grid->deviceQuery('watch', '() => window.innerWidth <= 600 && window.innerHeight <= 600');

// Defining the dashboard mode
$grid->setMode('dashboard')
     ->device('desktop')
        ->areas(["header header",
            "menu content",
            "bar bar"])
        ->rows("60px 1fr auto")
        ->columns("200px 1fr")
     ->device('mobile')
        ->areas(["header", "content", "bar"])
        ->rows("60px 1fr auto")
        ->columns("1fr")
     ->device('watch')
        ->areas(["header", "content"])
        ->rows("50px 1fr")
        ->columns("1fr");

// Defining the editor mode
$grid->setMode('editor')
     ->device('desktop')
        ->areas(["header header",
            "form form",
            "actions actions"])
        ->rows("60px 1fr auto")
        ->columns("1fr")
     ->device('mobile')
        ->areas(["header", "form", "actions"])
        ->rows("60px 1fr auto")
        ->columns("1fr")
     ->device('watch')
        ->areas(["header", "form"])
        ->rows("50px 1fr")
        ->columns("1fr");

// Mode priorities and default mode
$grid->defaultmode('dashboard')
    ->priority('watch,mobile,desktop');

// Wrappers for areas
$header = $grid->area('header')->capt('🌐 Grid Demo')->addclass('header');
$menu = $grid->area('menu')
    ->add(LTS::Div()->addclass('menu-item')->capt('🏠 Home'))
    ->add(LTS::Div()->addclass('menu-item')->capt('📊 Reports'))
    ->add(LTS::Div()->addclass('menu-item')->capt('⚙️ Settings'));
$content = $grid->area('content')->capt('Content loading...')->addclass('content');
$bar = $grid->area('bar')->capt('Ready to work')->addclass('status-bar');
$form = $grid->area('form')->capt('Edit form')->addclass('form'); 
$actions = $grid->area('actions')->capt('Action buttons')->addclass('actions'); 

// --- Control buttons ---
$btnDashboard = LTS::Button()
    ->capt('📊 Dashboard')
    ->click(
<<<JS
    LTS(grid).mode('dashboard')
JS
);

$btnEditor = LTS::Button()
    ->capt('✏️ Editor')
    ->click(
<<<JS
    LTS(grid).mode('editor')
JS
);
```

## Notes
*   Class extends `Div`, therefore inherits all Flexbox methods (`flex()`, `row()`, `column()`, `content()`, `align()`, `gap()`, etc.), child management (`add()`, `del()`, `getchilds()`, `move()`), and styling (`css()`, `width()`, `height()`).
*   Class extends `Element`, `Quark`, `Construct`, therefore supports full object lifecycle: `compile()`, `shine()`, `childs()`, `addinspace()`, as well as hooks (`$check`, `$before`, `$on`, etc.).
*   **Grid.js Dependency:** For correct operation of client methods, the `Grid.js` file must be loaded, which contains the implementation of the `ltsGrid` object. The PHP `Grid` class only generates configuration and method calls.
*   All grid configuration methods return `$this` to support fluent interface and method chaining.
*   Modes allow defining multiple layout configurations and switching between them on the client without page reload.
*   Devices are defined via JavaScript `matchMedia` functions — default `'mobile'` (≤768px) and `'desktop'` (>768px).
*   Priority determines the order of device checking — the first matching condition activates the corresponding mode.
*   The `area()` method creates a `Div` wrapper with the `data-grid-area` attribute — this allows placing elements in named areas via CSS Grid.
*   The overridden `add()` method automatically assigns the area via `gridarea()` if the object supports this method — simplifies element placement in the grid.
*   The `compile()` method generates 8 client methods via `compilemethod()` — all become available via `LTS.get('{id}')` on the client.
*   Hooks `check()`, `before()`, `on()` for client methods work similarly to `Events` hooks — allow injecting user logic into the mode switching process.
*   Ready-made templates `cards()` and `layout()` provide typical configurations for common scenarios — adaptive cards and application layout.
*   The `defaultMode()` method automatically generates JS code for mode activation on load — no explicit client call required.
*   Mode configurations are encoded to JSON and passed to the client during rendering — all data is available in `ltsGrid` without additional requests.
*   The `ltsGrid` object automatically tracks the browser window `resize` event and applies the appropriate mode when screen size changes (the `handleResize()` method).
*   Methods `isrow()` and `iscolumn()` check the `grid-auto-flow` value via the CSS object — return the actual state after all changes.
*   Grid objects are automatically registered in the namespace via the inheritance mechanism of `addinspace()` from the `Quark` class.
*   During rendering, all CSS properties set via class methods are added to the CSS object with the selector `#{$this->id}`.
*   Child elements are added via methods `add()`, `to()`, `area()->add()` — the addition order determines the display order in the DOM.
*   Class does not implement its own `shine()`, `childs()` methods — they are inherited from `Div` and `Quark` without changes.
*   For creating adaptive layouts, chained calls are recommended: `$grid->setMode('default')->device('desktop')->areas([...])->rows(...)->columns()->device('mobile')->areas([...])`.
*   Client methods support lifecycle hooks — this allows validating, modifying, and logging mode switching on the browser side.
*   When switching modes, `ltsGrid` automatically hides areas (`GridWrapper`) that are not in the new configuration, and shows active ones.

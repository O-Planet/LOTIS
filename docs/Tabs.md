[English](Tabs.md) | [Русский](Tabs.ru.md)

🚀 [Quick Start](../QuickStart.md)

# Tabs Class

## Purpose
A tabbed interface class for the LOTIS framework, inheriting from `Div`. Designed for creating content section switchers using the jQuery UI Tabs widget. Allows grouping content into panels where only one active tab is displayed at a time. Supports horizontal and vertical tab positioning, collapsible panels, activation by click or mouse hover. The class automatically generates HTML structure for tabs (`<ul>` list with headers and `<div>` with content), creates client-side JavaScript code for widget initialization, and provides methods for programmatic tab switching.

### Key Features

* **Horizontal** and **vertical** tab positioning
* **Collapsible** tabs
* **Mouse hover** events
* **Dynamic** tab addition

## Properties

| Property | Type | Visibility | Description |
|----------|------|------------|-------------|
| **$vertical** | `bool` | `public` | Flag for vertical tab positioning. Default `false` (horizontal). Controlled via `vertical()` method. |
| **$ul** | `Element` | `public` | Container object for tab headers. Created in constructor as `<ul>` element, added to children. Used by jQuery UI Tabs for navigation. |

### Inherited Properties

| Property | Type | Visibility | Description |
|----------|------|------------|-------------|
| **$id** | `string` | `public` | Inherited from `Construct`. Unique object identifier, used as HTML `id` attribute. |
| **$tagname** | `string` | `public` | Inherited from `Div`. Default `'div'`. Defines HTML tag of element during rendering. |
| **$classname** | `string` | `public` | Inherited from `Element`. Automatically adds class `'Tabs'` in constructor. |

## Methods

### Constructor

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **__construct** | `string $id = ''` | — | Class constructor. Calls parent `Div` constructor, adds CSS class `'Tabs'`, creates `$ul` object of type `Element` with tag `'ul'`, adds it to children. Sets `$vertical` to `false`. |

### Tab Creation Methods

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **newtab** | `string $caption` | `Div` | Creates new tab. Creates `Div` object with class `'Tab'`, sets `$caption`, adds to children. Returns tab object for adding content. |

### Display Configuration Methods

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **collaps** | — | `$this` | Enables collapsible mode for all tabs. Sets option `'collapsible'` to `true`. Supports fluent interface. |
| **onmouse** | — | `$this` | Enables tab activation on mouse hover. Sets option `'event'` to `'mouseover'`. Supports fluent interface. |
| **vertical** | `bool $v = true` | `$this` | Sets vertical tab positioning. Saves to `$vertical`. During compilation generates JS code for adding classes `'ui-tabs-vertical'` and `'ui-helper-clearfix'`. Supports fluent interface. |

### Lifecycle

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **compile** | — | — | Generates client JavaScript methods. Finds all children with class `'Tab'`, for each creates `<li>` element with `<a>` link (text from `$caption`, href to tab ID). Clears `$caption` on tabs. Encodes options via `json_encode()`. Creates JS object with methods: `vertical()`, `horizontal()`. Adds JS initialization code to `'ready'` area: `jQuery('#{$this->id}').tabs({options})`. If `$vertical === true` — adds `vertical()` call. Calls `parent::compile()` at end. |

## Client-Side LTS Object Methods

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **LTS(tabs).vertical()** | — | — | Switches tabs to vertical mode. Adds classes `'ui-tabs-vertical'`, `'ui-helper-clearfix'` to container, changes `<li>` element classes from `'ui-corner-top'` to `'ui-corner-left'`. |
| **LTS(tabs).horizontal()** | — | — | Switches tabs to horizontal mode. Removes classes `'ui-tabs-vertical'`, `'ui-helper-clearfix'`, changes `<li>` element classes from `'ui-corner-left'` to `'ui-corner-top'`. |

## Usage Examples

```php
// Creating tab system
$tabs = LTS::Tabs();

// Adding tabs
$tab1 = $tabs->newtab('Tab 1');
$tab2 = $tabs->newtab('Tab 2');

// Adding content to tabs
$tab1->add(LTS::Html('p')->capt('Content of first tab'));
$tab2->add(LTS::Html('p')->capt('Content of second tab'));

// Display configuration
$tabs->vertical()   // Vertical positioning
     ->collaps();   // Mouse hover event
```

## Notes
*   Class extends `Div`, therefore inherits all Flexbox methods, child management, and styling.
*   Class extends `Element`, `Quark`, `Construct`, therefore supports full object lifecycle and hooks.
*   **jQuery UI dependency:** Tabs use jQuery UI Tabs — requires `jquery-ui.css` and `jquery-ui.js` connection in `Space`.
*   All configuration methods return `$this` to support fluent interface and chained calls.
*   Tabs created via `newtab()` — each tab gets class `'Tab'` for identification during compilation.
*   During compilation, `<li><a>` header created for each tab — text taken from `$tab->caption`, link points to tab ID.
*   Headers added to `$ul` — this is standard structure for jQuery UI Tabs.
*   `compile()` method generates 2 client methods: `vertical()`, `horizontal()` — available via `LTS.get('{id}')`.
*   Tab options encoded via `json_encode()` with `JSON_UNESCAPED_UNICODE` — Cyrillic in headers preserved without escaping.
*   Vertical mode switched via CSS classes — requires additional styling via jQuery UI CSS.
*   Mouse hover activation set via `'event'` option — default activation is by click.
*   Collapsible mode allows hiding all tabs — by default in jQuery UI one tab is always open.
*   For customizing appearance — use CSS classes `.Tabs`, `.Tab`, `.ui-tabs`.
*   `vertical()` and `horizontal()` methods on client change classes — do not recreate widget.
*   Class does not implement switch animations — uses standard jQuery UI animations.
*   Tabs can contain any child elements — `Div`, `Form`, `Input`, `DataTable`, etc.
*   During compilation, tabs with empty `$caption` — header created empty.
*   `collaps()` method sets `'collapsible'` option — can be combined with other options.
*   `onmouse()` method sets `'event'` option — overrides default click behavior.
*   During compilation `$tab->caption = ''` — cleared after header creation.
*   To preserve caption — use separate property.
*   Tabs compatible with `Dialog` — can be placed inside modal window.
*   For default horizontal mode — do not call `vertical()`.
*   During compilation `$optionsstr` — encoded via `json_encode()` with `JSON_UNESCAPED_UNICODE` flag.
*   `newtab()` method returns `Div` — allows chained content addition.
*   To hide tabs — use `css()->add('display', 'none')`.
*   Tabs integrate with signal system — can send signals on switch.
*   For multilingual tabs — use `$lang->say()` in `$caption`.
*   During compilation `<li><a>` headers created automatically — no explicit addition required.
*   Tab switching can be controlled programmatically via jQuery UI API methods.
*   Active tab index can be retrieved/modified via jQuery UI `option('active')` method.
*   Tabs support lazy loading — content loaded on first activation if configured.
*   For dynamic tab addition after initialization — use jQuery UI `add()` method.
*   Tab removal supported via jQuery UI `remove()` method — cleans up DOM and event handlers.
*   Disabled tabs styled differently — use `option('disabled', [indexes])` to disable.
*   Tab load event can trigger AJAX content fetching — useful for dynamic data.
*   For responsive design — combine with CSS media queries to switch horizontal/vertical modes.
*   Tabs maintain state across page reloads if combined with localStorage or URL hash.
*   Accessibility supported via ARIA attributes — jQuery UI handles keyboard navigation.
*   Tab height can be set uniformly via CSS or jQuery UI `heightStyle` option.
*   Nested tabs supported — create `Tabs` instance inside another tab's content.
*   For custom tab icons — add `<span>` with icon class inside `<a>` element during compilation.
*   Tab close buttons can be added via custom template — requires jQuery UI extension.
*   Drag-and-drop tab reordering supported via jQuery UI Sortable integration.
*   Tab animation duration configurable via jQuery UI `show`/`hide` options.
*   For SEO — ensure tab content is present in HTML source, not loaded via AJAX only.

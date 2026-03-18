[English](Space.md) | [Русский](Space.ru.md)

🚀 [Quick Start](../QuickStart.md)

# Space Class

## Purpose
The central rendering class of the LOTIS framework, inheriting from `Element`. Responsible for transforming object metadata into a ready HTML document or JSON metadata for client-side assembly. Serves as the entry point for final application assembly — coordinates lifecycle execution of all objects, distributes elements by type (CSS, JS, HTML, VARS), forms DOM structure, and generates the final page. The class supports two operating modes: server-side rendering of full HTML and client-side rendering via metadata transfer to the browser. All application objects must be created within the Space context for proper registration and rendering.

### Key Features

* **Metadata processing**
* **HTML document assembly**
* **DOM structure management**
* **Resource management** (CSS, JS)
* **Interface component assembly**

## Properties

| Property | Type | Visibility | Description |
|----------|------|------------|-------------|
| **$jqueryui** | `bool` | `public` | jQuery UI connection flag. If `true` — links to `jquery-ui.css` and `jquery-ui.js` are added to `<head>`. Default `true`. |
| **$ltsjs** | `bool` | `public` | Client core `lts.js` connection flag. If `true` — script is added to `<head>`. Default `true`. |

### Static Properties

| Property | Type | Visibility | Description |
|----------|------|------------|-------------|
| **$title** | `string` | `public static` | Page title. Used for generating `<title>` tag. |
| **$keywords** | `string` | `public static` | Page keywords. Generates `<meta name="keywords">` meta tag. |
| **$description** | `string` | `public static` | Page description. Generates `<meta name="description">` meta tag. |
| **$meta** | `string` | `public static` | Additional meta tags. Inserted directly into `<head>`. |
| **$scriptversion** | `string` | `public static` | Script version. Added as query parameter `?version=...` to CSS/JS file paths for cache control. |

### Inherited Properties

| Property | Type | Visibility | Description |
|----------|------|------------|-------------|
| **$storage** | `array` | `public` | Inherited from `Quark`. Contains sections for organizing elements before rendering: `'elements'` (source metadata), `'DOM_css'`, `'DOM_link'`, `'DOM_scripts'`, `'DOM_ready'`, `'DOM_eventsready'`, `'DOM_body'`, `'DOM_childs'`. |

## Methods

### Constructor

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **__construct** | `string $id = ''` | — | Class constructor. Calls parent `Element` constructor, initializes `'elements'` section with empty array in storage. |

### Metadata Rendering

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **metadata** | `Element $element`, `bool $ret = true` | `array/null` | Extract metadata without HTML rendering. Executes `compile()`, `create()`, `Events::build()`. If `$ret == true` — returns array of elements from `'elements'` section. If `false` — returns `null`. Used for client-side rendering via `clientbuild()`. |

### WEB Page Building

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **html** | — | — | Entry point for HTML rendering. Calls `build($this)` to start assembly process. |
| **build** | `Element $element` | `$this` | Main page assembly method. Sequentially calls: `compile()` for element metadata compilation, `create($this)` for object creation in space, `Events::build()` for handling incoming AJAX event requests. Then calls `DOMBuilder()` for HTML generation and browser output. |

### Metadata Rendering Helper Methods

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **processCSS** | `array $el` | — | CSS element processing. Iterates through styles array `$el['style']`, forms CSS rules string. If key is numeric — value treated as full rule, otherwise — as `property: value` pair. Saves result to `'DOM_css'` section with selector from `$el['class']`. |
| **processLink** | `array $el` | — | External links processing (CSS/JS files). Generates `<link>` tag with `href` and `rel` attributes. Saves to `'DOM_link'` section. |
| **processScript** | `array $el` | — | External scripts processing. Generates `<script src="...">` tag. Supports `async` and `defer` attributes — if present in `$el`, added to tag. Saves to `'DOM_link'` section. |
| **processFile** | `array $el` | — | File resources processing. Checks file existence at `$el['filename']`, reads content via `file_get_contents()`. Forms record for `'DOM_body'` section with hierarchy support (parent-child). If `$id` specified — registers element by ID, otherwise — adds as root. |
| **processVARS** | `array $el` | — | Global variables processing. Generates JavaScript initialization code `LTS.vars("{id}", {body})` where `{body}` — JSON representation of variables storage. Saves to `'DOM_scripts'` section. |
| **processJS** | `array $el` | — | JavaScript element processing. Depending on `$el['area']` (`'script'` or `'ready'`) and `$el['on']` flag, forms different code: functions in `<head>`, jQuery event handlers for `ready`, anonymous scripts. If `$el['eventsready'] == true` — saves to `'DOM_eventsready'`, otherwise — to `'DOM_scripts'` or `'DOM_ready'`. |
| **processHTML** | `array $el` | — | HTML element processing. Generates opening tag with `id`, `class`, `attr` attributes. Forms record for `'DOM_body'` section with fields: `'tag'`, `'tagname'`, `'parent'`, `'childs'`, `'caption'`. Supports hierarchy — if `$parent` specified — adds `$id` to `'childs'` array of parent element. |
| **processOther** | `array $el` | — | Unknown type element processing. Creates empty record in `'DOM_body'` for object registration in space without visual representation. If `$parent` not specified — adds ID to `'DOM_childs'` as root element. |

### WEB Page Building Helper Methods

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **DOMBuilder** | `array $elements = null` | — | Distributes elements by type for subsequent rendering. Initializes storage sections (`'DOM_css'`, `'DOM_link'`, `'DOM_scripts'`, `'DOM_ready'`, `'DOM_eventsready'`, `'DOM_body'`, `'DOM_childs'`). Iterates through elements array and calls corresponding `process*()` methods depending on `'type'` field. After processing all elements, calls `__builddoc()` for final HTML generation. |
| **__builddoc** | — | — | Final HTML document generation. Forms structure `<!DOCTYPE html><html><head>...<body>...</body></html>`. In `<head>` adds: meta tags (charset, description, keywords, title), jQuery/jQuery UI/lts.js connections, external CSS/JS class files, inline styles from `'DOM_css'`, scripts from `'DOM_scripts'`, `jQuery(document).ready()` initialization code with contents of `'DOM_ready'` and `'DOM_eventsready'`. In `<body>` recursively builds DOM tree via `__buildchild()` for elements from `'DOM_childs'`. Outputs result via `echo`. |
| **__buildchild** | `string $id` | `string` | Recursive child element assembly. Gets element record from `'DOM_body'` by ID. If `'body'` exists — returns as-is (for files). Otherwise — forms opening tag, content (`'caption'`), recursively processes all children from `'childs'`, closing tag. Returns assembled HTML string. |
| **clientbuild** | `Element $element` | — | Alternative assembly mode for client-side rendering. Executes `compile()`, `create()`, `Events::build()`, extracts metadata via `get('elements')`. Generates minimal HTML scaffold with jQuery, jQuery UI, lts.js, `lotisbuilder.js` connections. In `<head>` adds initialization script with global variables: `window.__LOTIS_METADATA__` (element metadata), `window.__LOTIS_CONFIG__` (page configuration), `window.__lts_classes` (class list). After `DOMContentLoaded` calls `LotisBuilder.build()` for client-side interface assembly. |

## Usage Examples

```php
// Creating space
$space = LTS::Space('main-space');

// Setting metadata
Space::$title = 'My Page';
Space::$description = 'Page description';

// Adding elements
$element = LTS::Element('main-element');
$space->add($element);

// Building HTML
$space->html();
```

## Notes
*   Class extends `Element`, therefore inherits all methods for managing children, styles, attributes, and events.
*   Class extends `Quark` and `Construct`, therefore supports full object lifecycle: `compile()`, `shine()`, `childs()`, `addinspace()`.
*   Static properties (`$title`, `$keywords`, `$description`, `$meta`, `$scriptversion`) are managed globally — set before calling `build()`.
*   `build()` method is main entry point for server-side rendering — after its call, HTML is output to browser and script execution completes.
*   `clientbuild()` method used for scenarios where interface is built on client — server transmits only metadata in JSON format.
*   `DOMBuilder()` method automatically calls `Events::build()` — this ensures handling of incoming AJAX event requests before page rendering.
*   Scripts distributed across three execution sections: `'DOM_scripts'` (in `<head>` before DOM load), `'DOM_ready'` (after `DOMContentLoaded`), `'DOM_eventsready'` (event handlers with `eventsready` flag).
*   DOM element hierarchy built via `'parent'` and `'childs'` fields — each element knows its parent and list of children by ID.
*   `__buildchild()` method recursively traverses element tree — traversal order corresponds to order of addition to `'childs'`.
*   External resource connections (jQuery, jQuery UI, lts.js) controlled by `$jqueryui` and `$ltsjs` flags — can be disabled if needed.
*   Script versioning via `$scriptversion` adds query parameter to all CSS/JS files — useful for browser cache invalidation on update.
*   `Space` class is singleton in application context — usually one instance created via `LTS::Space()`.
*   All objects created within space context automatically registered in `'elements'` section via `addinspace()` method.
*   `metadata()` method can be used for preview or debugging — returns metadata array without HTML output.
*   For client-side rendering via `clientbuild()`, `lotisbuilder.js` file required — it handles metadata-to-DOM transformation in browser.
*   Class does not implement `shine()` and `compile()` methods itself — they are inherited from `Element` and `Quark` respectively.
*   Error and exception handling performed via `Logger` class — all critical errors logged before execution completion.
*   For proper event handling, `Events::build()` must be called before HTML generation — this ensured by sequence in `build()` method.
*   Global `$_SESSION` variables used by `Vars` class for state synchronization — Space does not manage session directly.
*   All HTML attributes and content escaped via `htmlspecialchars()` for XSS attack protection.
*   Class supports CLI mode operation — `php_sapi_name()` check used in `Logger` to determine output mode.
*   Method chaining via fluent interface supported for most configuration methods.
*   Element processing order in `DOMBuilder()` affects final HTML output — CSS before JS, scripts before body content.
*   Recursive `__buildchild()` handles nested elements of arbitrary depth — no hardcoded nesting limit.
*   Metadata extraction via `metadata()` does not trigger HTML output — useful for API responses or AJAX endpoints.
*   Client build mode via `clientbuild()` reduces server load — heavy DOM manipulation delegated to browser.
*   Global configuration via static properties allows centralized page metadata management.
*   Space class coordinates all framework components — serves as orchestration layer for application rendering.

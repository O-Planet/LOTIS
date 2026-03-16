[English](JS.md) | [Русский](JS.ru.md)

🚀 [Quick Start](../QuickStart.md)

# JS Class

## Purpose
Client-side JavaScript code management class of the LOTIS framework, inheriting from `Quark`. Designed for server-side generation of JavaScript code that executes in the browser. Allows embedding scripts directly into the HTML document or connecting external JS files. The class automatically converts PHP objects into client-side references by their ID, processes special syntax constructs (`LTS(var)`, `$(var)`) for accessing framework objects, and manages script execution through areas (areas). All JS objects are added to the universe (Space) and rendered in appropriate places in the HTML document.

## Properties

| Property | Type | Description |
|----------|-----|----------|
| **$area** | `string` | Script execution area. Valid values: `'script'` (in `<head>`), `'ready'` (after DOM load), `'create'` (when creating an object). Default `'script'`. |
| **$compile** | `bool` | Code compilation flag. If `true` — special constructs `LTS(var)` and `$(var)` are processed for variable value substitution. Default `true`. |

### Inherited Properties

| Property | Type | Description |
|----------|-----|----------|
| **$id** | `string` | Inherited from `Construct`. Unique identifier, used for linking server and client objects. |
| **$type** | `string` | Set to `'JS'` in the constructor. Defines the object type for filtering and processing. |
| **$tagname** | `string` | Inherited from `Quark`. Not used for JS objects (empty string). |
| **$classname** | `string` | Inherited from `Quark`. CSS class of the object, can be used for selectors. |
| **$caption** | `string` | Inherited from `Quark`. Not used for JS objects. |
| **$storage** | `array` | Inherited from `Quark`. Storage contains sections `'childs'` (scripts), `'element'` (DOM element), `'localvars'` (local variables for substitution). |
| **$jscreate** | `JS/null` | Inherited from `Quark`. Reference to the JavaScript object for client-side logic. |

## Methods

### Lifecycle

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **__construct** | `string $id = ''` | — | Class constructor. If `$id` equals `'script'`, `'ready'`, or `'create'` — sets `$area` to this value and clears ID. Otherwise — uses `$id` as the identifier, `$area` is set to `'script'`. Calls parent `Quark` constructor, sets `$type` to `'JS'`, `$compile` to `true`. |
| **compile** | — | — | Empty implementation. Inherited from `Construct`, overridden without logic — JS objects do not require preliminary metadata compilation. |
| **shine** | `Element $parent = null` | — | Generation of JS elements. Iterates through children in the `'childs'` section. If the value contains `'.js'` — creates an entry for the `<script src="...">` tag (supports `LTS:` prefix for loading local files). Otherwise — forms an array of inline scripts with variable processing via `javascript()`. Adds generated entries to `$space->storage['elements']` for subsequent rendering in HTML. |
| **childs** | — | — | Empty implementation. Inherited from `Construct`, overridden without logic — JS objects do not create child DOM elements in the traditional sense. |

### String Transformation with LTS(var) and $(var) Search and Replace

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **javascript** | `string $body`, `mixed $local = false` | `string` | Static method for processing JavaScript code. Replaces syntax constructs: `->` with `.`, `LTS(var)` with `LTS.get('id')`, `$(var)` with `jQuery('#id')`. Calls `resolveVar()` for substituting variable values from local storage or global scope. Returns processed JS code. |
| **resolveVar** | `string $name`, `mixed $local`, `string $left`, `string $right` | `string` | Private static method for variable resolution. Checks for the variable in local storage (`$local`), then in `$GLOBALS`. If found — calls `getVarReplacement()`. If not found — returns the original name with wrapper (`$left + $name + $right`). |
| **getVarReplacement** | `mixed $value`, `string $left`, `string $right` | `string` | Private static method for getting variable replacement. For objects with `$id` — returns `LTS.get('id')`. For arrays/objects — encodes to JSON. For strings — escapes via `json_encode()`. For scalars — returns the value as is. |
| **paramobject** | `array $arr` | `string` | Static method for converting a PHP array to a JavaScript object. Processes keys (escapes special characters), values (calls `encodeJSValue()`). Returns a formatted JavaScript object string with indentation. |
| **encodeJSValue** | `mixed $value` | `string` | Private static method for encoding PHP values to JavaScript format. For objects with `id()` method — returns `LTS.get('id')`. For arrays — recursively calls `paramobject()`. For string-functions — returns as is. For booleans, null, scalars — corresponding JS representation. |

## Usage Examples

```php
// Creating a JavaScript container
$js = LTS::JS('script');

// Adding an external file
$js->add('path/to/script.js');

// Adding a function to the scripts area
$js->add('hello()',
<<<JS
    alert("Hello, world!");
JS
);

// Adding an event handler for an object
$myelement->js('ready')->add('change',
<<<JS
    alert("Object changed!");
JS
);

// Adding inline code to the ready area
$myelement->js('ready')->add(
<<<JS
    $(myelement).css('display', 'block');
    LTS(myelement).mymethod();
JS
);
```

## Notes
*   Class extends `Quark`, therefore supports all child management methods: `add()`, `get()`, `set()`, `del()`, `getchilds()`, etc.
*   Scripts are added via the `add()` method as key-value pairs, where key is the script name/identifier, value is JS code or file path.
*   External JS files are determined by the presence of the `'.js'` substring in the value — such entries are processed separately and generate `<script src="...">` tags.
*   The `LTS:` prefix in the file path indicates loading a local file from the file system (via `file_get_contents()`).
*   Three execution areas: `'script'` — in `<head>` before DOM load, `'ready'` — after `DOMContentLoaded`, `'create'` — when creating an object (lazy initialization).
*   Special syntax `LTS(var)` is replaced with `LTS.get('id')` for accessing framework objects on the client.
*   Special syntax `$(var)` is replaced with `jQuery('#id')` for accessing DOM elements via jQuery.
*   The `javascript()` method automatically converts PHP objects into client-side references by their `$id` property.
*   Local variables for substitution are stored in the `'localvars'` section of the `$storage`.
*   Methods `compile()` and `childs()` are overridden as empty — the JS object lifecycle is limited to the `shine()` stage.
*   All scripts added through the JS object are automatically registered in the namespace via the inheritance mechanism of `addinspace()` from the `Quark` class.
*   The `JS` class is used by the `Quark::js()` method for lazy creation of JavaScript objects on first access.
*   During code compilation (`$compile == true`), all PHP variables passed to the JS context are automatically serialized to JavaScript format.
*   For functions in array values, the `function(` pattern is checked — such strings are not escaped but inserted as executable code.
*   JS objects have no visual representation in the DOM — they only generate entries in the namespace for insertion into `<script>` tags.

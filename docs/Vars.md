[English](Vars.md) | [Русский](Vars.ru.md)

🚀 [Quick Start](../QuickStart.md)

# Vars Class

## Purpose
Global variables management class of the LOTIS framework. Designed for storing and synchronizing data between server and client within a user session. The server side ensures saving values in `$_SESSION`, restoring state on page load, and automatic synchronization during AJAX event requests. The client side provides a JavaScript API for working with variables in the browser, including support for localStorage for client-side caching. All class instances are registered in a static registry for automatic synchronization during event processing.

## Properties (Server Side, PHP)

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$storage** | `array` | `public` | Associative array for storing variables. Keys are variable names, values are arbitrary PHP data (scalars, arrays, objects). |
| **$elements** | `array` | `private` | Array of metadata for generating client-side JavaScript code. Filled by the `shine()` method and used when rendering the space. |
| **$all** | `static array` | `public static` | Static registry of all created Vars objects by keys of the form `"__globals{id}"`. Used by the `Events::build()` class for automatic variable synchronization during event processing. |

### Inherited Properties

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$id** | `string` | `public` | Inherited from `Construct`. Unique identifier of the Vars object. If not passed — generated automatically. Used as part of the session key. Allows creating separate named storage spaces for global variables. |
| **$type** | `string` | `public` | Inherited from `Construct`. Set to `'Vars'` in the constructor. Defines the object type for filtering and processing. |
| **$space** | `Space` | `public` | Inherited from `Construct`. Reference to the namespace where generated elements are added. |

## Methods (Server Side, PHP)

### Constructor

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **__construct** | `string $id = ''` | — | Class constructor. Calls parent `Construct` constructor, sets `$type` to `'Vars'`, initializes `$storage` as an empty array. Calls `restore()` to load data from the session. Registers the object in the static registry `Vars::$all` by the key `"__globals{$id}"`. |

### Data Management

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **restore** | — | `$this` | Restores variable values from the PHP session. Checks for the key `"__globals{$id}"` in `$_SESSION`. If data exists and is an array or object — converts to an array and loads into `$storage`. Returns `$this` to support fluent interface. |
| **store** | — | `$this` | Saves current `$storage` values to the PHP session. Writes the array to `$_SESSION["__globals{$id}"]`. Called automatically during event processing via `Events::build()`. Returns `$this` to support fluent interface. |
| **clearsession** | — | `bool` | Removes the object's data from the PHP session. Finds the key `"__globals{$id}"` in `$_SESSION` and removes it via `unset()`. Does not affect `$storage` in memory. Returns `true` on success. |
| **clearvalues** | `bool $session = true` | `bool` | Clears values of all variables while preserving keys. Iterates through `$storage` and sets each value to an empty string. If `$session == true` — also calls `clearsession()`. Returns `true`. |
| **clear** | `bool $session = true` | `bool` | Completely clears the variable storage. Sets `$storage` to an empty array. If `$session == true` — also calls `clearsession()`. Returns `true`. |
| **get** | `string $name` | `mixed/null` | Gets the value of a variable by name. If the key exists in `$storage` — returns the value. Otherwise returns `null`. Does not throw exceptions when the key is missing. |
| **set** | `string $name`, `mixed $value = null` | `$this` | Sets the value of a variable. If `$value === null` — removes the variable from `$storage` via `unset()`. Otherwise writes the value by key. Returns `$this` to support fluent interface. |
| **value** | `string $name`, `mixed $value = null` | `mixed/$this` | Universal getter/setter method. If `$value === null` — works as `get()` (returns the value). If passed — works as `set()` (sets the value and returns `$this`). |
| **del** | `string $name` | `$this` | Removes a variable from storage. Finds the key in `$storage` and removes it via `unset()`. Does not affect the session until `store()` is called. Returns `$this` to support fluent interface. |

### Lifecycle

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **shine** | `Element $parent = null` | — | Generates metadata for client-side JavaScript. Encodes `$storage` to JSON via `json_encode()`. Fills `$elements` with an array containing type `'VARS'`, area `'script'`, object ID, and script body. Does not create a DOM element directly. |
| **addinspace** | — | — | Registers variables in the universe. Forms the key `"__globals{$id}"` and adds `$elements` to `$space->storage['elements']` via the `set()` method. Ensures data transfer to the client during page rendering. |

## Client Methods

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **constructor** | `string id` | — | Class constructor. Initializes the `id` property and an empty `values` object. Called automatically during initialization via `LTS.vars()`. |
| **get** | `string name` | `mixed/null` | Gets the value of a variable by name from the client-side `values` storage. If the key does not exist — returns `null`. |
| **set** | `string name`, `mixed value` | `this` | Sets the value of a variable in the client-side storage. If `value === undefined` or `null` — removes the variable from `values`. Returns `this` to support method chaining. |
| **varsname** | — | `string` | Returns the session/localStorage key name. Forms a string of the form `"__globals{id}"` or `"__globals"` if the ID is empty. |
| **store** | — | — | Saves current `values` to the server via an AJAX POST request. Sends data via `LTS.post('__ltssavevars', window.location.href, args)`. Data is saved in `$_SESSION` on the server. |
| **localsave** | `string name = null` | — | Saves values to the browser's localStorage. If `name` is specified — saves only one variable. If not specified — saves all `values`. Data is saved under the key from `varsname()`. |
| **localload** | `string name = null` | `mixed/bool` | Loads values from the browser's localStorage. If `name` is specified — loads and returns one variable. If not specified — loads all values into `values` and returns `true`. Returns `false` if data is not found. |

## Global LTS Object Functions for Working with Vars

| Function | Parameters | Returns | Description |
|---------|-----------|------------|----------|
| **LTS.vars(name, values)** | `string name`, `object values` | `Vars/null` | If `values` is passed — creates or updates a Vars object with the specified values. If not passed — returns an existing Vars object by name or `null`. For an object without an ID (name not specified), `"__globals"` is used. |
| **LTS.vars(varsname).values** | — | `object` | Direct access to the value storage of the Vars object. varsname is optional. If specified, works with the global variables vector `"__globals{id}"`, if not specified, works with `"__globals"`. |
| **LTS.vars(varsname).set(name, value)** | `string name`, `mixed value` | `Vars` | Setting a variable value on the client. |
| **LTS.vars(varsname).get(name)** | `string name` | `mixed` | Getting a variable value on the client. |
| **LTS.vars(varsname).store()** | — | — | Synchronizing client values with the server. |

## Usage Examples

### On the Server

```php
// Creating storage
$vars = LTS::Vars('global');

// Setting values
$vars->set('counter', 10);
$vars->value('name', 'John');

// Getting values
$value = $vars->get('counter');

// Saving to session
$vars->store();

// Clearing
$vars->clear();
```

### On the Client

```JS
// Getting values
const name = LTS.vars('global').get('name');
let counter = LTS.vars('global').get('counter');

// Setting values 
LTS.vars('global').set('counter', ++counter);

// Saving values to session
LTS.vars('global').store();

```

## Notes
*   Class extends `Construct`, therefore supports all lifecycle hooks: `$check`, `$before`, `$on`, `$checkchilds`, `$beforechilds`, `$onchilds`.
*   All Vars objects are automatically registered in the static registry `Vars::$all` — this is necessary for synchronization with the client during AJAX requests.
*   The session key is formed as `"__globals{$id}"` — with an empty ID it will be `"__globals"`.
*   The `store()` method is called automatically in `Events::build()` after processing each event — data is synchronized without explicit developer call.
*   The `value()` method supports two modes of operation: getter (without the second parameter) and setter (with the second parameter) — similar to jQuery `.val()`.
*   When setting a value to `null` via `set()`, the variable is removed from storage — this differs from setting an empty string.
*   The `clearvalues()` method resets values but preserves keys — useful for resetting a form without losing data structure.
*   The `clear()` method completely clears the storage — removes all keys and values.
*   Data in `$storage` can be of any type — arrays, objects, scalars. When saving to the session, PHP serialization occurs.
*   During rendering, `$storage` is encoded to JSON and passed to the client — complex data types are converted to JavaScript objects.
*   On the client, data is available via `LTS.vars('{id}').values` or `LTS.vars().values` for an object without an ID.
*   The `shine()` and `addinspace()` methods do not create visual elements — Vars is a functional object without DOM representation.
*   Class does not implement `compile()` and `childs()` methods — they are inherited from `Construct` without changes.
*   During event processing via `Events::build()`, all Vars objects automatically call `store()` — changes are saved to the session before sending the response to the client.
*   For clearing the session on user logout, it is recommended to explicitly call `clearsession()` or `clear(true)`.
*   Vars objects with the same ID share one session storage — creating a second object with the same ID will load the same data from the session.
*   The `restore()` method is called only in the constructor — for forced reload from the session, you need to explicitly call `restore()`.
*   All data modification methods (`set`, `value`, `del`, `clear`, `clearvalues`) return `$this` to support fluent interface and method chaining.
*   The client-side Vars.js class supports localStorage for saving data between page reloads without server synchronization.
*   The `localsave()` method is useful for saving form state or interface settings without server load.
*   The `localload()` method allows restoring data from localStorage on page load before receiving a response from the server.
*   Server synchronization via `store()` happens automatically during events — explicit call is required only for saving without events.
*   For the client side to work, the `Vars.js` file is required, which is connected automatically via the Space namespace.
*   During page rendering, Vars data is passed through the initialization script `LTS.vars("{id}", {body})` where `{body}` is the JSON representation of the storage.
*   Changes made on the client via `LTS.vars().set()` are not saved to the server until `store()` is called or an event is processed.
*   Client and server data are synchronized bidirectionally — changes on the server are passed to the client in the event response via the `__ltsVars` field.

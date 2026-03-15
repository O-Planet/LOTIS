[English](Events.md) | [Русский](Events.ru.md)

🚀 [Quick Start](../QuickStart.md)

# Events Class

## Purpose
Event management class of the LOTIS framework. Designed for organizing bidirectional communication between client and server without writing AJAX code. The server side implements event handler registration, client-side JavaScript code generation, processing of incoming POST requests, and execution of business logic. The client side provides data sending to the server, FormData serialization, management of global variables, and response handling. The class supports a lifecycle hook system for events (`check`, `before`, `on`), asynchronous and synchronous modes of operation, as well as global registration of all event instances for request routing.

### Workflow Structure

1. **Client Side:**
   * Forming the request
   * Sending data
   * Processing the response

2. **Server Side:**
   * Checking conditions
   * Executing logic
   * Returning the result

## Properties (Server Side, PHP)

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$async** | `bool` | `public` | Flag for asynchronous mode. If `true` — requests are executed asynchronously via `fetch`/`XMLHttpRequest`. If `false` — synchronously. Default `true`. |
| **$eventresult** | `mixed` | `public` | Result of the last processed event execution. Filled when calling the `join()` method. |
| **$checktrue** | `bool` | `public` | Flag indicating successful `check` hook validation. Set to `true` before event execution, can be changed by the validation hook. |
| **$checkerror** | `string` | `public` | Error message from validation. Filled if the `check` hook returns something other than `true`. |
| **$all** | `static array` | `public static` | Static registry of all created Events objects by their ID. Used for routing incoming POST requests to the correct handler. |

### Inherited Properties

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$id** | `string` | `public` | Inherited from `Construct`. Unique identifier of the Events object, used for client-server linking. |
| **$type** | `string` | `public` | Inherited from `Quark`. Set to `'Events'` in the constructor. Defines the object type for filtering and processing. |
| **$storage** | `array` | `public` | Inherited from `Quark`. Storage contains sections `'childs'` (event handlers), `'errors'` (error handlers). |

## ltsEvents Object (Client Side, JS)

| Property | Type | Description |
|----------|-----|----------|
| **async** | `bool` | Flag for asynchronous mode. Set by the server during compilation. Default `true`. |
| **script** | `string` | Path to the script for sending POST requests. Filled automatically when compiling events. |
| **post** | `function` | Method for sending an event to the server. Accepts the event name, object ID, and arguments. |

## Methods

### Constructor

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **__construct** | `string $id = ''` | — | Class constructor. Calls parent `Quark` constructor, sets `$type` to `'Events'`, `$async` to `true`. Registers the object in the static registry `Events::$all` by ID. |

### Event Programming

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **client** | `string $name`, `string $body` | `$this` | Adds a client-side result handler for the event execution on the server. Accepts the event header and JavaScript handler code. The code is added to the JS object via the `js()->add()` method. Supports fluent interface. `$name`, the event header, may contain a comma-separated list of parameters in parentheses, as in a regular JS function definition. |
| **server** | `string $name`, `callable $body` | `$this` | Adds a server-side event handler. Accepts the event name and PHP function/closure. If the name contains parameters in parentheses `name(params)` — extracts only the name. Adds the handler to the `'childs'` section. Supports fluent interface. The `$body` function must have a single argument, which receives the array of parameters specified when defining the event header in the `client` method. Even if the event is defined without parameters, specifying the argument is mandatory. The method must return a result, which will be passed to the client as the event execution response. |
| **error** | `string $name`, `string $body` | `$this` | Adds an error handler for the event. Accepts the event name and JavaScript error handler code. If the code does not start with `function` — wraps it in a function `function (error) { ... }`. Saves in the `'errors'` section with the key `{$this->id}_{$_name}`. |
| **add** | `string $name`, `callable $value = null` | `$this` | Overridden method for adding a child. If a closure is passed — processes the name syntax with parameters `name(params)`. Calls parent `Quark::add()`. Supports fluent interface. This method should be used for defining server event hooks. The hook name uses the event name with the prefix `check`, `before`, or `on`. Hooks, like the server event handler, accept a single argument, which receives the array of parameters received from the client when calling the event. The `before` hook is designed for processing and supplementing the argument array. It must return the array that will be passed to the main event handler as an argument. Even if no array transformation is required, the `before` hook must return this array as the result of its work. The `on` hook, in addition to the argument array, receives the result of the main server event handler execution as a second parameter. It is designed for transforming the result and must return it after its execution. |

### Server Request Processing

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **eventjoin** | `string $eventname`, `callable $event`, `array $args` | `mixed/false` | Internal method for executing the server event handler with hooks. Sequentially calls: `check` hook (if present — checks return of `true`), `before` hook (can modify arguments), main handler `$event`, `on` hook (receives the result). Returns the execution result or `false` on failed validation. |
| **join** | `string $id_eventname` | `bool` | Processing of an incoming POST request. Extracts the event name from `$id_eventname` (after `id_`). Parses `$_POST` and `$_FILES` into an array of arguments, decodes JSON fields by keys from `__ltsJSONKeys`. Calls `eventjoin()` to execute the handler. Saves the result in `$eventresult`. Returns `true` on success, `false` on failed validation or missing handler. |
| **build** | — | `void` | Main static method for processing the global POST event request. Called automatically by the `Space` class object when `__ltsEvent` is present in `$_POST`. Extracts the event name and object ID from POST data. Finds the object in the `Events::$all` registry, calls `join()`. Collects results of all `Vars::$all` variables in `__ltsVars`. Outputs a JSON response and terminates script execution via `exit()`. Although the method is called automatically when building the page, it is possible to programmatically separate the server event processing section from the code building the part of the application not involved in events. To do this, `LTS::Events()->build()` must be explicitly called in the program code. |

### Lifecycle

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **compile** | — | — | Generation of client-side JavaScript code for all events. Creates a `JS` object with the `'ready'` area. For each handler generates: hook functions (`check`, `before`, `on`), main event function with argument processing, FormData serialization, POST request via `ltsEvents.post()`, response and error handlers. Sets `$this->js()->type` to `'none'` after compilation. |

## Client Methods Available via LTS Object

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **LTS(events).{eventname}** | `...args` | — | Event call method on the client. Generated automatically when compiling Events.php. Accepts event arguments, calls `ltsEvents.post()` with correct parameters. After receiving the response, calls the client handler via `ltsEvents.__event{id}_{eventname}()`. |
| **LTS(events).check{eventname}** | `...args` | `bool` | Event validation hook. Generated during compilation if defined on the server. If returns `false` — the event is not sent to the server. |
| **LTS(events).before{eventname}** | `...args` | `mixed` | Event preprocessing hook. Generated during compilation if defined on the server. Can modify arguments before sending. If returns FormData — used as is, if an object — fields are added to arguments. |
| **LTS(events).on{eventname}** | `...args`, `result` | — | Event postprocessing hook. Generated during compilation if defined on the server. Called after receiving the response from the server with the execution result. |

### Helper Client Methods

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **ltsEvents.post** | `string name`, `string id`, `mixed args` | — | Sends an event to the server. Forms FormData with fields `__ltsEvent`, `__ltsEventId`, `__ltsVars`, `__ltsJSONKeys`. Serializes objects and arrays to JSON, adds files as is. Calls `LTS.post()` to send the request. Response handlers are set during compilation in Events.php. |
| **LTS.request** | `string name`, `function func` | — | Registers a successful response handler for the event. Called inside the generated code during compilation. Accepts response data, updates `LTS.vars()` from `__ltsVars`, calls the client event handler. |
| **LTS.error** | `string name`, `function func` | — | Registers an error handler for the event. Called inside the generated code during compilation or via the `error()` method on the server. Accepts the error object, outputs to console or executes user code. |

## Usage Example

```php
// Creating an events object
$events = LTS::Events('myEvents');

// Registering a server event
$events->server('calculate', function($args) {
    return $args['a'] + $args['b'];
});

// Registering a client result handler
$events->client('calculate(a, b)', function(result) {
    alert('Result: ' + result);
});

// Registering a client hook
$events->method('checkcalculate(a, b)',
<<<JS
    if(a < 0 || b < 0)
    {
        alert('Enter positive numbers!');
        return false;  
    }
    return true;
JS
);

// Registering a server hook
$events->add('oncalculate', function ($args, $result) {
    if($args['a'] == $args['b'])
        return $result * 2;
    else
        return $result;
});

// Example of calling an event
$button = LTS::Button()
    ->capt('Add 5 and 3')
    ->click(
<<<JS
        LTS(events).calculate(5, 3);
JS
);
```

## Notes
*   Class extends `Quark`, therefore supports all child management methods: `add()`, `get()`, `set()`, `del()`, `getchilds()`, etc.
*   Class extends `Construct`, therefore supports all lifecycle hooks: `$check`, `$before`, `$on`, `$checkchilds`, `$beforechilds`, `$onchilds`.
*   All Events objects are automatically registered in the static registry `Events::$all` — this is necessary for routing POST requests to the correct handler.
*   The `build()` method must be called before page rendering — this is usually done at the framework entry point via `Space::metadata()` or `Space::build()`.
*   Event lifecycle hooks: `check{eventname}` (validation), `before{eventname}` (argument preprocessing), `on{eventname}` (result postprocessing).
*   In asynchronous mode (`$async == true`), requests are executed via `fetch` or `XMLHttpRequest` without blocking the interface.
*   In synchronous mode (`$async == false`), requests block execution until a response is received — used rarely, for specific scenarios.
*   Error handlers are registered via the `error()` method and executed on the client when the server response fails.
*   The `compile()` method generates complex JavaScript code with FormData handling, JSON serialization, hooks, and response handlers — all this code is transparent to the developer.
*   For events with parameters `name(params)` — parameters are extracted from the name and used to generate the function signature on the client.
*   Event arguments may include data from `$_POST` and `$_FILES` — files are automatically added to the arguments array.
*   JSON fields are decoded by the list of keys from `__ltsJSONKeys` — this allows passing complex data structures in the POST request.
*   The result of event execution is returned to the client handler via the `{eventname}` field in the JSON response.
*   Global variables `Vars` are automatically synchronized between server and client via the `__ltsVars` field in the response.
*   The `client()` method is used for registering response handlers on the client side — the code is executed after receiving a successful response from the server.
*   The `server()` method is used for registering handlers on the server side — the function is called when a POST request with the event name is received.
*   The class does not implement `shine()` and `childs()` methods — they are inherited from `Quark` without changes, as Events does not create DOM elements directly.
*   Events objects have no visual representation — they only generate JavaScript code and process POST requests.
*   To call an event on the client, the syntax `LTS.get('{id}').{eventname}(args)` is used — the function is created automatically during compilation. It is also possible to call `LTS(var).{eventname}(args)`, where var is a PHP variable referencing an instance of the `Events` class.
*   The client side (Events.js) contains the `ltsEvents` object with the `post()` method — it is responsible for forming FormData and sending the request via `LTS.post()`.
*   The server side (Events.php) generates client code during compilation — all handlers, hooks, and settings are written to the JS object.
*   When processing events via `Events::build()`, all Vars objects automatically call `store()` — changes are saved to the session before sending the response to the client.
*   For the client side to work, the `Events.js` file is required, which is connected automatically via the Space namespace.
*   The `ltsEvents.post()` method automatically determines the data type — if arguments contain objects or arrays, they are serialized to JSON with the `<JSON>` prefix.
*   Files are passed via FormData without serialization — `ltsEvents.post()` checks instanceof File and adds as is.
*   Response handlers are set via `LTS.request()` inside the generated code — the developer does not call them explicitly.
*   Event processing errors are logged to the browser console via `console.error()` if no custom handler is set via `error()`.
*   Synchronization of `Vars` variables happens automatically — all changes on the server are passed to the client in the `__ltsVars` field of the JSON response.
*   For debugging events, `console.log()` can be used in client handlers and `Logger` on the server.
*   The class supports multiple events in one object — each event has a unique handler and hooks.
*   When compiling Events.php, a JS object with the `'ready'` area is created — the code is executed after DOM load.
*   The `$async` flag is set in the constructor — can be changed before calling `compile()` to switch the mode of operation.

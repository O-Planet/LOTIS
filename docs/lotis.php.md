[English](lotis.php.md) | [Русский](lotis.php.ru.md)

🚀 [Quick Start](../QuickStart.md)

# lotis.php File

## Purpose
Entry point and core of the LOTIS framework. Initializes session, processes incoming POST requests for synchronizing global variables (`Vars`) and navigation between pages. Defines constants for framework resource paths, registers class autoloader, initializes logging system. Contains abstract class `LTS` with factory methods for creating all framework components and helper functions for data manipulation.

## Constants

| Constant | Value | Description |
|----------|-------|-------------|
| **_LOTIS_INTERNET** | `false` | Internet mode flag. Disabled by default. |
| **_LOTIS_DIR** | `dirname(__DIR__) . '/newlotis/'` | Framework base directory. |
| **_LOTIS_JSDIR** | `'/newlotis/JS/'` | Path to JavaScript files directory. |
| **_LOTIS_CSSDIR** | `'/newlotis/CSS/'` | Path to CSS files directory. |

## Global Variables

| Variable | Type | Description |
|----------|------|-------------|
| **$__ltsinputcontent** | `string` | Raw POST data content from `php://input`. |
| **$__ltsdata** | `array` | Decoded JSON data from POST request. |
| **$__lts_classes** | `array` | Array of loaded framework classes for CSS/JS connection. |

## LTS Class

Abstract class with static factory methods for creating components and working with data.

### Data Processing Methods

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **inputrequest** | `string $reg`, `int $filter = FILTER_SANITIZE_SPECIAL_CHARS` | `mixed/false` | Gets value from `$_POST` or `$_GET` by name with filtering. Returns `false` if not found. |
| **isAssociativeArray** | `array $array` | `bool` | Checks if array is associative (keys are not sequential numbers 0..n). |
| **explodestr** | `mixed $name` | `array` | Splits string into array by delimiters (priority: `|`, `;`, `,`). If array or object passed — converts to array. |
| **log** | `string $filename`, `array $arr` | — | Writes array to file in JSON format. |
| **_new** | `string $type`, `string $id = ''`, `mixed $parent = null` | `Construct` | Factory method for creating objects. Creates instance of class `LTS\{$type}`, adds to parent if specified. |

### Space Configuration Methods

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **scriptversion** | `string $v` | — | Sets script version in `Space::$scriptversion` for cache control. |
| **meta** | `string $v` | — | Sets additional meta tags in `Space::$meta`. |
| **keywords** | `string $v` | — | Sets keywords in `Space::$keywords`. |
| **description** | `string $v` | — | Sets page description in `Space::$description`. |

### Component Factory Methods

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **Accordion** | `string $id = ''`, `mixed $parent = null` | `Accordion` | Creates accordion object. |
| **Button** | `string $id = ''`, `mixed $parent = null` | `Button` | Creates button object. |
| **Cells** | `string $id = ''`, `mixed $parent = null` | `Cells` | Creates table grid object. |
| **Columns** | `string $id = ''`, `mixed $parent = null` | `Columns` | Creates column layout object. |
| **Construct** | `string $id = ''`, `mixed $parent = null` | `Construct` | Creates base object. |
| **CSS** | `string $id = ''`, `mixed $parent = null` | `CSS` | Creates styles object. |
| **DataTable** | `string $id = ''`, `mixed $parent = null` | `DataTable` | Creates data table object. |
| **DataSync** | `string $id = ''`, `mixed $parent = null` | `DataSync` | Creates data synchronization object. |
| **DataView** | `string $id = ''`, `mixed $parent = null` | `DataView` | Creates data view object. |
| **Dialog** | `string $id = ''`, `mixed $parent = null` | `Dialog` | Creates dialog object. |
| **Div** | `string $id = ''`, `mixed $parent = null` | `Div` | Creates container object. |
| **Element** | `string $id = ''`, `mixed $parent = null` | `Element` | Creates base UI element. |
| **Events** | `string $id = ''`, `mixed $parent = null` | `Events` | Creates events object. |
| **FilterForm** | `string $id = ''`, `mixed $parent = null` | `FilterForm` | Creates filter form object. |
| **Form** | `string $id = ''`, `mixed $parent = null` | `Form` | Creates form object. |
| **Grid** | `string $id = ''`, `mixed $parent = null` | `Grid` | Creates CSS Grid object. |
| **Html** | `string $id = ''`, `mixed $parent = null` | `Html` | Creates HTML element object. |
| **Input** | `string $id = ''`, `mixed $parent = null` | `Input` | Creates input field object. |
| **JS** | `string $id = ''`, `mixed $parent = null` | `JS` | Creates JavaScript object. |
| **LayerSlider** | `string $id = ''`, `mixed $parent = null` | `LayerSlider` | Creates layer switcher object. |
| **Lang** | `string $id = ''`, `mixed $parent = null` | `Lang` | Creates localization object. |
| **LookupField** | `string $id = ''`, `mixed $parent = null` | `LookupField` | Creates lookup field object. |
| **MySql** | `string $name`, `string $server`, `string $user`, `string $password` | `MySql` | Creates database connection. |
| **MySqlField** | `string $name`, `string $type`, `mixed $param = null` | `MySqlField` | Creates table field object. |
| **MySqlTable** | `string $name` | `MySqlTable` | Creates database table object. |
| **MultiTable** | `MySqlTable $header` | `MultiTable` | Creates document object with tabular parts. |
| **ProgressBar** | `string $id = ''`, `mixed $parent = null` | `ProgressBar` | Creates progress indicator object. |
| **SimpleChart** | `string $id = ''`, `mixed $parent = null` | `SimpleChart` | Creates chart object. |
| **Space** | `string $id = ''`, `mixed $parent = null` | `Space` | Creates space object for rendering. |
| **Span** | `string $id = ''`, `mixed $parent = null` | `Span` | Creates inline container object. |
| **Stock** | `MySqlTable $dbtable`, `array $fieldmap = []` | `Stock` | Creates operations management object. |
| **Tabs** | `string $id = ''`, `mixed $parent = null` | `Tabs` | Creates tabs object. |
| **Quark** | `string $id = ''`, `mixed $parent = null` | `Quark` | Creates base container object. |
| **QueryBuilder** | — | `QueryBuilder` | Creates SQL query builder. |
| **Vars** | `string $id = ''`, `mixed $parent = null` | `Vars` | Creates global variables object. |
| **Video** | `string $id = ''`, `mixed $parent = null` | `Video` | Creates video player object. |

### Usage Examples

```php
// Connecting LOTIS framework
define('UPPER_DIR', dirname(__DIR__) . '/newlotis/');
include_once UPPER_DIR . 'lotis.php';

// Creating application objects
$div = LTS::Div()->capt('Hello, world!');

// Building application
LTS::Space()->build($div);
```

## Notes
*   **Session:** Calls `session_start()` at beginning — required for `Vars` operation and state persistence.
*   **POST processing:** Processes `php://input` for JSON requests and `$_POST` for Events — synchronizes `__ltsVars` with session.
*   **Navigation:** If `__ltspageurl` present in POST data — returns JSON with URL and terminates execution for client-side redirect.
*   **Autoloader:** Registers `spl_autoload_register` with class search in current directory and `_LOTIS_DIR`.
*   **Logging:** Calls `LTS\Logger::init()` after constants definition — initializes logging system.
*   **Global array:** `$__lts_classes` populated during autoloading — used in `Space` for connecting component CSS/JS files.
*   **Factory methods:** All `LTS::Component()` methods use `_new()` — create object and add to parent if specified.
*   **Parent:** `$parent` parameter can be string (ID) or object — if object, calls `$parent->add($el)`.
*   **ID:** If `$id` not passed — auto-generated in class constructor via `Construct::$spaceid`.
*   **MySql:** `MySql()` method accepts 4 connection parameters — returns `LTS\MySql` object.
*   **Stock:** `Stock()` method accepts operations table and optional field mapping — returns `LTS\Stock` object.
*   **MultiTable:** `MultiTable()` method accepts header table — returns `LTS\MultiTable` object.
*   **QueryBuilder:** `QueryBuilder()` method accepts no parameters — creates empty query builder.
*   **Constants:** Defined via `define()` — available throughout application via `_LOTIS_*`.
*   **Paths:** `_LOTIS_DIR` computed via `dirname(__DIR__)` — assumes placement in `newlotis` subdirectory.
*   **Filtering:** `inputrequest()` uses `FILTER_SANITIZE_SPECIAL_CHARS` by default — can be overridden.
*   **explodestr:** Supports arrays and objects — converts them to array via `(array)` or `explodestr()`.
*   **Delimiters:** Delimiter priority in `explodestr()`: `|` → `;` → `,` — for format flexibility.
*   **log():** Writes JSON via `file_put_contents()` — for debugging and data logging.
*   **scriptversion:** Added to CSS/JS paths in `Space` — for browser cache invalidation.
*   **meta/keywords/description:** Set in static `Space` properties — used during `<head>` generation.
*   **Abstract class:** `LTS` declared as `abstract` — cannot be instantiated directly.
*   **Static methods:** All `LTS` methods are static — called via `LTS::method()`.
*   **Namespace:** Framework classes in `LTS\` namespace — autoloader adds prefix.
*   **File search:** Autoloader searches files in order: current directory, `_LOTIS_DIR`, with path prefix and without.
*   **Initialization:** File must be connected first at application entry point — before creating objects.
*   **Vars session:** `__ltsVars` from POST data saved to `$_SESSION` — for synchronization between requests.
*   **Termination:** When processing navigation, `exit()` called — stops script execution.
*   **JSON:** POST data decoded via `json_decode()` with `true` — returns associative arrays.
*   **Logger:** Initialized after constants definition — considers user logging settings.
*   **Classes:** `$__lts_classes` array used in `Space::__builddoc()` for connecting CSS/JS files.
*   **_new():** Checks parent type — if object, adds via `add()`, if string — uses as ID.
*   **Component types:** All factory methods return objects of corresponding framework classes.
*   **Flexibility:** Parent may be unspecified — object created without adding to hierarchy.
*   **Conventions:** Factory method names match class names — for API consistency.
*   **Error handling:** Methods return `false` or throw exceptions on failure — check return values in production.
*   **Security:** Input filtering via `inputrequest()` helps prevent XSS — always validate user input additionally.
*   **Performance:** Autoloader searches multiple paths — consider optimizing for production environments.
*   **Extensibility:** Custom components can be added by placing classes in framework directory or extending LTS.
*   **Thread safety:** Session handling requires proper configuration for concurrent requests.
*   **Encoding:** JSON operations assume UTF-8 encoding — ensure proper charset headers.
*   **Dependencies:** Framework components may have inter-dependencies — load order matters for some features.
*   **Testing:** Use `log()` method for debugging complex data flows during development.
*   **Deployment:** Set `_LOTIS_INTERNET` to `true` for production environments with external resources.
*   **Versioning:** Use `scriptversion()` to manage cache busting when updating framework files.
*   **Localization:** `Lang` component integrates with `Vars` for multilingual applications.
*   **Database:** `MySql` component supports connection pooling via persistent connections configuration.
*   **Events:** `Events` component enables AJAX communication between client and server components.
*   **Rendering:** `Space` component coordinates final HTML assembly from all registered components.
*   **Client-side:** Generated JavaScript integrates with LTS client library for dynamic behavior.
*   **Maintenance:** Regular updates to framework files recommended for security and feature improvements.

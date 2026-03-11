[English](DataSync.md) | [Русский](DataSync.ru.md)

🚀 [Quick Start](../QuickStart.md)

# DataSync Class

## Purpose
Data synchronization class of the LOTIS framework, inheriting from `Quark`. Designed for organizing data synchronization between client and server in real-time. Establishes a link between the database table `dbtable` and its representation in the user's `datatable`. Supports automatic loading of records via `IntersectionObserver`, tracking changes to records between clients, locking records during editing, and fixing changes in a special manager table. Integrates with `DataTable` for data display, `Events` and `Vars` for client-server communication.

### Usage Modes

* **Real-time data loading** - allows adding data from `dbtable` to `datatable` in portions when scrolling the table inside `wrapper`.
* **Multi-user mode** - organizes real-time updates to the user's `datatable` if changes are made to the `dbtable` database table by someone else. Allows blocking a record from `dbtable` during editing and notifying other application users about the changes made.

To organize multi-user mode operation, it is required to create a manager table in the database that fixes record changes. This is a one-time operation performed when calling the `initmanager` method of the `DataSync` object with the second parameter set to true.

## Properties

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$datatable** | `DataTable/null` | `public` | Reference to the DataTable object for displaying data. Set via `datatable()`. |
| **$dbtable** | `MySqlTable/null` | `public` | Reference to the database table. Set via `dbtable()`. Used in queries via `QueryBuilder`. |
| **$autochange** | `bool` | `public` | Flag allowing change tracking. Default `false`. Controlled via `autochange()`. |
| **$autoupload** | `bool` | `public` | Flag allowing auto-loading. Default `false`. Controlled via `autoupload()`. |
| **$limit** | `int` | `public` | Number of records to load simultaneously. Default `100`. Used during auto-loading via `upload()`. |
| **$conditions** | `array` | `public` | Conditions set on the server. Passed to `QueryBuilder` via `applycondition()`. Set via `condition()`. |
| **$usersconditions** | `mixed/null` | `public` | User conditions for filtering and sorting. Assigned on the client and saved in the LTS object. Passed automatically when calling `upload()`. |
| **$system** | `mixed/null` | `public` | Conditions of internal functions, particularly for `LIMIT`. Controlled automatically during auto-loading. |
| **$datarequest** | `callable` | `public` | Data loading function. Default — query to `$dbtable` with conditions. Overridden via `datarequest()`. |
| **$changeregime** | `int` | `public` | Mode for updating changed records: `1` — automatic, `0` — do not update. Required for manual processing of received changes on the client. Received changed records can be obtained in `LTS(datasync).changed`, and deleted records — in `LTS(datasync).deleted`. The composition of record fields is determined by the conditions set on the server `conditions`, on the client `usersconditions`, and the `datarequest` method reading records from the database table. |
| **$ltstablesmanager** | `MySqlTable/null` | `public` | Change manager table in the database. Created via `initmanager()`. Stores information about changed records. |
| **$events** | `Events` | `public` | Events object for server-client communication. Created in the constructor with ID `"{$this->id}_Events"`. |
| **$vars** | `Vars` | `public` | Global variables object for storing state. Created with ID `"ltsSync_{$this->id}"`. Stores `begin`, `next`, `term`, `client`, `recid`. |
| **$timer** | `int` | `public` | Polling interval for changes on the server in milliseconds. Default `5000`. Used in `setInterval()` on the client. |
| **$wrapper** | `mixed/null` | `public` | Element relative to which the appearance of the last row is tracked via `IntersectionObserver`. Default `$this->dropdown`. |
| **$recidname** | `string` | `public` | Name of the field storing the record ID in DataTable. Default `'id'`. |

## Methods (Server Side, PHP)

### Constructor

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **__construct** | `string $id = ''` | — | Class constructor. Calls parent `Quark` constructor, sets `$type` to `'DataSync'`. Creates `$events`, `$vars` objects. Initializes `$datarequest` with the function for loading from `$dbtable`. |

### Initialization

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **initmanager** | `MySql $base`, `bool $create = false` | `$this` | Initializes the change manager table `ltstablesmanager`. Creates fields: `name` (table name), `recid` (record ID), `client` (author of changes), `created` (date), `type` (operation type: 0=deleted, 1=changed). If `$create === true` — creates the change manager table in the DB. |
| **dbtable** | `MySqlTable $dbtable` | `$this` | Binds the database table. Saves in `$dbtable`. Used in the default search function. Supports fluent interface. |
| **datatable** | `DataTable $datatable` | `$this` | Binds the DataTable object. Saves in `$datatable`. Used for displaying data on the client. Supports fluent interface. |
| **wrapper** | `mixed $el` | `$this` | Sets the element for tracking via `IntersectionObserver`. Can be a string (ID), an object with `$id`, or a DOM element. Saves in `$wrapper`. Supports fluent interface. |
| **datarequest** | `callable $f` | `$this` | Overrides the data loading function. The function should accept `$ids` (array of IDs) and return an array of records. Binds context via `bindTo($this)`. |

### Synchronization Management

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **autochange** | `bool $reg = true` | `$this` | Enables or disables change tracking. Sets `$autochange`. Supports fluent interface. |
| **autoupload** | `bool $reg = true` | `$this` | Enables or disables auto-loading of data. Sets `$autoupload`. Supports fluent interface. |
| **condition** | `mixed $key`, `mixed $value = null` | `$this` | Adds a condition for selecting records in the database table. If `$value === null` and `$key` is an array — adds all key-value pairs. Otherwise — adds one pair. Saves in `$conditions`. Supports fluent interface. |
| **begchange** | — | `$this` | Sets the maximum ID in the manager table at the start of operation. Queries the last `id` from `$ltstablesmanager` and saves in `$vars->recid`. Used for tracking new changes. |

### Auto-loading

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **reset** | — | `$this` | Resets auto-loading counters. Sets `$vars->begin = 0`, `$vars->next = 1`. Calls `$vars->store()` to save to the session. Supports fluent interface. |
| **upload** | — | `array` | Loads the next batch of records during auto-loading. Gets `begin`, `next` from `$vars`. Sets `LIMIT` via `$this->system`. Calls `$this->datarequest`. Updates `$vars->begin` and `$vars->next`. Calls `$vars->store()`. Returns an array of records or an empty array. The method is used in the server part of the event responsible for auto-loading data. |

### Multi-user Mode

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **fixchange** | `array $arr`, `int $type = 1` | `$this` | Fixes record changes in the manager table. `$arr` — array of record IDs, `$type` — operation type (1=changed/added, 0=deleted). For each ID, creates a record in `$ltstablesmanager` with the current client and time. |
| **setlock** | `mixed $recid` | `$this` | Sets a lock on a record (type=2 in the manager). Checks existing locks, creates or updates a record in `$ltstablesmanager`. Supports an array of IDs for multiple locking. |
| **removelock** | `mixed $recid` | `$this` | Removes the lock from a record. Deletes the record from `$ltstablesmanager` with `type=2` and matching `recid`. Supports an array of IDs. |
| **checklock** | `mixed $recid` | `bool` | Checks if a record is locked. Queries a record from `$ltstablesmanager` with `type=2` and matching `recid`. Returns `true` if not locked, `false` if locked. |
| **getchange** | — | `array` | Gets an array of changed records from other clients. Queries records from `$ltstablesmanager` with `client != current` and `id > last_recid`. Separates into `$changed` (type 1) and `$deleted` (type 0). Returns `['result' => bool, 'changed' => array, 'deleted' => array]`. The method is used in the server part of the event responsible for organizing multi-user mode. |

### Helper Methods

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **applycondition** | `QueryBuilder $query`, `array $cond` | `$this` | Applies conditions to the `QueryBuilder` object. Supports keys: `FIELDS`, `WHERE`, `ORDER`, `LIMIT`, `GROUP`. Ignores empty values. Called inside `datarequest()` and `upload()`. |
| **checktime** | — | `$this` | Cleans the manager table from outdated records older than 120 seconds. Called automatically in `getchange()`. Saves the next check time in `$vars->checktime`. |
| **compile** | — | — | Generation of client-side JavaScript methods. If `$autoupload == true` — creates the `upload` event, registers `IntersectionObserver`, generates methods `observe()`, `unobserve()`, `reset()`, `upload()`. If `$autochange == true` — creates events `setlock`, `removelock`, `checklock`, `getchange`, `fixchange`, registers `setInterval` for polling changes every `$timer` ms. Adds `$vars` to children. Calls `parent::compile()`. |

## LTS Object Methods (Client Side)

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **LTS(datasync).observe(row)** | `jQuery/Element/string row` | `this` | Starts tracking an element via `IntersectionObserver`. If `row` is jQuery — extracts the DOM element. If string — finds by ID. If element — uses directly. Adds the element to the observer. Saves in `this.observerow`. |
| **LTS(datasync).unobserve(row)** | `jQuery/Element/string row` | `this` | Stops tracking an element. If `row` is not passed — removes `this.observerow`. If passed — finds the element and calls `unobserve()`. Resets `this.observerow` if the element matches. |
| **LTS(datasync).upload(usersconditions, reset)** | `object usersconditions`, `bool reset` | `this` | Starts loading the next batch of data. Calls the server event `upload` with parameters. If `reset === true` — resets counters before loading. |
| **LTS(datasync).reset(usersconditions)** | `object usersconditions` | `this` | Resets the table, changes local conditions, and loads the first pool of data. Sets `autochange = false`, calls `DataTable.clear()`, sets `usersconditions`, calls `upload(usersconditions, true)`. |
| **LTS(datasync).condition(usersconditions)** | `object usersconditions` | `this` | Sets local selection and sorting conditions for auto-loading. Saves in `this.usersconditions`. |
| **LTS(datasync).fixchange(changed, deleted)** | `array changed`, `array deleted` | `this` | Transfers data about IDs of changed and deleted records to the server. Calls the server event `fixchange` with ID arrays. |
| **LTS(datasync).setlock(recid)** | `mixed recid` | `this` | Sets a lock on a record. Checks the local cache `__locks`, adds recid to the Set, calls the server event `setlock`. |
| **LTS(datasync).removelock(recid)** | `mixed recid` | `this` | Removes the lock from a record. Deletes recid from the local cache `__locks`, calls the server event `removelock`. |
| **LTS(datasync).checklock(recid)** | `mixed recid` | `bool` | Checks if a record is locked. Calls the server event `checklock`, returns the result from `LTS.get(events).__checklock`. |

## Events (Server Side)

| Event | Parameters | Returns | Description |
|---------|-----------|------------|----------|
| **upload** | `array $args` with keys `'usersconditions'`, `'reset'` | `array ['result' => bool, 'data' => array]` | Server handler for auto-loading. If `$reset` — calls `reset()`. If `$usersconditions` — saves in `$this->usersconditions`. Calls `upload()`, returns data. |
| **setlock** | `array $args` with key `'recid'` | `bool` | Server handler for record locking. Calls `$this->setlock($args['recid'])`. Returns `true`. |
| **removelock** | `array $args` with key `'recid'` | `bool` | Server handler for removing the lock. Calls `$this->removelock($args['recid'])`. Returns `true`. |
| **checklock** | `array $args` with key `'recid'` | `bool` | Server handler for checking the lock. Calls `$this->checklock($args['recid'])`. Returns the check result. |
| **getchange** | `array $args` | `array ['result' => bool, 'changed' => array, 'deleted' => array]` | Server handler for getting changes. Calls `$this->getchange()`. Returns arrays of changed and deleted records. |
| **fixchange** | `array $args` with keys `'changed'`, `'deleted'` | `bool` | Server handler for fixing changes. Calls `$this->fixchange($changed, 1)` and `$this->fixchange($deleted, 0)`. Returns `true`. |

## Events (Client Side)

| Event | Parameters | Description |
|---------|-----------|----------|
| **upload** | `array result` | Client handler for auto-loading response. If `result` is not empty — calls `DataTable.append(result)`. If there are `$limit` records — continues observing the last row. |
| **getchange** | `array result` | Client handler for getting changes response. If `$changeregime === 1` — automatically updates rows in DataTable via `values()` and deletes via `clear()`. If `$changeregime === 0` — saves in `LTS(datasync).changed` and `LTS(datasync).deleted`. Sends signal `{id}_Change`. |
| **{id}_Change** | — | Signal sent when changes are received. Can be subscribed to via `LTS.onSignal('{id}_Change', handler)`. |
| **{id}_Upload** | — | Signal sent when auto-loading is completed. Can be subscribed to via `LTS.onSignal('{id}_Upload', handler)`. |

## Usage Examples

```php
// Creating DataSync
$datasync = LTS::DataSync('OrdersSync', $maindiv)
    ->initmanager($mybase)
    ->dbtable($orders)
    ->datatable($orderstable)
    ->wrapper($content);

// Enabling multi-user mode
$datasync->autochange();
// Enabling data loading
$datasync->autoupload();

// Setting conditions on the server
$datasync->condition([
    'WHERE' => ['active' => 1, 'user.blocked' => 0],
    'ORDER' => '-date'
]);
```

### Example of manual change processing on the client 

```php
$datasync->changeregime = 0;

$datatable->signal('Change', 
<<<JS
function () {
    let changedrows = LTS(datasync).changed;    // [{...}, {...}, ...] - rows for DataTable
    let deletedids = LTS(datasync).deleted;     // [id1, id2, ...] - ids of deleted records
    ...
    LTS(datatable).filter([...]);      // Applying filter to newly added rows
    LTS(datatable).sort(sortname, sortnapr);    // Sorting
}
JS
, $datasync);
```

## Notes
*   Class extends `Quark`, therefore inherits child management methods (`add()`, `get()`, `set()`, `del()`).
*   Class extends `Construct`, therefore supports lifecycle hooks (`$check`, `$before`, `$on`).
*   **Auto-loading:** When `$autoupload == true`, `IntersectionObserver` is used to track the appearance of the last row of DataTable. When intersecting — the `upload()` event is called to load the next batch.
*   **Change tracking:** When `$autochange == true` — every `$timer` ms (default 5000), `getchange()` is called to get changes from other clients.
*   **Manager table:** The `ltstablesmanager` table stores information about changes: table name, record ID, author client, time, operation type. Cleared of records older than 120 seconds.
*   **Locks:** Records are locked via `setlock()` with `type=2` in the manager. Prevents simultaneous editing of one record by multiple clients.
*   **Client ID:** A unique client ID is generated upon creation via `bin2hex(random_bytes(16))` and saved in `$vars->client`.
*   **Update modes:** `$changeregime = 1` — automatic DataTable update when changes are received. `$changeregime = 0` — changes are placed in `LTS(datasync).changed` and `LTS(datasync).deleted` for manual processing.
*   **Signals:** Signals `{id}_Change` and `{id}_Upload` are sent when changes are received and auto-loading is completed, respectively.
*   **State variables:** The `$vars` object with ID `"ltsSync_{$this->id}"` stores `begin` (offset), `next` (continuation flag), `term` (search query), `client` (client ID), `recid` (last change ID), `checktime` (next cleanup time).
*   **IntersectionObserver:** Created during compilation if `$autoupload == true`. Observes `$wrapper` or the last row of the table. When intersecting — calls `upload()`.
*   **Loading function:** `$datarequest` by default queries data from `$dbtable` with conditions. Can be overridden via `datarequest()` for custom logic (external APIs, cache, etc.).
*   **Conditions:** Via `condition()`, you can add permanent conditions — filtering by status, user, date. Applied to all queries.
*   **Reset:** The `reset()` method resets auto-loading counters — called on new search, on page load.
*   **Cleanup:** The `checktime()` method is automatically called in `getchange()` — deletes old records from the change manager.
*   **Cache locks:** On the client, locks are cached in `LTS.get(events).__locks` (Set) — to prevent repeated lock requests.
*   **Server synchronization:** During `getchange()`, changes from other clients are queried with `client != current` and `id > last_recid` — only new changes.
*   **Change fixing:** The `fixchange()` method should be called after successful record saving on the server — to notify other clients.
*   **Lock checking:** The `checklock()` method returns `true` if the record is not locked — convenient for validation before editing.
*   **Multiple locks:** The `setlock()` and `removelock()` methods support an array of IDs — for locking multiple records simultaneously.
*   **Client methods:** Methods `observe()`, `unobserve()`, `reset()`, `upload()`, `condition()`, `fixchange()`, `setlock()`, `removelock()`, `checklock()` are generated via `compilemethod()` — available via `LTS.get('{id}')` or LTS(var), where var is a global PHP variable referencing the DataSync object.
*   **Polling timer:** The `$timer` interval is 5000 ms by default — can be changed before compilation for more or less frequent polling.
*   **ID field name:** The `$recidname` property defaults to `'id'` — can be changed if the ID in DataTable is stored in another field (e.g., `'code'`).
*   **Wrapper element:** Via `wrapper()`, you can specify a container for tracking — useful for custom layouts.
*   **Observer options:** When `$wrapper` is specified — it is passed to `IntersectionObserver` as the `root` option.
*   **Storage:** `$vars->store()` is called after each `upload()` — state is saved to the session.
*   **Restoration:** On page load — `$vars->restore()` — restores `begin`, `next`, `recid` from the session.

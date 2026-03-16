[English](LookupField.md) | [Русский](LookupField.ru.md)

🚀 [Quick Start](../QuickStart.md)

# LookupField Class

## Purpose
A lookup field class with autocomplete functionality from the LOTIS framework, inheriting from `Div`. Designed for creating interactive input fields with a dropdown list of search results, supporting data auto-loading on scroll, database table binding, and state synchronization between server and client. The class combines several components: `Input` (input field), `Div` (dropdown block), `DataTable` (results table), `Events` (server-client communication), and `Vars` (state storage). Supports search query customization, field mapping, result filtering, and integration with the signal system for value selection notifications.

### Key Features

* **Database search** with autocomplete
* **Dropdown menu** with results
* **Automatic data loading**
* **Search state preservation**
* **Custom data loading**
* **DataTable support**

## Properties

### Integrated Objects

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$dbtable** | `MySqlTable/null` | `public` | Reference to the database table for searching. Set via `dbtable()`. Used in search queries through `QueryBuilder`. |
| **$events** | `Events` | `public` | Event object for server-client communication. Created in the constructor. Used for handling search and auto-loading. |
| **$table** | `DataTable` | `public` | Results table object. Created in the constructor with ID `"{$this->id}_results"`. Displays found records. |
| **$input** | `Input` | `public` | Input field object. Created in the constructor with ID `"{$this->id}_input"`. Added to the component's children. |
| **$vars** | `Vars` | `public` | Global variables object for state storage. Created with ID `"ltsSync_{$this->id}"`. Stores `begin`, `next`, `term`. |
| **$wrapper** | `mixed/null` | `public` | Element for tracking the appearance of the last row via `IntersectionObserver`. By default `$this->dropdown`. Set via `wrapper()`. |
| **$dropdown** | `Div` | `public` | Dropdown block object. Created in the constructor with ID `"{$this->id}_dropdown"`. Contains the results table. Hidden by default via CSS. |

### Flags

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$dropdownenable** | `bool` | `public` | Flag for using the dropdown block. Default `true`. If `false` — the table is displayed without dropdown wrapper. |
| **$binddatatable** | `bool` | `public` | Flag for binding `DataTable` to `Div`. Default `true`. Controls adding the table to children. |
| **$autoupload** | `bool` | `public` | Flag allowing automatic data loading. Default `true`. If `true` — uses `IntersectionObserver` for loading on scroll. |

### Parameters for Database Query Formation

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$searchfield** | `string` | `public` | Database field name for searching. Default `'name'`. May include table name (`table.field`). Set via `searchfield()`. |
| **$tablefield** | `string` | `public` | Field alias for display in the results table. Default `'name'`. Set automatically when calling `searchfield()` with an alias. |
| **$conditions** | `array` | `public` | Additional conditions for the search query. Passed to `QueryBuilder` via `applycondition()`. Set via `condition()`. |
| **$datarequest** | `callable/null` | `public` | Custom data loading function. By default — search by `$dbtable` with `$searchfield`. Overridden via `datarequest()`. |
| **$fieldmap** | `array/null` | `public` | Mapping of `DataTable` fields with `values` method fields. Format: `['datatable_field' => 'values_field']`. Set via `fieldmap()`. |
| **$system** | `array/null` | `public` | Internal conditions for queries (LIMIT, OFFSET, etc.). Managed automatically during auto-loading. |
| **$limit** | `int` | `public` | Number of records to load at once. Default `100`. Used during auto-loading via `upload()`. |

## Methods (Server-side, PHP)

### Constructor

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **__construct** | `string $id = ''` | — | Class constructor. Calls parent `Div` constructor, adds class `'lookup-field'`. Creates objects `$input`, `$dropdown`, `$table`, `$events`, `$vars`. Sets default search function via `datarequest()`. Adds `$events` and `$input` to children. |

### Database Connection Setup Methods

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **dbtable** | `MySqlTable $dbtable` | `$this` | Sets the database table for searching. Saves to `$dbtable`. Used in the default search function. Supports fluent interface. |
| **searchfield** | `string $field` | `$this` | Sets the search field. Supports aliases via `AS` (`table.field as alias`). If there is a dot and alias — splits into `$searchfield` and `$tablefield`. Otherwise — sets both to the same value. Supports fluent interface. |
| **condition** | `mixed $key`, `mixed $value = null` | `$this` | Adds search condition. If `$value === null` and `$key` is an array — adds all key-value pairs. Otherwise — adds one pair. Saves to `$conditions`. Supports fluent interface. |
| **datarequest** | `callable $f` | `$this` | Sets custom data loading function. The function should accept `$term` (search string) and return an array of records. Binds context via `bindTo($this)`. Overrides the default search function. |
| **head** | `array $arr` | `$this` | Sets results table column headers. Calls `$this->table->head($arr)`. Supports fluent interface. |
| **fieldmap** | `array $prot` | `$this` | Sets field mapping. Saves to `$fieldmap`. Used on the client for mapping data between `DataTable` and form. Supports fluent interface. |

### Data Manipulation Methods

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **search** | `string $term` | `array` | Performs search by term. Calls `$this->datarequest` via `call_user_func()`. Returns array of search results. |
| **reset** | — | `$this` | Resets auto-loading counters. Sets `$vars->begin = 0`, `$vars->next = 1`. Calls `$vars->store()` to save to session. Supports fluent interface. |
| **upload** | — | `array` | Loads the next batch of records during auto-loading. Gets `begin`, `next`, `term` from `$vars`. Sets `LIMIT` via `$this->system`. Calls `$this->datarequest`. Updates `$vars->begin` and `$vars->next`. Calls `$vars->store()`. Returns array of records or empty array. |

### Change Tracking Setup

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **wrapper** | `mixed $el` | `$this` | Sets the element for tracking via `IntersectionObserver`. Can be a string (ID), object with `$id`, or DOM element. Saves to `$wrapper`. Supports fluent interface. |

### Additional Methods

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **label** | `string $caption = null` | `Html/bool` | Manages the associated label element. If `$caption` is passed — creates new `Html('label')` with `for` attribute equal to `$input->id`. If exists — updates `$caption`. If doesn't exist and `$caption === null` — returns `false`. |
| **applycondition** | `QueryBuilder $query`, `array $cond` | `$this` | Applies conditions to the `QueryBuilder` object. Supports keys: `FIELDS`, `WHERE`, `ORDER`, `LIMIT`, `GROUP`. Ignores empty values. Called inside `datarequest()` and `upload()`. |

### Lifecycle

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **compile** | — | — | Generates client-side JavaScript methods. Creates `search` and `upload` events on server and client. Generates methods: `observe()`, `unobserve()`, `reset()`, `select()`, `clear()`. Adds jQuery event handlers for `focus`, `input`, `click`. If `$autoupload == true` — adds `IntersectionObserver` for auto-loading. Adds `$vars` to children. Calls `parent::compile()`. |

## Client Methods Available via LTS Object

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **LTS(lookup).search(term)** | `string term` | — | Calls server event `search` with search term. Gets results via `Events`, passes to `DataTable.create()`. Shows/hides dropdown depending on results. |
| **LTS(lookup).value(term)** | `string term` | `mixed` | Gets or sets the input field value. Calls `LTS(input).value(term)`. Used for programmatic value changes. |
| **LTS(lookup).observe(row)** | `jQuery/Element/string row` | `this` | Starts tracking an element via `IntersectionObserver`. If `row` is jQuery — extracts DOM element. If string — finds by ID. If element — uses directly. Adds element to observer. Saves to `this.observerow`. |
| **LTS(lookup).unobserve(row)** | `jQuery/Element/string row` | `this` | Stops tracking an element. If `row` not passed — removes `this.observerow`. If passed — finds element and calls `unobserve()`. Resets `this.observerow` if element matches. |
| **LTS(lookup).reset()** | — | `this` | Resets auto-loading variables. Calls `LTS.vars('ltsSync_{id}').set('begin', 0).set('next', 1)`. Calls `this.unobserve()`. Returns `this` for chaining. |
| **LTS(lookup).select(row)** | `object row` | — | Saves the selected row. Sets `this.selected = row`. If `row === null` — clears selection. Used when clicking a table row. |
| **LTS(lookup).clear()** | — | — | Clears the field and table. Sets `this.selected = null`. Clears input value via `jQuery('#{inputId}').val('')`. Calls `DataTable.clear()`. Calls `this.reset()`. Hides dropdown. |
| **LTS(lookup).upload()** | — | — | Calls server event `upload` for auto-loading. Gets results via `Events`, passes to `DataTable.append()`. If fewer results than `$limit` — stops observation. |

## Events and Signals

| Event | Parameters | Description |
|---------|-----------|----------|
| **search(term)** | `string term`` | Search by word. If `$autoupload == true` — saves `term` to `$vars`, calls `upload()`. Otherwise — calls `search($term)`. Returns results in format `['result' => true, 'data' => [...]]`. On client, if `result.result == true` and `result.data.length > 0` — calls `DataTable.create(result.data)`, shows dropdown. Otherwise — clears table, hides dropdown. |
| **upload** | `array result` | Auto-loading. On server calls `upload()`, returns array of records. If no records — returns `false`. Used only when `$autoupload == true`. Client response handler for auto-loading. On client, if `result` is not empty — calls `DataTable.append(result)`. If `$limit` records — continues observation of the last row. |
| **{id}_Selected** | — | Signal sent when selecting a table row. |

## Special CSS Classes

| Class | Description |
|-------|----------|
| **`.lookup-field`** | Base component class — added automatically in constructor. |
| **`.lookup-dropdown`** | Dropdown block class — added by `$dropdown`. Default `display: none`. |
| **`.filterfield`** | Filter field class — added by `$input` if component has class `filterfield`. |

### Usage Examples

```php
// Database connection
$base = LTS::MySql('tracking', 'localhost', 'root', 'root');
$works = $base->table('works');
$works->string('name', 100);
$works->float('price');

// Creating lookup field
$workLookup = LTS::LookupField();

// Binding to database table, defining columns in search table  
$workLookup->dbtable($works)->head(['name' => 'Work']);

// Handling value selection via signal
$page = LTS::Div();
$page->signal('Selected', 
<<<JS
    function() {
        const selected = LTS(workLookup).selected;
        if (selected) 
            alert('Selected: ' + selected.name + ' — ' + selected.price + ' ₽');
    }
JS
, $workLookup);
```

## Notes
*   The class extends `Div`, therefore inherits all Flexbox methods (`flex()`, `row()`, `column()`, `content()`, `align()`, `gap()`, etc.), children management methods (`add()`, `del()`, `getchilds()`, `move()`), and styling methods (`css()`, `width()`, `height()`).
*   The class extends `Element`, `Quark`, `Construct`, therefore supports the full object lifecycle: `compile()`, `shine()`, `childs()`, `addinspace()`, as well as hooks (`$check`, `$before`, `$on`, etc.).
*   **Auto-loading:** When `$autoupload == true`, uses `IntersectionObserver` to track the appearance of the last table row. When intersecting — calls `upload()` event to load the next batch.
*   **State variables:** The `$vars` object with ID `"ltsSync_{$this->id}"` stores `begin` (offset), `next` (continuation flag), `term` (current search query). Saved to session via `$vars->store()`.
*   **Default search function:** If not overridden via `datarequest()` — performs query to `$dbtable` with `LIKE '%term%'` condition by `$searchfield`. Applies `$conditions` and `$system`.
*   **Field mapping:** The `$fieldmap` property is used on the client for mapping `DataTable` field names with form fields. Set via `fieldmap()`, passed to JS via `compile()`.
*   **Dropdown block:** When `$dropdownenable == true` — results table is wrapped in `$dropdown` with class `'lookup-dropdown'`. When `false` — table is added directly.
*   **Event handlers:** During compilation, jQuery event handlers are added for `focus`, `input`, `click` on `$input`, as well as global `click` on `document` to hide dropdown when clicking outside the component.
*   **Selection signal:** When clicking a table row, signal `{id}_Selected` is sent — can be subscribed via `LTS.onSignal()` to react to value selection.
*   **State reset:** The `reset()` method resets auto-loading counters — called in `clear()`, during new search, on page load.
*   **Custom search:** Via `datarequest()` you can override search logic — for example, use external API, complex queries with JOIN, result caching.
*   **Search conditions:** Via `condition()` you can add permanent conditions — for example, filtering by status, user, date. Applied to all queries.
*   **Search field:** The `searchfield()` method supports aliases — `table.field as alias` is split into `$searchfield` (for query) and `$tablefield` (for display).
*   **Label:** The `label()` method creates an associated label element with `for` attribute equal to `$input->id` — ensures accessibility.
*   **Auto-loading:** When `$autoupload == true`, after loading `$limit` records — observation of the last row continues. If fewer than `$limit` records — observation stops.
*   **Wrapper:** Via `wrapper()` you can specify the element for tracking — by default `$this->dropdown`. Can be ID, object with `$id`, or DOM element.
*   **Clearing:** The `clear()` method on the client clears the input field, table, state variables, hides dropdown — complete component reset.
*   **Row selection:** When clicking a table row — value of `$tablefield` is written to `$input`, signal `{id}_Selected` is sent, dropdown is hidden.
*   **Search on input:** When typing in `$input` — `search(term)` is called if length > 2 (when `$autoupload == false`) or immediately (when `$autoupload == true`).
*   **First focus:** On first focus on `$input` — search with current value is called. `firstloaded` flag prevents repeated search.
*   **Click outside component:** When clicking outside `$this->id` — dropdown is hidden, selected row value is restored or field is cleared.
*   **Events Events:** Server events `search` and `upload` are registered via `$events->server()`, client events — via `$events->client()`. Response handlers are set via `LTS.request()`.
*   **IntersectionObserver:** Created during compilation if `$autoupload == true`. Observes `$wrapper` or the last table row. When intersecting — calls `upload()`.
*   **Context passing:** The `$datarequest` function is bound to context via `bindTo($this)` — inside the function `$this` is accessible for referencing component properties.
*   **LIMIT:** The `$limit` parameter defaults to `100` — determines the number of records per load. Can be changed before compilation.
*   **System conditions:** The `$system` property is used for temporary conditions (LIMIT, OFFSET) — reset after each query via `$this->system = null`.
*   **State storage:** `$vars` saves state between requests — `begin`, `next`, `term` are available after page reload within the session.
*   **Client mapping:** `$fieldmap` is passed to JS via `compile()` — used for transforming field names when setting values in the form.
*   **Results table:** `$table` — `DataTable` object with ID `"{$this->id}_results"`. Supports all `DataTable` methods: `create()`, `append()`, `values()`, `clear()`, `sort()`.
*   **Input field:** `$input` — `Input` object with ID `"{$this->id}_input"`. Has attribute `autocomplete='off'` to disable browser autocomplete.
*   **Dropdown block:** `$dropdown` — `Div` object with ID `"{$this->id}_dropdown"`. Hidden by default via `css()->add('display', 'none')`. Shown when search results appear.
*   **jQuery Events:** Handlers are registered via `$(document).on()` — work with dynamically created elements.
*   **Signals:** Signal `{id}_Selected` is sent when selecting a row — can be used to update related components.
*   **Errors:** On search error — shows `alert(result.error)`, hides dropdown. Handled via client `search()` handler.
*   **Empty results:** If search yields no results — table is cleared, dropdown is hidden. No error message is shown.
*   **First run:** `firstloaded` flag prevents repeated search on focus — set to `true` after first search.
*   **Value restoration:** When clicking outside the component — if `selectedrow` exists — value is restored to `$input`. Otherwise — field is cleared.
*   **Observation:** The `observe()` method accepts jQuery, string (ID), or DOM element — universal interface for different scenarios.
*   **Stop observation:** The `unobserve()` method without parameters — stops observing `this.observerow`. With parameter — stops observing the specified element.
*   **Variable reset:** The `reset()` method on the client calls `LTS.vars().set()` — variables are saved to localStorage and session.
*   **Data loading:** The `upload()` method on the server — incremental loading with `begin` and `limit`. Updates `$vars->begin` for the next batch.
*   **Continuation flag:** The `$vars->next` variable — `1` if more records exist, `0` if everything is loaded. Controls continuation of observation.
*   **Search term:** The `$vars->term` variable — stores the current search query. Used during auto-loading for result consistency.
*   **Customization:** Via `datarequest()` you can implement any data source — DB, API, file, cache. Return array of associative arrays.
*   **Conditions:** Via `condition()` you can add filtering — for example, only active records, only for current user.
*   **Sorting:** Via `condition(['ORDER' => 'name'])` you can set result sorting. Applied to all queries.
*   **Limits:** Via `condition(['LIMIT' => '0, 50'])` you can override `$limit` for specific queries.
*   **Grouping:** Via `condition(['GROUP' => 'category'])` you can group results. Uses `QueryBuilder::groupBy()`.
*   **Fields:** Via `condition(['FIELDS' => 'id, name, email'])` you can select specific fields. Uses `QueryBuilder::select()`.
*   **WHERE:** Via `condition(['WHERE' => 'status = 1'])` you can add raw condition. Uses `QueryBuilder::where()`.
*   **Mapping:** `$fieldmap` — associative array `['datatable_field' => 'form_field']`. Used when passing data to the form.
*   **Wrapper element:** Can be a string (ID), object with `$id`, or DOM element — universal interface.
*   **Auto-loading:** When `$autoupload == false` — all results loaded at once. When `true` — incremental with `IntersectionObserver`.
*   **Events:** Events `search` and `upload` are registered automatically — no explicit call required.
*   **Client methods:** Methods `observe()`, `unobserve()`, `reset()`, `select()`, `clear()` are generated via `compilemethod()` — available via `LTS.get('{id}')`.
*   **Input attributes:** `$input` has attribute `lookupfield` equal to `$this->id` — used for linking with the component.
*   **Dropdown CSS:** `$dropdown` has `display: none` by default — shown when search results appear via jQuery `.show()`.
*   **Table ID:** `$table` has ID `"{$this->id}_results"` — used in jQuery selectors and client methods.
*   **Events ID:** `$events` has automatic ID — used for registering handlers via `LTS.request()`.
*   **Vars ID:** `$vars` has ID `"ltsSync_{$this->id}"` — unique name for storing state in the session.
*   **Search on focus:** On first focus on `$input` — search with current value is called. `firstloaded` flag prevents repetition.
*   **Search on input:** On each character input — `search(term)` is called. When `$autoupload == false` — only if `term.length > 2`.
*   **Hide dropdown:** When clicking outside the component — dropdown is hidden. Value of `$input` is restored from `selectedrow` or cleared.
*   **Row selection:** When clicking a row — `select(row)`, value to `$input`, signal `{id}_Selected`, hide dropdown.
*   **Clearing:** The `clear()` method — resets all states, clears `$input`, `$table`, `$vars`, hides dropdown.
*   **Observation:** `IntersectionObserver` is created once during compilation — reused for all data batches.
*   **Last row:** Observation of the last row — when intersecting — loads next batch via `upload()`.
*   **Next flag:** If `result.data.length < $limit` — `$vars->next = 0` — stops observation. Otherwise — `$vars->next = 1`.
*   **Begin offset:** On each load — `$vars->begin += $limit` — for the next data batch.
*   **Term:** Saved to `$vars->term` — used during auto-loading for search result consistency.
*   **Storage:** `$vars->store()` is called after each `upload()` — state is saved to session.
*   **Restoration:** On page load — `$vars->restore()` — restores `begin`, `next`, `term` from session.
*   **Reset:** The `reset()` method — resets `begin = 0`, `next = 1` — for new search.
*   **Client reset:** On client — `LTS.vars().set('begin', 0).set('next', 1)` — synchronization with server.
*   **jQuery Events:** `focus`, `input`, `click` on `$input` — control search and dropdown display.
*   **Global click:** On `document` — hides dropdown when clicking outside the component.
*   **Selected Signal:** Sent when selecting a row — to notify other components.
*   **Field mapping:** `$fieldmap` is passed to JS — used when passing data to the form.
*   **Wrapper element:** Can be a string (ID), object with `$id`, or DOM element — universal interface.
*   **Observer options:** When specifying `$wrapper` — passed to `IntersectionObserver` as `root` option.

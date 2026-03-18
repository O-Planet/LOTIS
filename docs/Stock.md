[English](Stock.md) | [Русский](Stock.ru.md)

🚀 [Quick Start](../QuickStart.md)

# Stock Class

## Purpose
A class for managing operations and synchronizing tabular data in the LOTIS framework. Designed for atomic record updates with automatic recalculation of related collectors (balances, sums, statistics). Solves the task of consistent updates of two related entities: the operations table (detailed document rows) and the balances table (aggregated values by key fields). When operations change, balances are automatically adjusted without risk of data inconsistency. The class compares old and new values, determines changes (addition/update/deletion), records them in the database, and recalculates collectors considering value deltas.

### Key Features

* **Data management** via main table
* **Data synchronization** with collector tables
* **Bulk value updates**
* **Real-time change tracking**
* **Warehouse balance adjustment**

## Properties

| Property | Type | Visibility | Description |
|----------|------|------------|-------------|
| **$dbtable** | `MySqlTable` | `public` | Database table for operations. Set in constructor. Used for reading old values and writing new ones. |
| **$fieldmap** | `array` | `public` | Mapping of database field names to field names in passed values array. Format: `['nameintable' => 'nameinvalues']`. Set in constructor. |
| **$collectors** | `array` | `public` | Array of collector configurations for balance recalculation. Each element contains: `dbtable` (balances table), `selectname` (link field), `fieldmap` (field mapping), `plus` (mode: true=increase, false=decrease). Populated via `collector()`. |

## Methods

### Constructor

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **__construct** | `MySqlTable $dbtable`, `array $fieldmap = []` | — | Class constructor. Saves operations table to `$dbtable`. If `$fieldmap` is string — splits via `\LTS::explodestr()` and creates 1:1 mapping. Otherwise — uses passed array. Initializes `$collectors` as empty array. |

### Adding Collectors

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **collector** | `MySqlTable $dbtable`, `string $selectname`, `array $fieldmap`, `bool $plus = true` | `$this` | Adds collector for balance recalculation. `$dbtable` — balances table. `$selectname` — field in collector table for linking (e.g., product ID). `$fieldmap` — mapping of collector fields to operation fields. `$plus` — mode: `true` = values increase, `false` = decrease. Saves configuration to `$collectors`. Supports fluent interface. |

### Data Synchronization

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **update** | `array $keys`, `array $newvalues` | `$this` | Main method for fixing changes. `$keys` — array of keys for fetching old records (e.g., `['parent_doc' => 3, 'type' => 'incoming']`). `$newvalues` — array of new document rows. Performs: (1) Reading old values via `$dbtable->all($keys)`. (2) Matching old and new via `matchArrays()`. (3) Deleting removed rows via `$dbtable->delall()`. (4) Updating changed via `$dbtable->set()`. (5) Inserting new via `$dbtable->insert()`. (6) Recalculating all collectors via `updateCollector()`. Returns `$this`. |

### Private Methods

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **updateCollector** | `array $collector`, `array $mathresult` | — | Recalculates values in collector table. Gets collector configuration and matching result from `matchArrays()`. For each old/new value pair computes delta: old values subtracted, new added (or vice versa depending on `$plus`). Accumulated deltas by record IDs applied via `$db->set()`. Skips fields where `abs($val) < 1e-9`. |
| **remapNewValuesKeys** | `array $newItem` | `array` | Transforms new value keys from input array format to database format via `$fieldmap`. For each key searches reverse mapping in `$fieldmap`. If not found — uses key as-is. Returns array with database keys. |
| **matchArrays** | `array $oldvalues`, `array $newvalues` | `array` | Matches old and new values by key fields from `$fieldmap`. Returns array with keys: `'matched'` (array of pairs `['old' => ..., 'new' => ...]`), `'unmatched'` (array of new records without pair). For each old record searches match in new by all `$fieldmap` fields. If found — adds to `matched` and removes from `$remainingNew`. If not found — adds to `matched` with `'new' => null` (deletion). |
| **updateDB** | `array $keys`, `array $newItem`, `int $id = null` | — | Updates or inserts record in database. For each field from `$newItem` checks existence via `$dbtable->field()` and sets via `$dbtable->value()`. Adds keys from `$keys` (e.g., document ID). Calls `$dbtable->set($id)` if `$id` specified, otherwise `$dbtable->insert()`. |

## Usage Examples

```php
// Creating stock
$stock = new Stock($stockTable, ['goods' => 'product_id']);

// Adding collector
$stock->collector(
    $goodsTable,        // collector table
    'product_id',       // link field
    [                   // fields to be increased/decreased
        'quantity',
        'amount' => 'total'
    ],
    true               // increase flag
);

// Updating data
$stock->update(
    ['type_doc' => 'incoming', 'parent_docid' => 123],  // keys for finding previously created records
    [                  // new actual data to rewrite stock and update collectors
        ['product_id' => 1, 'quantity' => 10, 'total' => 1000],
        ['product_id' => 2, 'quantity' => 5, 'total' => 850]
    ]
);
```

This example assumes the following data table structure:

```php
$goodsTable
    ->string('name', 100)
    ->float('quantity')
    ->float('amout');

$stockTable
    ->enum('type_doc', ['incoming' => 'Incoming document', ...])
    ->int('parent_docid')
    ->table('goods', $goodsTable)
    ->float('quantity')
    ->float('total');
```

## Notes
*   Class does not inherit from `Construct` — this is a functional class without UI object lifecycle.
*   **Atomicity:** `update()` method performs all operations in single call — deletion, update, insertion and collector recalculation.
*   **Record matching:** Via `matchArrays()` determines which rows are deleted (old exists, new missing), updated (both exist), or added (only new).
*   **Collector delta:** For each collector, difference between old and new values computed — applied to existing balances via addition/subtraction.
*   **Collector mode:** `$plus` parameter in `collector()` determines direction: `true` = income (increase), `false` = expense (decrease).
*   **Link field:** `$selectname` in `collector()` — field in collector table for linking with operation (e.g., `goods` for product link).
*   **Field mapping:** `$fieldmap` transforms field names between database and input array — allows using different names in code and DB.
*   **Selection keys:** `$keys` in `update()` determine which old records to read — usually document ID or other operation group identifiers.
*   **Record deletion:** Records with `'new' => null` in `matched` deleted via `$dbtable->delall('id', $idsToDelete)`.
*   **Record update:** Records with both parts in `matched` updated via `$dbtable->set($pair['old']['id'])`.
*   **Record insertion:** Records from `unmatched` inserted via `$dbtable->set()` without ID (auto-increment).
*   **Calculation precision:** In `updateCollector()` checks `abs($val) >= 1e-9` — ignores minimal deltas to avoid rounding errors.
*   **Multiple collectors:** Via `collector()` can add unlimited collectors — all recalculated in single `update()` call.
*   **Record identification:** In `$newvalues` must have field corresponding to `$selectname` for collector link (e.g., product ID).
*   **Old values:** Read via `$dbtable->all($keys)` with `$dbtable->asarray = true` — returned as associative arrays.
*   **Value freeing:** After `update()` calls `$dbtable->freevalues()` — resets `$isset` flags for fields.
*   **Mapping errors:** If field not found in `$dbtable->field()` — skipped in `updateDB()` via `!== null` check.
*   **Delta grouping:** In `updateCollector()` deltas accumulated by record ID in `$objs[$id]` — then applied via single `set()` per record.
*   **NULL values:** In `updateCollector()` if `$oldval === null` — uses `0` for delta calculation.
*   **Reverse mapping:** In `remapNewValuesKeys()` uses `array_search($key, $fieldmap, true)` — strict type comparison.
*   **String fieldmap:** If `$fieldmap` passed as string in constructor — splits via `\LTS::explodestr()` and creates 1:1 mapping.
*   **Collectors array:** In `$collectors` stores configuration — each element processed in loop in `update()`.
*   **Operation types:** In `matchArrays()` determines: addition (only new), update (both), deletion (only old).
*   **Transactions:** For atomicity recommended to wrap `update()` in `$db->transaction()` at MySql level.
*   **Performance:** All operations performed in memory — minimizes database queries.
*   **Scalability:** Supports large documents with many operation rows and collectors.
*   **Fluent interface:** Methods `collector()` and `update()` return `$this` for method chaining support.
*   **Input validation:** In `update()` checks `is_array($keys)` and `is_array($newvalues)` — returns `$this` if not arrays.
*   **Empty old values:** If `$oldvalues === false` — uses empty array `[]` — all new records treated as additions.
*   **Record IDs:** In `matchArrays()` uses `$oldvalues[$oldselectname]` to get collector record ID.
*   **ID uniqueness:** In `updateCollector()` via `array_unique()` for `$deletedids` and `$changedids` — prevents duplication.
*   **Excluding deleted:** In `updateCollector()` via `array_diff($changedids, $deletedids)` — deleted not updated.
*   **Collector fields:** In `updateCollector()` checks `$db->field($dbkey) !== null` — only existing fields updated.
*   **String fieldmap support:** In `collector()` and constructor — supports string format for simple 1:1 mapping.

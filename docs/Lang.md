[English](Lang.md) | [Русский](Lang.ru.md)

🚀 [Quick Start](../QuickStart.md)

# Lang Class

## Purpose
Localization and multilingual support class of the LOTIS framework, inheriting from `Construct`. Designed for managing application interface translations, loading dictionaries from files, and providing translated strings both on the server and client. Automatically determines the translation file based on the current script name and language identifier. Generates client-side JavaScript code for accessing translations in the browser via `LTS.say()`. Supports multi-line values in translation files and allows saving modified dictionaries back to files.

### Main Features

* **Translation management** through a dictionary
* **Dynamic loading** of language files
* **Saving changes** to translation files
* **Integration** with LTS objects

## Properties

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$elements** | `array` | `private` | Array for storing generated object metadata. Filled by the `shine()` method and used during `Space` rendering. |
| **$words** | `array` | `public` | Associative array of translations. Keys are string identifiers, values are translated text. Supports multi-line values. |
| **$loaded** | `bool` | `private` | Flag of successful translation file loading. Set to `true` if the file is found and read, otherwise `false`. |

### Inherited Properties

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$type** | `string` | `public` | Inherited from `Construct`. Set to `'html'`. Defines the object type for filtering and processing. |
| **$id** | `string` | `public` | Inherited from `Construct`. Language identifier (e.g., `'ru'`, `'en'`, `'de'`). Used for forming the translation file name. |

## Methods

### Constructor

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **__construct** | `string $id = ''` | — | Class constructor. Calls parent `Construct` constructor, initializes `$elements` as an empty array, `$classname` as an empty string, `$type` to `'html'`. If `$id` is not empty — forms the translation file path based on the current script name (`{filename}.{id}`) and calls `loadLangFile()`. The loading result is saved in `$loaded`. |

### Getting Translations

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **say** | `string $word`, `string|bool $key = false` | `string` | Returns the translated string. If `$key === false` — uses `$word` as the search key. If the file is loaded and the key is found in `$words` — returns the translation. Otherwise — returns the original word. If the key is not found — adds it to `$words` with value `$word` for subsequent saving. |

### Reading and Saving Data

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **loadLangFile** | `string $filePath` | `bool` | Private method for loading a translation file. Reads the file line by line, ignoring empty lines. Lines without leading spaces are considered new keys (format `key:value`). Lines with leading spaces — continuation of the previous value (multi-line text). Fills the `$words` array. Returns `true` on success, `false` if the file is not found. |
| **save** | `string $lng` | `$this` | Saves the current translation dictionary to a file. Forms the file name based on the current script name and `$lng` (`{filename}.{lng}`). For each key, writes the first line of the value after `:`, subsequent lines — with indentation (4 spaces). Uses `file_put_contents()`. Returns `$this` to support fluent interface. |

### Lifecycle

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **shine** | `Element $parent = null` | — | Generation of metadata for client-side JavaScript. If the file is loaded (`$loaded == true`) — encodes `$words` to JSON via `json_encode()`. Fills `$elements['script']` with an array containing type `'JS'`, area `'script'`, ID `0`, and script body `LTS.lang('{id}', {json})`. Does not create a DOM element directly. |
| **addinspace** | — | — | Registration of language data in the namespace. If the file is loaded — adds `$elements['script']` to `$space->storage['elements']` via the `set()` method with key `$this->id`. Ensures dictionary transfer to the client during page rendering. |

## Translation File Format

Translation files use a text format with keys and values:

```
welcome:Welcome
description:This is a site about programming
footer:© All rights reserved
multiline:First line
    Second line
    Third line
```

*   **Key**: String before the first colon `:` (without leading spaces).
*   **Value**: String after the colon.
*   **Multi-line values**: Continuations start with space(s) and are added to the previous value with a newline character.
*   **Empty lines**: Ignored during loading.
*   **File name**: `{script_name}.{language}` (e.g., `index.ru`, `index.en`).

## LTS Object Client Methods for Working with Translations

| Function | Parameters | Returns | Description |
|---------|-----------|------------|----------|
| **LTS.lang(lng, words)** | `string lng`, `object words` | — | Registers a translation dictionary for the specified language on the client. Called automatically when rendering the Lang object. |
| **LTS.say(word, key, lng)** | `string word`, `string key`, `string lng` | `string` | Returns the translated string on the client. If `$key` is not specified — uses `$word` as the key. If `$lng` is not specified — uses the current language. If the translation is not found — returns the original word. |

## Usage Examples

File index.en
```
long:This is a very long sentence, so we use a key
```

File index.de 
```
Hello, world!:Hallo Welt!
long:Dies ist ein sehr langer Satz, daher verwenden wir einen Schlüssel.
```

Usage on the server
```php
// Creating a language object
$lang = new Lang('de');

// Getting a translation
echo $lang->say('Hello, world!'); // Outputs: Hallo Welt!
echo $lang->say('long'); // Outputs: Dies ist ein sehr langer Satz, daher verwenden wir einen Schlüssel.

// Saving used words to prepare a translation file
$lang->save('es');
```

Usage on the client
```JS
alert(LTS.lang('Hello, world!'));          
```

## Notes
*   Class extends `Construct`, therefore supports all lifecycle hooks: `$check`, `$before`, `$on`, `$checkchilds`, `$beforechilds`, `$onchilds`.
*   Class does not extend `Element` or `Quark` — has no visual representation and does not create DOM elements.
*   The translation file is searched in the same directory as the current script — the file name is formed as `{filename}.{id}` where `{filename}` is the current PHP file name without extension.
*   The `say()` method automatically adds new keys to `$words` if they are not found — this allows easily expanding dictionaries during development.
*   The `save()` method is useful for exporting accumulated translations to a file — can be used for creating initial dictionaries or updating existing ones.
*   Multi-line values are supported through formatting with indentation — line continuations start with a space.
*   The client dictionary is registered via `LTS.lang()` — after that available via `LTS.say()` in any JavaScript code.
*   Methods `shine()` and `addinspace()` do not create visual elements — Lang is a functional object without DOM representation.
*   Class does not implement `compile()` and `childs()` methods — they are inherited from `Construct` without changes (empty implementations).
*   The `$loaded` flag determines whether the translation file was found — if `false`, the `say()` method always returns the original word.
*   A separate Lang object is created for each language — multiple languages can be used simultaneously in one application.
*   Dictionary JSON encoding uses `JSON_UNESCAPED_UNICODE` — Cyrillic and other Unicode characters are preserved without escaping.
*   When saving via `save()`, multi-line values are split into lines — the first line goes after `:`, the rest — with a 4-space indentation.
*   Class does not support automatic browser language detection — the language is set explicitly via `$id` in the constructor.
*   To switch languages, a new Lang object with a different `$id` needs to be created and the interface re-rendered.
*   The `say()` method on the server and `LTS.say()` on the client have the same logic — return the translation or the original word.
*   Dictionaries are not cached between requests on the server — the file is loaded on each Lang object creation.
*   On the client, dictionaries are stored in `LTS.languages` — available until the end of the browser session.
*   For large projects, it is recommended to move translation files to a separate directory and modify the loading logic in `loadLangFile()`.
*   Class is compatible with the Events system — translations can be used in event handlers on the server and client.
*   If the translation file is missing, the Lang object is still created — `$loaded` will be `false`, but the `say()` method remains functional.
*   To add translations "on the fly", you can directly modify `$words` before calling `shine()`.
*   The `save()` method overwrites the existing file — for merging with existing translations, additional logic is needed.
*   Translation keys are case-sensitive — `'Welcome'` and `'welcome'` are considered different keys.
*   Translation values may contain HTML markup — it is not escaped when output on the client.
*   For security, it is recommended to check and clean translations from untrusted sources before use.
*   Class does not support pluralization — for different number forms, separate keys are needed.
*   Class does not support variable interpolation in translations — for value substitution, use concatenation in code.
*   Lang objects are automatically registered in the namespace via the inheritance mechanism of `addinspace()` from the `Construct` class.
*   During rendering, the dictionary is passed to the client through the initialization script `LTS.lang("{id}", {body})` where `{body}` is the JSON representation of `$words`.

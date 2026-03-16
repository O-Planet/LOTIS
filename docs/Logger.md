[English](Logger.md) | [Русский](Logger.ru.md)

🚀 [Quick Start](../QuickStart.md)

# Logger Class

## Purpose
Central component of the LOTIS framework logging system. Responsible for recording events, errors, and debug information to a file and/or outputting them to the browser. The class implements interception of standard PHP errors and unhandled exceptions, ensuring a unified logging format. Operates in static mode (Singleton pattern), does not allow instance creation. Logger behavior is controlled through global constants defined before framework initialization.

### Main Features

* **Logging** to file or browser
* **Error handling** of PHP
* **Exception handling**
* **Contextual logging**
* **Output mode management**

## Constants

| Constant | Value | Description |
|-----------|----------|----------|
| **LOG_DISABLED** | `0` | Mode of complete logging disablement. |
| **LOG_FILE** | `1` | Logging only to file. |
| **LOG_SCREEN** | `2` | Logging output only to browser (HTML). |
| **LOG_BOTH** | `3` | Duplicating logs to file and browser. |

## Properties

| Property | Type | Visibility | Description |
|----------|-----|-----------|----------|
| **$enabled** | `bool` | `private static` | Logger activity flag. Controlled by the `LOTIS_LOG_DISABLED` constant. |
| **$mode** | `int` | `private static` | Current output mode (see constants). Default `LOG_SCREEN`. |
| **$logFile** | `string` | `private static` | Full path to the log file (`lotis.log`). |
| **$baseDir** | `string` | `private static` | Base directory for storing log files. |
| **$errors** | `array` | `private static` | Buffer of accumulated log entries in memory of the current session. |

## Methods

### Initialization

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **init** | — | `void` | Initialization of the logging system. Called automatically when loading the core. Checks configuration constants, creates a directory for logs, registers PHP error handlers (`set_error_handler`) and exception handlers (`set_exception_handler`). |

### Main Recording Methods

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **log** | `string $level`, `string $message`, `array $context = []` | `void` | Basic logging method. Forms a message string with timestamp, level, Caller class name, file, and line of call. Writes to file and/or outputs to browser depending on the mode. Saves entry to the `$errors` array. |
| **error** | `string $message`, `array $context = []` | `void` | Wrapper for recording `ERROR` level messages. |
| **warning** | `string $message`, `array $context = []` | `void` | Wrapper for recording `WARNING` level messages. |
| **info** | `string $message`, `array $context = []` | `void` | Wrapper for recording `INFO` level messages. |
| **debug** | `string $message`, `array $context = []` | `void` | Wrapper for recording `DEBUG` level messages. |

### Getting Information

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **getErrors** | — | `array` | Returns an array of all log entries accumulated in memory during the current request. |
| **hasErrors** | — | `bool` | Returns `true` if there are entries in the `$errors` buffer. |

### Error Handling

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **errorHandler** | `int $severity`, `string $message`, `string $file`, `int $line` | `void` | Static callback for handling PHP errors. Converts the error into a log entry of `ERROR` level with severity context. |
| **exceptionHandler** | `Throwable $exception` | `void` | Static callback for handling unhandled exceptions. Logs the message, stack trace, and outputs information to the screen in debug mode. |
| **getCaller** | — | `array` | Internal method for analyzing the call stack (`debug_backtrace`). Returns an array with keys `class`, `file`, `line` of the first non-system call (excluding `Logger` and `Quark`). |

## Configuration

Class behavior is controlled by global constants that must be defined **before** connecting the framework core:

*   **LOTIS_LOG_DISABLED** (`bool`): `true` completely disables logging.
*   **LOTIS_LOG_MODE** (`int`): Sets the output mode (1 — file, 2 — screen, 3 — both). Default 2.
*   **LOTIS_LOG_DIR** (`string`): Path to the directory for the `lotis.log` file. Default `/log/` relative to the framework root.

## Usage Examples

```php
// Recording an error
Logger::error('A critical error occurred', ['user_id' => 123]);

// Recording a warning
Logger::warning('Suspicious activity detected');

// Recording information
Logger::info('Operation successfully completed');

// Checking for errors
if (Logger::hasErrors()) {
    // Error handling
    $errors = Logger::getErrors();
}
```

## Notes
*   Class forbids instance creation via `__construct`, `__clone`, `__wakeup` (declared private).
*   Output to the browser is done through HTML blocks with inline styles (red border, monospace font), visible only in `LOG_SCREEN` or `LOG_BOTH` mode.
*   Writing to the file occurs with exclusive locking (`LOCK_EX`) in append mode (`FILE_APPEND`).
*   The `getCaller()` method ignores calls from `LTS\Logger` and `LTS\Quark` classes for correct determination of the error location in user code.
*   Logging can be completely disabled via the LOTIS_LOG_DISABLED constant
*   Logging modes are configured via LOTIS_LOG_MODE
*   When working in CLI mode, output to the browser is not performed
*   All entries contain a timestamp, level, and contextual information

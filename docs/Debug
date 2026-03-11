[English](Debug.md) | [Русский](Debug.ru.md)

🚀 [Quick Start](../QuickStart.md)

# Debug Class

## Purpose
Helper class of the LOTIS framework, providing a unified interface for logging argument validation errors. Acts as a facade over the `Logger` class, specializing in registering errors of method parameter types and values. Used primarily by the `TypeChecker` trait for automatic recording of type violations without interrupting code execution. The class operates in static mode (Singleton pattern), does not allow instance creation.

## Properties
The class contains no own properties — all data is passed directly to the `Logger::log()` method.

## Methods

| Method | Parameters | Returns | Description |
|-------|-----------|------------|----------|
| **typeError** | `string $class`, `string $method`, `string $paramName`, `string $expected`, `mixed $actual` | — | Logs an argument type mismatch error. Forms a structured message with the class name, method, parameter, expected type, and actual type/value. For objects, the class name is specified, for scalar types — type and value. Calls `Logger::error()` with a diagnostic context. |
| **valueError** | `string $class`, `string $method`, `string $paramName`, `string $message`, `mixed $value = null` | — | Logs an invalid argument value error. Accepts a user-defined error message and optionally the parameter value itself. Calls `Logger::error()` with a diagnostic context. Used for validating business rules, ranges, data formats. |

## Message Structure

### For typeError

* Records:
  * Class
  * Method
  * Parameter name
  * Expected type
  * Actual type
  * Parameter value

### For valueError

* Records:
  * Class
  * Method
  * Parameter name
  * Error message
  * Parameter value

## Usage Recommendations

* Use **typeError()** for parameter type mismatches
* Apply **valueError()** for checking value correctness
* Pass the most detailed error information possible
* Use these methods at parameter validation points

## Usage Examples

```php
// Example of logging a type error
Debug::typeError(
    'UserController',
    'createUser',
    'email',
    'string',
    12345
);

// Example of logging a value error
Debug::valueError(
    'ProductModel',
    'savePrice',
    'price',
    'Price must be positive',
    -100
);
```

## Notes
*   Class forbids instance creation via `__construct`, `__clone`, `__wakeup` (declared as private).
*   All methods are declared as `public static` — called without creating an object: `Debug::typeError(...)`.
*   Class does not implement its own logging logic — delegates writing to `Logger`.
*   For non-scalar values (arrays, objects, resources), only the data type is output in the `value` field in parentheses, e.g., `(array)`, `(stdClass)`.
*   For scalar values (string, int, float, bool), the actual value is output to simplify debugging.
*   Main consumers of the class are the `TypeChecker` trait and classes performing manual input data validation.
*   Errors are recorded with the `ERROR` level and include the full call context (class, method, parameter, expected/actual value).
*   Class is automatically connected via `TypeChecker` in all classes inheriting `Construct`.

[English](TypeChecker.md) | [Русский](TypeChecker.ru.md)

🚀 [Quick Start](../QuickStart.md)

# TypeChecker Trait

## Purpose
A helper trait for the LOTIS framework that provides mechanisms for logging validation errors of argument types and values. Used to ensure strict typing in framework class methods. All errors are passed to the central debugging class `Debug`, which allows unified handling and output of error messages. The trait is connected to classes requiring input data validation (e.g., `Construct`) and provides two protected methods for registering errors without interrupting code execution.

## Properties
The trait contains no own properties.

## Methods

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| **typeError** | `string $method`, `string $paramName`, `string $expected`, `string $actual` | — | Logs type mismatch error for an argument. Accepts method name, parameter name, expected type, and actual type of passed value. Calls `Debug::typeError()` to record error in log. Used for validating parameter types in methods. |
| **valueError** | `string $method`, `string $paramName`, `string $message`, `mixed $value = null` | — | Logs invalid value error for an argument. Accepts method name, parameter name, error message, and optionally the value itself. Calls `Debug::valueError()` to record error in log. Used for validating ranges, formats, or business rules for parameters. |

## Notes
*   Trait uses `Debug` class for actual error logging — does not implement logging logic itself.
*   Both methods declared as `protected` — accessible only within classes that use the trait.
*   Methods do not throw exceptions or interrupt code execution — only register errors for subsequent analysis.
*   `$value` parameter in `valueError()` is optional — can be passed for detailed debugging.
*   Trait connected via `use TypeChecker;` in class body (e.g., in `Construct`).
*   Static context `static::class` used for automatic determination of class name that called the error method.
*   Trait enables non-invasive type validation — errors logged without stopping program flow.
*   Integration with `Debug` class ensures consistent error formatting across the framework.
*   Methods support any string-based type descriptions — not limited to PHP native types.
*   Useful for development and testing phases — helps identify type-related issues early.
*   Does not replace PHP's native type declarations — complements them with runtime validation and logging.
*   Error messages include full context: class, method, parameter, expected vs actual — simplifies debugging.
*   Trait follows single responsibility principle — focuses solely on error registration, not handling.
*   Compatible with PHP 7.4+ type system — works alongside native type hints and return types.
*   Can be combined with other validation traits for comprehensive input checking.

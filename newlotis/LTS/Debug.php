<?php
namespace LTS;

class Debug
{
    /**
     * Логирует ошибку типа аргумента
     */
    public static function typeError($class, $method, $paramName, $expected, $actual)
    {
        Logger::error("Invalid argument type", [
            'class' => $class,
            'method' => $method,
            'parameter' => $paramName,
            'expected' => $expected,
            'actual' => is_object($actual) ? get_class($actual) : gettype($actual),
            'value' => is_scalar($actual) ? $actual : '(' . gettype($actual) . ')'
        ]);
    }

    /**
     * Логирует ошибку значения аргумента
     */
    public static function valueError($class, $method, $paramName, $message, $value = null)
    {
        Logger::error("Invalid argument value", [
            'class' => $class,
            'method' => $method,
            'parameter' => $paramName,
            'message' => $message,
            'value' => $value
        ]);
    }
    
    // Запрещаем создание экземпляров
    private function __construct() {}
    private function __clone() {}
    private function __wakeup() {}
}
?>

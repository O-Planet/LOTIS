<?php
namespace LTS;

trait TypeChecker
{
    /**
     * Логирует ошибку типа аргумента
     */
    protected function typeError($method, $paramName, $expected, $actual)
    {
        Debug::typeError(static::class, $method, $paramName, $expected, $actual);
    }

    /**
     * Логирует ошибку значения аргумента
     */
    protected function valueError($method, $paramName, $message, $value = null)
    {
        Debug::valueError(static::class, $method, $paramName, $message, $value);
    }
}
?>

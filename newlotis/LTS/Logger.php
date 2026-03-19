<?php
/**
 * Настройка логгирования LOTIS
 * 
 * Для управления поведением системы логгирования используйте следующие константы:
 * 
 * define('LOTIS_LOG_DISABLED', true);
 *   - Полностью отключает систему логгирования.
 * 
 * define('LOTIS_LOG_MODE', ...);
 *   - Определяет режим вывода логов. Допустимые значения:
 *     1 - Логи пишутся только в файл.
 *     2 - Логи выводятся только на экран (в браузер).
 *     3 - Логи пишутся в файл И выводятся на экран.
 *   - Если константа LOTIS_LOG_MODE не определена, используется режим 2 (браузер).
 * 
 * define('LOTIS_LOG_DIR', '/путь/к/папке/');
 *   - Указывает директорию для файла логов (lotis.log).
 *   - По умолчанию: [корень newlotis]/log/
 * 
 * Примеры:
 * 
 * // Выводить ошибки и в файл, и на экран
 * define('LOTIS_LOG_MODE', 3);
 * 
 * // Отключить логирование
 * define('LOTIS_LOG_DISABLED', true);
 */

namespace LTS;

if (!defined('LOTIS_LOG_DIR'))
define('LOTIS_LOG_DIR', dirname(__DIR__) . '/log/');

class Logger
{
    private static $enabled = true;
    private static $mode = 1; // По умолчанию LOTIS_LOG_FILE
    private static $logFile = '';
    private static $baseDir = '';
    private static $errors = [];

    // Константы (дублируем здесь для внутреннего использования)
    const LOG_DISABLED = 0;
    const LOG_FILE     = 1;
    const LOG_SCREEN   = 2;
    const LOG_BOTH     = 3;

    /**
     * Инициализация логгера.
     * Вызывается из lotis.php после определения констант пользователем.
     */
    public static function init()
    {
        // Проверяем, отключено ли логгирование полностью
        if (defined('LOTIS_LOG_DISABLED') && constant('LOTIS_LOG_DISABLED')) {
            self::$enabled = false;
            return;
        }

        $invalidModeWarning = null; // Для отложенного предупреждения        

        // // Определяем режим
        if (!defined('LOTIS_LOG_MODE')) 
            // Режим по умолчанию: только в файл
            self::$mode = self::LOG_SCREEN;
        else {
            $modeConst = constant('LOTIS_LOG_MODE');
            // Проверяем корректность значения
            if (in_array($modeConst, [self::LOG_FILE, self::LOG_SCREEN, self::LOG_BOTH])) 
                self::$mode = $modeConst;
            else {
                // Если значение некорректное — используем 'screen' и логируем предупреждение
                self::$mode = self::LOG_SCREEN;
                $invalidModeWarning = "Некорректное значение LOTIS_LOG_MODE='{$modeConst}'. Используется режим 'screen'.";
            }
        }

        // Логгирование включено
        self::$enabled = true;

        if(self::$mode !== self::LOG_SCREEN)
        {
            // Определяем директорию
            self::$baseDir = rtrim(constant('LOTIS_LOG_DIR'), '/') . '/';

            // Создаём директорию, если не существует
            if (!is_dir(self::$baseDir)) 
                mkdir(self::$baseDir, 0777, true);

            self::$logFile = self::$baseDir . 'lotis.log';
        }

        // Подключаем обработчики ошибок PHP
        set_error_handler([self::class, 'errorHandler']);
        set_exception_handler([self::class, 'exceptionHandler']);

            // --- Шаг 6: Отложенное логгирование предупреждений ---
        if ($invalidModeWarning !== null) 
            self::warning($invalidModeWarning);
    }

    /**
     * Основной метод записи лога
     */
    public static function log($level, $message, $context = [])
    {
        if (!self::$enabled) return;

        $timestamp = date('Y-m-d H:i:s');
        $caller = self::getCaller();

        $entry = "[{$timestamp}] {$level}: {$message}";

        // Добавляем информацию о классе, если доступна
        if ($caller && isset($caller['class'])) 
            $entry .= " in {$caller['class']}";

        // Всегда добавляем файл и строку
        $file = isset($caller['file']) ? $caller['file'] : 'unknown';
        $line = isset($caller['line']) ? $caller['line'] : 0;
        $entry .= " ({$file}:{$line})";

        // Добавляем контекст
        if ($context) 
            $entry .= ' ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        self::$errors[] = $entry;

        // Форматированная строка для вывода/записи
        $output = $entry;

        // Запись в файл
        if (in_array(self::$mode, [self::LOG_FILE, self::LOG_BOTH])) 
            file_put_contents(self::$logFile, $output . PHP_EOL, FILE_APPEND | LOCK_EX);

        // Вывод на экран (только в веб-режиме)
        if (in_array(self::$mode, [self::LOG_SCREEN, self::LOG_BOTH]) && php_sapi_name() !== 'cli') 
            echo "<div style='color: #d32f2f; font-family: monospace; border-left: 4px solid #d32f2f; padding: 8px; margin: 5px 0; background: #ffebee; font-size: 12px;'>{$output}</div>";
    }

    // Удобные методы
    public static function error($message, $context = []) { self::log('ERROR', $message, $context); }
    public static function warning($message, $context = []) { self::log('WARNING', $message, $context); }
    public static function info($message, $context = []) { self::log('INFO', $message, $context); }
    public static function debug($message, $context = []) { self::log('DEBUG', $message, $context); }

    // Получение списка ошибок
    public static function getErrors() { return self::$errors; }
    public static function hasErrors() { return !empty(self::$errors); }

    /**
     * Получение информации о месте вызова
     */
    private static function getCaller()
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);

        foreach ($trace as $frame) {
            // Пропускаем системные вызовы
            if (isset($frame['class']) && in_array($frame['class'], ['LTS\\Logger', 'LTS\\Quark'])) {
                continue;
            }

            // Возвращаем первый найденный пользовательский вызов
            return [
                'class' => isset($frame['class']) ? $frame['class'] : null,
                'file'  => isset($frame['file']) ? $frame['file'] : 'unknown',
                'line'  => isset($frame['line']) ? $frame['line'] : 0
            ];
        }

        // Если не нашли, возвращаем хотя бы базовую информацию
        return ['class' => null, 'file' => 'unknown', 'line' => 0];
    }

    /**
     * Обработчик ошибок PHP
     */
    public static function errorHandler($severity, $message, $file, $line)
    {
        if (!(error_reporting() & $severity)) return;

        self::error("PHP Error: {$message}", [
            'severity' => $severity,
            'file' => $file,
            'line' => $line
        ]);
    }

    /**
     * Обработчик исключений
     */
    public static function exceptionHandler($exception)
    {
        self::error("Uncaught Exception: " . $exception->getMessage(), [
            'class' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);

        // При необходимости показываем исключение на экране
        if (in_array(self::$mode, [self::LOG_SCREEN, self::LOG_BOTH])) {
            echo "<h3 style='color: #c62828'>Необработанное исключение</h3>";
            echo "<pre style='background: #ffebee; color: #d32f2f; padding: 10px; border-radius: 4px;'>" . nl2br(htmlspecialchars($exception)) . "</pre>";
        }
    }

    // Запрещаем создание экземпляров
    private function __construct() {}
    private function __clone() {}
    private function __wakeup() {}
}
?>

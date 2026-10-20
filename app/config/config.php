<?php
declare(strict_types=1);

function envValue(string $key, string $default = ''): string
{
    static $values;
    if ($values === null) {
        $values = [];
        $file = dirname(__DIR__, 2) . '/.env';
        if (is_readable($file)) {
            foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
                [$name, $value] = explode('=', $line, 2);
                $values[trim($name)] = trim($value, " \t\n\r\0\x0B\"");
            }
        }
    }
    return $values[$key] ?? getenv($key) ?: $default;
}

const DB_HOST = '127.0.0.1';
const DB_PORT = '8000';
const DB_NAME = 'simon';
const DB_USER = 'root';
const DB_PASSWORD = '';
const APP_NAME = 'I.E. Simon Bolivar';
define('GOOGLE_SHEET_ID', envValue('GOOGLE_SHEET_ID', '1oGtawL_y74UvzOdJfJMYPBRVEZtQF6XFko4eHmfzW2c'));
define('GOOGLE_SHEET_GID', envValue('GOOGLE_SHEET_GID', '0'));

function db(): PDO
{
    static $connection;

    if ($connection instanceof PDO) {
        return $connection;
    }

    $dsn = 'mysql:host=' . envValue('DB_HOST', DB_HOST) . ';port=' . envValue('DB_PORT', DB_PORT) . ';dbname=' . envValue('DB_NAME', DB_NAME) . ';charset=utf8mb4';
    $connection = new PDO($dsn, envValue('DB_USER', DB_USER), envValue('DB_PASSWORD', DB_PASSWORD), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $connection;
}

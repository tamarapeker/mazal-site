<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $host = (string)config('database.host', '127.0.0.1');
        $port = (int)config('database.port', 3306);
        $name = (string)config('database.name', '');
        $user = (string)config('database.user', '');
        $password = (string)config('database.password', '');
        $charset = (string)config('database.charset', 'utf8mb4');

        if ($name === '') {
            throw new RuntimeException('Missing database name in configuration.');
        }

        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $host, $port, $name, $charset);

        try {
            self::$connection = new PDO(
                $dsn,
                $user,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ],
            );
        } catch (PDOException $exception) {
            throw new RuntimeException('Database connection failed: ' . $exception->getMessage(), 0, $exception);
        }

        return self::$connection;
    }
}

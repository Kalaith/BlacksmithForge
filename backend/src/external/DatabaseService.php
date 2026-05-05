<?php

declare(strict_types=1);

namespace App\External;

use App\Core\Environment;

class DatabaseService
{
    private static ?DatabaseService $instance = null;
    private $pdo;

    private function __construct()
    {
        $this->loadEnvironmentFile();
        $charset = 'utf8mb4';
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            Environment::required('DB_HOST'),
            Environment::requiredInt('DB_PORT'),
            Environment::required('DB_NAME'),
            $charset
        );

        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->pdo = new \PDO(
                $dsn,
                Environment::required('DB_USER'),
                Environment::required('DB_PASSWORD', true),
                $options
            );
            $this->pdo->exec("SET NAMES {$charset}");
        } catch (\PDOException $e) {
            throw new \RuntimeException("Connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getPdo(): \PDO
    {
        return $this->pdo;
    }

    private function loadEnvironmentFile(): void
    {
        $envPath = __DIR__ . '/../../.env';
        if (!file_exists($envPath)) {
            return;
        }
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                $value = trim($value, "\"'");
                if (!isset($_ENV[$key])) {
                    $_ENV[$key] = $value;
                    putenv("{$key}={$value}");
                }
            }
        }
    }

    public function testConnection(): bool
    {
        try {
            $stmt = $this->pdo->prepare("SELECT 1");
            $stmt->execute();
            return $stmt !== false;
        } catch (\PDOException $e) {
            return false;
        }
    }

    // Prevent cloning and unserialization
    private function __clone() {}
    public function __wakeup(): void {}
}

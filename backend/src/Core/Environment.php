<?php

declare(strict_types=1);

namespace App\Core;

final class Environment
{
    public static function required(string $key, bool $allowEmpty = false): string
    {
        if (!array_key_exists($key, $_ENV) || !is_string($_ENV[$key])) {
            throw new \RuntimeException("Missing required environment variable: {$key}");
        }

        $value = trim($_ENV[$key]);
        if (!$allowEmpty && $value === '') {
            throw new \RuntimeException("Missing required environment variable: {$key}");
        }

        return $value;
    }

    public static function requiredInt(string $key): int
    {
        $value = self::required($key);
        if (!ctype_digit($value)) {
            throw new \RuntimeException("Environment variable {$key} must be an integer");
        }

        return (int) $value;
    }
}

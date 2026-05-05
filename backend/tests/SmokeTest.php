<?php

declare(strict_types=1);

namespace Tests;

use App\Core\Environment;
use PHPUnit\Framework\TestCase;

final class SmokeTest extends TestCase
{
    public function testEnvironmentRequiredFailsFast(): void
    {
        unset($_ENV['BLACKSMITH_FORGE_REQUIRED_TEST']);

        $this->expectException(\RuntimeException::class);
        Environment::required('BLACKSMITH_FORGE_REQUIRED_TEST');
    }

    public function testEnvironmentRequiredReturnsConfiguredValue(): void
    {
        $_ENV['BLACKSMITH_FORGE_REQUIRED_TEST'] = 'configured';

        self::assertSame('configured', Environment::required('BLACKSMITH_FORGE_REQUIRED_TEST'));
    }
}

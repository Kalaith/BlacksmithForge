<?php

declare(strict_types=1);

$localAutoload = dirname(__DIR__) . '/vendor/autoload.php';
$centralAutoload = dirname(__DIR__, 3) . '/vendor/autoload.php';

if (file_exists($localAutoload)) {
    require $localAutoload;
} elseif (file_exists($centralAutoload)) {
    $loader = require $centralAutoload;
    $loader->setPsr4('App\\', [dirname(__DIR__) . '/src/']);
    $loader->setPsr4('Tests\\', [__DIR__ . '/']);
} else {
    throw new RuntimeException('Composer autoload not found locally or in WebHatchery root.');
}

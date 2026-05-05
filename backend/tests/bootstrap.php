<?php

declare(strict_types=1);

$localAutoload = dirname(__DIR__) . '/vendor/autoload.php';
$centralAutoloadCandidates = [
    dirname(__DIR__, 3) . '/vendor/autoload.php',
    dirname(__DIR__, 4) . '/vendor/autoload.php',
];

if (file_exists($localAutoload)) {
    require $localAutoload;
}

$centralLoaded = false;
foreach ($centralAutoloadCandidates as $centralAutoload) {
    if (file_exists($centralAutoload)) {
        $loader = require $centralAutoload;
        $loader->setPsr4('App\\', [dirname(__DIR__) . '/src/']);
        $loader->setPsr4('Tests\\', [__DIR__ . '/']);
        $centralLoaded = true;
        break;
    }
}

if (!$centralLoaded && !file_exists($localAutoload)) {
    throw new RuntimeException('Composer autoload not found locally or in WebHatchery root.');
}

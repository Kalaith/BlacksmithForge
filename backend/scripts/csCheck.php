<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$directories = ['src', 'public', 'scripts', 'tests'];
$missing = [];

foreach ($directories as $directory) {
    $path = $root . DIRECTORY_SEPARATOR . $directory;
    if (!is_dir($path)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || $file->getExtension() !== 'php') {
            continue;
        }

        $contents = file_get_contents($file->getPathname());
        if ($contents === false || !str_contains($contents, 'declare(strict_types=1);')) {
            $missing[] = $file->getPathname();
        }
    }
}

if ($missing !== []) {
    fwrite(STDERR, "Missing declare(strict_types=1):\n" . implode("\n", $missing) . "\n");
    exit(1);
}

exit(0);

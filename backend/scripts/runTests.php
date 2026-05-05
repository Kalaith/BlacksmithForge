<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';
$centralAutoload = dirname(__DIR__, 3) . '/vendor/autoload.php';

if (file_exists($autoload)) {
    require $autoload;
} elseif (file_exists($centralAutoload)) {
    $loader = require $centralAutoload;
    $loader->setPsr4('App\\', [$root . '/src/']);
} else {
    fwrite(STDERR, "Composer autoload not found.\n");
    exit(1);
}

unset($_ENV['BLACKSMITH_FORGE_REQUIRED_TEST']);

try {
    \App\Core\Environment::required('BLACKSMITH_FORGE_REQUIRED_TEST');
    fwrite(STDERR, "Environment::required did not fail for missing value.\n");
    exit(1);
} catch (RuntimeException) {
}

$_ENV['BLACKSMITH_FORGE_REQUIRED_TEST'] = 'configured';
if (\App\Core\Environment::required('BLACKSMITH_FORGE_REQUIRED_TEST') !== 'configured') {
    fwrite(STDERR, "Environment::required did not return configured value.\n");
    exit(1);
}

$authController = file_get_contents($root . '/src/controllers/AuthController.php');
if ($authController === false) {
    fwrite(STDERR, "Unable to read AuthController.php.\n");
    exit(1);
}

foreach (['public function login(', 'public function register('] as $forbiddenAuthMethod) {
    if (str_contains($authController, $forbiddenAuthMethod)) {
        fwrite(STDERR, "Local auth method found in AuthController: {$forbiddenAuthMethod}\n");
        exit(1);
    }
}

$routes = file_get_contents($root . '/src/routes/router.php');
if ($routes === false) {
    fwrite(STDERR, "Unable to read router.php.\n");
    exit(1);
}

foreach (['{user_id}', '{userId}'] as $forbiddenRouteToken) {
    if (str_contains($routes, $forbiddenRouteToken)) {
        fwrite(STDERR, "Authenticated routes must not expose {$forbiddenRouteToken}.\n");
        exit(1);
    }
}

$linkGuestAction = file_get_contents($root . '/src/actions/LinkGuestAccountAction.php');
if ($linkGuestAction === false) {
    fwrite(STDERR, "Unable to read LinkGuestAccountAction.php.\n");
    exit(1);
}

if (str_contains($linkGuestAction, 'use PDO;') || str_contains($linkGuestAction, '$this->pdo')) {
    fwrite(STDERR, "LinkGuestAccountAction must delegate persistence to repositories.\n");
    exit(1);
}

echo "Smoke tests passed.\n";

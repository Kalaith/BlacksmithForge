<?php

declare(strict_types=1);

// Local autoloader for deployed API src (avoid central autoload mapping collisions)
$localAutoloader = function (string $class): void {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../src/';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($file)) {
        require $file;
    }
};
spl_autoload_register($localAutoloader, true, true);

$centralAutoload = __DIR__ . '/../../../vendor/autoload.php';
if (!file_exists($centralAutoload)) {
    throw new \RuntimeException('Central vendor autoload not found at ' . $centralAutoload);
}
$loader = require $centralAutoload;
$loader->setPsr4('App\\', [__DIR__ . '/../src/']);
spl_autoload_unregister($localAutoloader);
spl_autoload_register($localAutoloader, true, true);

use Dotenv\Dotenv;
use App\External\DatabaseService;
use App\Utils\ContainerConfig;
use App\Core\Router;
use App\Core\Environment;

// Load environment variables first
$dotenvPath = __DIR__ . '/..';
if (!file_exists($dotenvPath . '/.env')) {
    throw new \RuntimeException('Missing .env at ' . $dotenvPath . '/.env');
}
$dotenv = Dotenv::createImmutable($dotenvPath);
$dotenv->load();

// Add required environment variables
$required_env_vars = [
    'DB_HOST',
    'DB_PORT',
    'DB_NAME',
    'DB_USER',
    'DB_PASSWORD',
    'APP_BASE_PATH',
    'WEB_HATCHERY_LOGIN_URL',
    'JWT_SECRET',
    'JWT_ISSUER',
    'JWT_AUDIENCE',
];
foreach ($required_env_vars as $var) {
    Environment::required($var, $var === 'DB_PASSWORD');
}

// Create DI Container
$container = ContainerConfig::createContainer();

// Initialize database service after environment variables are loaded
$db = DatabaseService::getInstance();

// Router (Anime Prompt Gen pattern)
$router = new Router($container);

// Set base path for subdirectory deployment.
$router->setBasePath(rtrim(Environment::required('APP_BASE_PATH'), '/'));

// Handle CORS preflight
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Content-Type, Accept, Origin, Authorization');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    http_response_code(200);
    exit;
}

// Load routes
(require __DIR__ . '/../src/routes/router.php')($router);

// Run router
$router->handle();

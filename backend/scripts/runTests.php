<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';
$centralAutoloadCandidates = [
    dirname(__DIR__, 3) . '/vendor/autoload.php',
    dirname(__DIR__, 4) . '/vendor/autoload.php',
];

if (file_exists($autoload)) {
    require $autoload;
}

$centralLoaded = false;
foreach ($centralAutoloadCandidates as $centralAutoload) {
    if (file_exists($centralAutoload)) {
        $loader = require $centralAutoload;
        $loader->setPsr4('App\\', [$root . '/src/']);
        $centralLoaded = true;
        break;
    }
}

if (!$centralLoaded && !file_exists($autoload)) {
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

foreach (["/inventory/add", "/inventory/remove", "/minigames/play"] as $forbiddenClientMutationRoute) {
    if (str_contains($routes, $forbiddenClientMutationRoute)) {
        fwrite(STDERR, "Direct client mutation route found: {$forbiddenClientMutationRoute}\n");
        exit(1);
    }
}

foreach (["'/materials', [MaterialController::class, 'create'], \$admin", "'/recipes', [RecipeController::class, 'create'], \$admin"] as $requiredAdminRoute) {
    if (!str_contains($routes, $requiredAdminRoute)) {
        fwrite(STDERR, "Shared catalog write route is not admin-gated: {$requiredAdminRoute}\n");
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

foreach (glob($root . '/src/actions/*.php') ?: [] as $actionFile) {
    $contents = file_get_contents($actionFile);
    if ($contents === false) {
        fwrite(STDERR, "Unable to read action file {$actionFile}.\n");
        exit(1);
    }

    foreach (['static function', 'ContainerConfig::createContainer', 'container->get'] as $forbiddenActionPattern) {
        if (str_contains($contents, $forbiddenActionPattern)) {
            fwrite(STDERR, "Action contains forbidden service-locator/static pattern: {$actionFile}\n");
            exit(1);
        }
    }
}

$_ENV['JWT_SECRET'] = 'blacksmith-test-secret-with-enough-entropy';
$_ENV['JWT_ISSUER'] = 'webhatchery-test';
$_ENV['JWT_AUDIENCE'] = 'blacksmith-test';
$_ENV['WEB_HATCHERY_LOGIN_URL'] = 'https://login.example.test';

$middleware = new \App\Middleware\WebHatcheryJwtMiddleware();
$missingAuth = $middleware(
    new \App\Http\Request([], [], [], [], 'GET', '/api/v1/inventory'),
    new \App\Http\Response()
);

if (!$missingAuth instanceof \App\Http\Response || $missingAuth->getStatusCode() !== 401) {
    fwrite(STDERR, "Missing auth did not return 401 response.\n");
    exit(1);
}

$missingPayload = json_decode((string) $missingAuth->getBody(), true);
if (($missingPayload['login_url'] ?? null) !== $_ENV['WEB_HATCHERY_LOGIN_URL']) {
    fwrite(STDERR, "401 response did not include configured login_url.\n");
    exit(1);
}

$issuedAt = time();
$guestToken = \Firebase\JWT\JWT::encode([
    'sub' => 42,
    'user_id' => 42,
    'guest_user_id' => 42,
    'username' => 'guest_test',
    'roles' => ['guest'],
    'auth_type' => 'guest',
    'is_guest' => true,
    'iss' => $_ENV['JWT_ISSUER'],
    'aud' => $_ENV['JWT_AUDIENCE'],
    'iat' => $issuedAt,
    'exp' => $issuedAt + 3600,
], $_ENV['JWT_SECRET'], 'HS256');

$authenticated = $middleware(
    new \App\Http\Request(['authorization' => "Bearer {$guestToken}"], [], [], [], 'GET', '/api/v1/inventory'),
    new \App\Http\Response()
);

if (!$authenticated instanceof \App\Http\Request) {
    fwrite(STDERR, "Valid guest token did not return authenticated request.\n");
    exit(1);
}

$authUser = $authenticated->getAttribute('auth_user');
if (!is_array($authUser) || ($authUser['id'] ?? null) !== 42 || ($authUser['is_guest'] ?? null) !== true) {
    fwrite(STDERR, "Authenticated request did not carry expected auth_user attributes.\n");
    exit(1);
}

$adminMiddleware = new \App\Middleware\AdminRoleMiddleware();
$guestRequest = (new \App\Http\Request([], [], [], [], 'POST', '/api/v1/materials'))
    ->withAttribute('auth_user', [
        'id' => 42,
        'role' => 'guest',
        'roles' => ['guest'],
        'is_guest' => true,
    ]);
$forbidden = $adminMiddleware($guestRequest, new \App\Http\Response());
if (!$forbidden instanceof \App\Http\Response || $forbidden->getStatusCode() !== 403) {
    fwrite(STDERR, "Guest catalog write did not return 403.\n");
    exit(1);
}

$adminRequest = (new \App\Http\Request([], [], [], [], 'POST', '/api/v1/materials'))
    ->withAttribute('auth_user', [
        'id' => 1,
        'role' => 'admin',
        'roles' => ['admin'],
        'is_guest' => false,
    ]);
$allowed = $adminMiddleware($adminRequest, new \App\Http\Response());
if (!$allowed instanceof \App\Http\Request) {
    fwrite(STDERR, "Admin catalog write did not continue the request.\n");
    exit(1);
}

echo "Smoke tests passed.\n";

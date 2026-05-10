<?php

declare(strict_types=1);

use App\Core\Router;
use App\Controllers\MaterialController;
use App\Controllers\RecipeController;
use App\Controllers\CustomerController;
use App\Controllers\InventoryController;
use App\Controllers\CraftingController;
use App\Controllers\UpgradeController;
use App\Controllers\MiniGameController;
use App\Controllers\AuthController;
use App\Middleware\AdminRoleMiddleware;
use App\Middleware\WebHatcheryJwtMiddleware;

return function (Router $router): void {
    $api = '/api/v1';
    $auth = [WebHatcheryJwtMiddleware::class];
    $admin = [WebHatcheryJwtMiddleware::class, AdminRoleMiddleware::class];

    $healthHandler = function ($request, $response, $args) {
        $startTime = microtime(true);

        $health = [
            'status' => 'ok',
            'timestamp' => date('Y-m-d H:i:s'),
            'service' => 'Blacksmith Forge API',
            'version' => '1.0.0',
            'environment' => \App\Core\Environment::required('APP_ENV'),
            'uptime' => round(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 3),
            'checks' => []
        ];

        try {
            $db = \App\External\DatabaseService::getInstance();
            if ($db->testConnection()) {
                $health['checks']['database'] = [
                    'status' => 'healthy',
                    'message' => 'Database connection successful'
                ];
            } else {
                $health['checks']['database'] = [
                    'status' => 'unhealthy',
                    'message' => 'Database connection failed'
                ];
                $health['status'] = 'degraded';
            }
        } catch (\Exception $e) {
            $health['checks']['database'] = [
                'status' => 'unhealthy',
                'message' => 'Database error: ' . $e->getMessage()
            ];
            $health['status'] = 'down';
        }

        $requiredEnvVars = ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'WEB_HATCHERY_LOGIN_URL'];
        $missingVars = [];
        foreach ($requiredEnvVars as $var) {
            try {
                \App\Core\Environment::required($var, $var === 'DB_PASSWORD');
            } catch (\RuntimeException $error) {
                $missingVars[] = $var;
            }
        }

        if (empty($missingVars)) {
            $health['checks']['environment'] = [
                'status' => 'healthy',
                'message' => 'All required environment variables present'
            ];
        } else {
            $health['checks']['environment'] = [
                'status' => 'unhealthy',
                'message' => 'Missing environment variables: ' . implode(', ', $missingVars)
            ];
            $health['status'] = 'down';
        }

        $requiredExtensions = ['pdo', 'pdo_mysql', 'json'];
        $missingExtensions = [];
        foreach ($requiredExtensions as $ext) {
            if (!extension_loaded($ext)) {
                $missingExtensions[] = $ext;
            }
        }

        if (empty($missingExtensions)) {
            $health['checks']['php_extensions'] = [
                'status' => 'healthy',
                'message' => 'All required PHP extensions loaded'
            ];
        } else {
            $health['checks']['php_extensions'] = [
                'status' => 'unhealthy',
                'message' => 'Missing PHP extensions: ' . implode(', ', $missingExtensions)
            ];
            $health['status'] = 'down';
        }

        $health['response_time_ms'] = round((microtime(true) - $startTime) * 1000, 2);
        $statusCode = $health['status'] === 'down' ? 503 : 200;

        $response->getBody()->write(json_encode($health, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($statusCode);
    };

    // Materials (auth required)
    $router->get($api . '/materials', [MaterialController::class, 'getAll'], $auth);
    $router->get($api . '/materials/{id}', [MaterialController::class, 'get'], $auth);
    $router->get($api . '/materials/type/{type}', [MaterialController::class, 'getByType'], $auth);
    $router->get($api . '/materials/rarity/{rarity}', [MaterialController::class, 'getByRarity'], $auth);
    $router->post($api . '/materials', [MaterialController::class, 'create'], $admin);
    $router->put($api . '/materials/{id}', [MaterialController::class, 'update'], $admin);
    $router->delete($api . '/materials/{id}', [MaterialController::class, 'delete'], $admin);
    $router->get($api . '/materials/user', [MaterialController::class, 'getUserMaterials'], $auth);
    $router->post($api . '/materials/purchase', [MaterialController::class, 'purchaseMaterial'], $auth);

    // Recipes (auth required)
    $router->get($api . '/recipes', [RecipeController::class, 'getAll'], $auth);
    $router->get($api . '/recipes/{id}', [RecipeController::class, 'get'], $auth);
    $router->post($api . '/recipes', [RecipeController::class, 'create'], $admin);
    $router->put($api . '/recipes/{id}', [RecipeController::class, 'update'], $admin);
    $router->delete($api . '/recipes/{id}', [RecipeController::class, 'delete'], $admin);

    // Customers (auth required)
    $router->get($api . '/customers', [CustomerController::class, 'getAll'], $auth);
    $router->get($api . '/customers/{id}', [CustomerController::class, 'get'], $auth);
    $router->post($api . '/customers', [CustomerController::class, 'create'], $admin);
    $router->put($api . '/customers/{id}', [CustomerController::class, 'update'], $admin);
    $router->delete($api . '/customers/{id}', [CustomerController::class, 'delete'], $admin);
    $router->get($api . '/customers/current', [CustomerController::class, 'getCurrentCustomer'], $auth);
    $router->post($api . '/customers/generate', [CustomerController::class, 'generateCustomer'], $auth);
    $router->post($api . '/customers/sell', [CustomerController::class, 'sellItem'], $auth);
    $router->get($api . '/customers/price/{item_id}/{customer_id}', [CustomerController::class, 'getSellingPrice'], $auth);
    $router->post($api . '/customers/dismiss', [CustomerController::class, 'dismissCustomer'], $auth);

    // Inventory
    $router->get($api . '/inventory', [InventoryController::class, 'get'], $auth);

    // Crafting
    $router->post($api . '/crafting/start', [CraftingController::class, 'startCrafting'], $auth);
    $router->post($api . '/crafting/hammer-hit', [CraftingController::class, 'processHammerHit'], $auth);
    $router->post($api . '/crafting/complete', [CraftingController::class, 'completeCrafting'], $auth);
    $router->get($api . '/crafting/validate/{recipe_id}', [CraftingController::class, 'validateCrafting'], $auth);
    $router->get($api . '/crafting/session/{crafting_session_id}', [CraftingController::class, 'getCraftingSession'], $auth);
    $router->post($api . '/crafting/craft', [CraftingController::class, 'craft'], $auth);
    $router->get($api . '/crafting/history', [CraftingController::class, 'history'], $auth);

    // Upgrades (auth required)
    $router->get($api . '/upgrades', [UpgradeController::class, 'getAll'], $auth);
    $router->get($api . '/upgrades/purchased', [UpgradeController::class, 'getPurchased'], $auth);
    $router->post($api . '/upgrades/purchase', [UpgradeController::class, 'purchase'], $auth);

    // Mini-Games
    $router->get($api . '/minigames/history', [MiniGameController::class, 'history'], $auth);

    // Auth endpoints
    $router->get($api . '/auth/login-info', [AuthController::class, 'loginInfo']);
    $router->get($api . '/auth/session', [AuthController::class, 'session'], $auth);
    $router->post($api . '/auth/guest-session', [AuthController::class, 'guestSession']);
    $router->post($api . '/auth/link-guest', [AuthController::class, 'linkGuest'], $auth);

    // Health and ping
    $router->get($api . '/health', $healthHandler);
    $router->get('/health', $healthHandler);
    $router->get('/ping', function ($request, $response) {
        $response->getBody()->write(json_encode(['pong' => time()]));
        return $response->withHeader('Content-Type', 'application/json');
    });
};

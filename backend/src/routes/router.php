<?php

use App\Core\Router;
use App\Controllers\MaterialController;
use App\Controllers\RecipeController;
use App\Controllers\CustomerController;
use App\Controllers\InventoryController;
use App\Controllers\CraftingController;
use App\Controllers\UpgradeController;
use App\Controllers\MiniGameController;
use App\Controllers\AuthController;
use App\Middleware\WebHatcheryJwtMiddleware;

return function (Router $router): void {
    $api = '/api/v1';

    $healthHandler = function ($request, $response, $args) {
        $startTime = microtime(true);

        $health = [
            'status' => 'ok',
            'timestamp' => date('Y-m-d H:i:s'),
            'service' => 'Blacksmith Forge API',
            'version' => '1.0.0',
            'environment' => $_ENV['APP_ENV'] ?? 'development',
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

        $requiredEnvVars = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASSWORD'];
        $missingVars = [];
        foreach ($requiredEnvVars as $var) {
            if (!isset($_ENV[$var]) || empty($_ENV[$var])) {
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
    $router->get($api . '/materials', [MaterialController::class, 'getAll'], [WebHatcheryJwtMiddleware::class]);
    $router->get($api . '/materials/{id}', [MaterialController::class, 'get'], [WebHatcheryJwtMiddleware::class]);
    $router->get($api . '/materials/type/{type}', [MaterialController::class, 'getByType'], [WebHatcheryJwtMiddleware::class]);
    $router->get($api . '/materials/rarity/{rarity}', [MaterialController::class, 'getByRarity'], [WebHatcheryJwtMiddleware::class]);
    $router->post($api . '/materials', [MaterialController::class, 'create'], [WebHatcheryJwtMiddleware::class]);
    $router->put($api . '/materials/{id}', [MaterialController::class, 'update'], [WebHatcheryJwtMiddleware::class]);
    $router->delete($api . '/materials/{id}', [MaterialController::class, 'delete'], [WebHatcheryJwtMiddleware::class]);
    $router->get($api . '/materials/user/{userId}', [MaterialController::class, 'getUserMaterials'], [WebHatcheryJwtMiddleware::class]);
    $router->post($api . '/materials/purchase', [MaterialController::class, 'purchaseMaterial'], [WebHatcheryJwtMiddleware::class]);

    // Recipes (auth required)
    $router->get($api . '/recipes', [RecipeController::class, 'getAll'], [WebHatcheryJwtMiddleware::class]);
    $router->get($api . '/recipes/{id}', [RecipeController::class, 'get'], [WebHatcheryJwtMiddleware::class]);
    $router->post($api . '/recipes', [RecipeController::class, 'create'], [WebHatcheryJwtMiddleware::class]);
    $router->put($api . '/recipes/{id}', [RecipeController::class, 'update'], [WebHatcheryJwtMiddleware::class]);
    $router->delete($api . '/recipes/{id}', [RecipeController::class, 'delete'], [WebHatcheryJwtMiddleware::class]);

    // Customers (auth required)
    $router->get($api . '/customers', [CustomerController::class, 'getAll'], [WebHatcheryJwtMiddleware::class]);
    $router->get($api . '/customers/{id}', [CustomerController::class, 'get'], [WebHatcheryJwtMiddleware::class]);
    $router->post($api . '/customers', [CustomerController::class, 'create'], [WebHatcheryJwtMiddleware::class]);
    $router->put($api . '/customers/{id}', [CustomerController::class, 'update'], [WebHatcheryJwtMiddleware::class]);
    $router->delete($api . '/customers/{id}', [CustomerController::class, 'delete'], [WebHatcheryJwtMiddleware::class]);
    $router->get($api . '/customers/current/{user_id}', [CustomerController::class, 'getCurrentCustomer'], [WebHatcheryJwtMiddleware::class]);
    $router->post($api . '/customers/generate', [CustomerController::class, 'generateCustomer'], [WebHatcheryJwtMiddleware::class]);
    $router->post($api . '/customers/sell', [CustomerController::class, 'sellItem'], [WebHatcheryJwtMiddleware::class]);
    $router->get($api . '/customers/price/{user_id}/{item_id}/{customer_id}', [CustomerController::class, 'getSellingPrice'], [WebHatcheryJwtMiddleware::class]);
    $router->post($api . '/customers/dismiss', [CustomerController::class, 'dismissCustomer'], [WebHatcheryJwtMiddleware::class]);

    // Inventory
    $router->get($api . '/inventory/{user_id}', [InventoryController::class, 'get'], [WebHatcheryJwtMiddleware::class]);
    $router->post($api . '/inventory/{user_id}/add', [InventoryController::class, 'add'], [WebHatcheryJwtMiddleware::class]);
    $router->post($api . '/inventory/{user_id}/remove', [InventoryController::class, 'remove'], [WebHatcheryJwtMiddleware::class]);

    // Crafting
    $router->post($api . '/crafting/start', [CraftingController::class, 'startCrafting'], [WebHatcheryJwtMiddleware::class]);
    $router->post($api . '/crafting/hammer-hit', [CraftingController::class, 'processHammerHit'], [WebHatcheryJwtMiddleware::class]);
    $router->post($api . '/crafting/complete', [CraftingController::class, 'completeCrafting'], [WebHatcheryJwtMiddleware::class]);
    $router->get($api . '/crafting/validate/{user_id}/{recipe_id}', [CraftingController::class, 'validateCrafting'], [WebHatcheryJwtMiddleware::class]);
    $router->get($api . '/crafting/session/{user_id}/{crafting_session_id}', [CraftingController::class, 'getCraftingSession'], [WebHatcheryJwtMiddleware::class]);
    $router->post($api . '/crafting/craft', [CraftingController::class, 'craft'], [WebHatcheryJwtMiddleware::class]);
    $router->get($api . '/crafting/history/{user_id}', [CraftingController::class, 'history'], [WebHatcheryJwtMiddleware::class]);

    // Upgrades (auth required)
    $router->get($api . '/upgrades', [UpgradeController::class, 'getAll'], [WebHatcheryJwtMiddleware::class]);
    $router->get($api . '/upgrades/purchased', [UpgradeController::class, 'getPurchased'], [WebHatcheryJwtMiddleware::class]);
    $router->post($api . '/upgrades/purchase', [UpgradeController::class, 'purchase'], [WebHatcheryJwtMiddleware::class]);

    // Mini-Games
    $router->post($api . '/minigames/play', [MiniGameController::class, 'play'], [WebHatcheryJwtMiddleware::class]);
    $router->get($api . '/minigames/history/{user_id}', [MiniGameController::class, 'history'], [WebHatcheryJwtMiddleware::class]);

    // Auth session (auth required)
    $router->get($api . '/auth/session', [AuthController::class, 'session'], [WebHatcheryJwtMiddleware::class]);

    // Health and ping
    $router->get($api . '/health', $healthHandler);
    $router->get('/health', $healthHandler);
    $router->get('/ping', function ($request, $response) {
        $response->getBody()->write(json_encode(['pong' => time()]));
        return $response->withHeader('Content-Type', 'application/json');
    });
};

<?php

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use App\Controllers\MaterialController;
use App\Controllers\RecipeController;
use App\Controllers\CustomerController;
use App\Controllers\InventoryController;
use App\Controllers\CraftingController;
use App\Controllers\UpgradeController;
use App\Controllers\MiniGameController;
use App\Controllers\AuthController;

return function (App $app) {
    // API version prefix
    $app->group('/api/v1', function (RouteCollectorProxy $group) {
        
        // Materials
        $group->group('/materials', function (RouteCollectorProxy $group) {
            $group->get('', [MaterialController::class, 'getAll']);
            $group->get('/{id:[0-9]+}', [MaterialController::class, 'get']);
            $group->get('/type/{type}', [MaterialController::class, 'getByType']);
            $group->get('/rarity/{rarity}', [MaterialController::class, 'getByRarity']);
            $group->post('', [MaterialController::class, 'create']);
            $group->put('/{id:[0-9]+}', [MaterialController::class, 'update']);
            $group->delete('/{id:[0-9]+}', [MaterialController::class, 'delete']);
            
            // New user material endpoints
            $group->get('/user/{userId:[0-9]+}', [MaterialController::class, 'getUserMaterials']);
            $group->post('/purchase', [MaterialController::class, 'purchaseMaterial']);
        });

        // Recipes
        $group->group('/recipes', function (RouteCollectorProxy $group) {
            $group->get('', [RecipeController::class, 'getAll']);
            $group->get('/{id:[0-9]+}', [RecipeController::class, 'get']);
            $group->post('', [RecipeController::class, 'create']);
            $group->put('/{id:[0-9]+}', [RecipeController::class, 'update']);
            $group->delete('/{id:[0-9]+}', [RecipeController::class, 'delete']);
        });

        // Customers
        $group->group('/customers', function (RouteCollectorProxy $group) {
            $group->get('', [CustomerController::class, 'getAll']);
            $group->get('/{id:[0-9]+}', [CustomerController::class, 'get']);
            $group->post('', [CustomerController::class, 'create']);
            $group->put('/{id:[0-9]+}', [CustomerController::class, 'update']);
            $group->delete('/{id:[0-9]+}', [CustomerController::class, 'delete']);
            
            // New customer interaction endpoints
            $group->get('/current/{user_id:[0-9]+}', [CustomerController::class, 'getCurrentCustomer']);
            $group->post('/generate', [CustomerController::class, 'generateCustomer']);
            $group->post('/sell', [CustomerController::class, 'sellItem']);
            $group->get('/price/{user_id:[0-9]+}/{item_id:[0-9]+}/{customer_id:[0-9]+}', [CustomerController::class, 'getSellingPrice']);
            $group->post('/dismiss', [CustomerController::class, 'dismissCustomer']);
        });

        // Inventory
        $group->group('/inventory', function (RouteCollectorProxy $group) {
            $group->get('/{user_id:[0-9]+}', [InventoryController::class, 'get']);
            $group->post('/{user_id:[0-9]+}/add', [InventoryController::class, 'add']);
            $group->post('/{user_id:[0-9]+}/remove', [InventoryController::class, 'remove']);
        });

        // Crafting
        $group->group('/crafting', function (RouteCollectorProxy $group) {
            // New crafting endpoints
            $group->post('/start', [CraftingController::class, 'startCrafting']);
            $group->post('/hammer-hit', [CraftingController::class, 'processHammerHit']);
            $group->post('/complete', [CraftingController::class, 'completeCrafting']);
            $group->get('/validate/{user_id:[0-9]+}/{recipe_id:[0-9]+}', [CraftingController::class, 'validateCrafting']);
            $group->get('/session/{user_id:[0-9]+}/{crafting_session_id:[0-9]+}', [CraftingController::class, 'getCraftingSession']);
            
            // Legacy endpoints
            $group->post('/craft', [CraftingController::class, 'craft']);
            $group->get('/history/{user_id:[0-9]+}', [CraftingController::class, 'history']);
        });

        // Upgrades
        $group->group('/upgrades', function (RouteCollectorProxy $group) {
            $group->get('', [UpgradeController::class, 'getAll']);
            $group->post('/purchase', [UpgradeController::class, 'purchase']);
        });

        // Mini-Games
        $group->group('/minigames', function (RouteCollectorProxy $group) {
            $group->post('/play', [MiniGameController::class, 'play']);
            $group->get('/history/{user_id:[0-9]+}', [MiniGameController::class, 'history']);
        });

        // Authentication
        $group->group('/auth', function (RouteCollectorProxy $group) {
            $group->post('/register', [AuthController::class, 'register']);
            $group->post('/login', [AuthController::class, 'login']);
            $group->post('/logout', [AuthController::class, 'logout']);
        });
    });

    // Health check endpoint
    $app->get('/health', function ($request, $response, $args) {
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

        // Database connectivity check
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

        // Environment variables check
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

        // PHP extensions check
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

        // Calculate response time
        $health['response_time_ms'] = round((microtime(true) - $startTime) * 1000, 2);

        // Set appropriate HTTP status code
        $statusCode = 200;
        if ($health['status'] === 'degraded') {
            $statusCode = 200; // Still operational but with issues
        } elseif ($health['status'] === 'down') {
            $statusCode = 503; // Service unavailable
        }

        $response->getBody()->write(json_encode($health, JSON_PRETTY_PRINT));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    });

    // Simple ping endpoint for quick checks
    $app->get('/ping', function ($request, $response, $args) {
        $response->getBody()->write(json_encode(['pong' => time()]));
        return $response->withHeader('Content-Type', 'application/json');
    });
};

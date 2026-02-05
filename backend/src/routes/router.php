<?php

use App\Core\Router;
use App\Controllers\MaterialController;
use App\Controllers\RecipeController;
use App\Controllers\CustomerController;
use App\Controllers\InventoryController;
use App\Controllers\CraftingController;
use App\Controllers\UpgradeController;
use App\Controllers\MiniGameController;
use App\Middleware\WebHatcheryJwtMiddleware;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

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

    // Materials
    $router->get($api . '/materials', [MaterialController::class, 'getAll']);
    $router->get($api . '/materials/{id}', [MaterialController::class, 'get']);
    $router->get($api . '/materials/type/{type}', [MaterialController::class, 'getByType']);
    $router->get($api . '/materials/rarity/{rarity}', [MaterialController::class, 'getByRarity']);
    $router->post($api . '/materials', [MaterialController::class, 'create']);
    $router->put($api . '/materials/{id}', [MaterialController::class, 'update']);
    $router->delete($api . '/materials/{id}', [MaterialController::class, 'delete']);
    $router->get($api . '/materials/user/{userId}', [MaterialController::class, 'getUserMaterials'], [WebHatcheryJwtMiddleware::class]);
    $router->post($api . '/materials/purchase', [MaterialController::class, 'purchaseMaterial'], [WebHatcheryJwtMiddleware::class]);

    // Recipes
    $router->get($api . '/recipes', [RecipeController::class, 'getAll']);
    $router->get($api . '/recipes/{id}', [RecipeController::class, 'get']);
    $router->post($api . '/recipes', [RecipeController::class, 'create']);
    $router->put($api . '/recipes/{id}', [RecipeController::class, 'update']);
    $router->delete($api . '/recipes/{id}', [RecipeController::class, 'delete']);

    // Customers
    $router->get($api . '/customers', [CustomerController::class, 'getAll']);
    $router->get($api . '/customers/{id}', [CustomerController::class, 'get']);
    $router->post($api . '/customers', [CustomerController::class, 'create']);
    $router->put($api . '/customers/{id}', [CustomerController::class, 'update']);
    $router->delete($api . '/customers/{id}', [CustomerController::class, 'delete']);
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

    // Upgrades
    $router->get($api . '/upgrades', [UpgradeController::class, 'getAll']);
    $router->post($api . '/upgrades/purchase', [UpgradeController::class, 'purchase'], [WebHatcheryJwtMiddleware::class]);

    // Mini-Games
    $router->post($api . '/minigames/play', [MiniGameController::class, 'play'], [WebHatcheryJwtMiddleware::class]);
    $router->get($api . '/minigames/history/{user_id}', [MiniGameController::class, 'history'], [WebHatcheryJwtMiddleware::class]);

    // Auth session
    $router->get($api . '/auth/session', function ($request, $response) {
        $authHeader = $request->getHeaderLine('Authorization');
        $token = null;
        if ($authHeader && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $token = $matches[1];
        }

        $secret = $_ENV['JWT_SECRET'] ?? '';
        if (!$token || !$secret) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Unauthorized'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        try {
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));
            $userId = $decoded->user_id ?? $decoded->sub ?? null;
            $email = $decoded->email ?? '';
            $role = $decoded->role ?? null;
            $username = $decoded->username ?? ($email !== '' ? explode('@', $email)[0] : 'user');

            $db = \App\External\DatabaseService::getInstance();
            $profileRepo = new \App\Repositories\BlacksmithProfileRepository($db->getPdo());
            $profileModel = $profileRepo->findByUserId((int) $userId);
            if (!$profileModel) {
                $profileModel = $profileRepo->createDefaultProfile((int) $userId, ucfirst($username) . ' Forge');
            }

            $profile = [
                'forge_name' => $profileModel->forge_name,
                'level' => $profileModel->level,
                'reputation' => $profileModel->reputation,
                'coins' => $profileModel->coins,
            ];

            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => (int) $userId,
                        'email' => $email,
                        'username' => $username,
                        'role' => $role,
                    ],
                    'profile' => $profile,
                ],
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Invalid token'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }
    });

    // Health and ping
    $router->get($api . '/health', $healthHandler);
    $router->get('/health', $healthHandler);
    $router->get('/ping', function ($request, $response) {
        $response->getBody()->write(json_encode(['pong' => time()]));
        return $response->withHeader('Content-Type', 'application/json');
    });
};

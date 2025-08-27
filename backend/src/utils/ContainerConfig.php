<?php

namespace App\Utils;

use DI\Container;
use DI\ContainerBuilder;
use App\External\DatabaseService;
use App\Repositories\MaterialRepository;
use App\Repositories\RecipeRepository;
use App\Repositories\CustomerRepository;
use App\Repositories\InventoryRepository;
use App\Repositories\AuthRepository;
use App\Repositories\CraftingRepository;
use App\Repositories\UpgradeRepository;
use App\Repositories\MiniGameRepository;
use App\Services\MaterialService;
use App\Services\RecipeService;
use App\Services\CustomerService;
use App\Services\InventoryService;
use App\Services\AuthService;
use App\Services\CraftingService;
use App\Services\UpgradeService;
use App\Services\MiniGameService;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;

class ContainerConfig
{
    public static function createContainer(): Container
    {
        $containerBuilder = new ContainerBuilder();
        
        $containerBuilder->addDefinitions([
            // Logger
            LoggerInterface::class => function () {
                $logger = new Logger('blacksmith-forge');
                $logger->pushHandler(new StreamHandler(__DIR__ . '/../../logs/app.log', Logger::DEBUG));
                return $logger;
            },
            
            // Database
            DatabaseService::class => function () {
                return DatabaseService::getInstance();
            },
            
            \PDO::class => function (DatabaseService $db) {
                return $db->getPdo();
            },
            
            // Repositories
            MaterialRepository::class => function (\PDO $pdo) {
                return new MaterialRepository($pdo);
            },
            
            RecipeRepository::class => function (\PDO $pdo) {
                return new RecipeRepository($pdo);
            },
            
            CustomerRepository::class => function (\PDO $pdo) {
                return new CustomerRepository($pdo);
            },
            
            InventoryRepository::class => function (\PDO $pdo) {
                return new InventoryRepository($pdo);
            },
            
            AuthRepository::class => function (\PDO $pdo) {
                return new AuthRepository($pdo);
            },
            
            CraftingRepository::class => function (\PDO $pdo) {
                return new CraftingRepository($pdo);
            },
            
            UpgradeRepository::class => function (\PDO $pdo) {
                return new UpgradeRepository($pdo);
            },
            
            MiniGameRepository::class => function (\PDO $pdo) {
                return new MiniGameRepository($pdo);
            },
            
            // Services
            MaterialService::class => function (MaterialRepository $repo, LoggerInterface $logger) {
                return new MaterialService($repo, $logger);
            },
            
            RecipeService::class => function (RecipeRepository $repo, MaterialRepository $materialRepo, LoggerInterface $logger) {
                return new RecipeService($repo, $materialRepo, $logger);
            },
            
            CustomerService::class => function (CustomerRepository $repo, LoggerInterface $logger) {
                return new CustomerService($repo, $logger);
            },
            
            InventoryService::class => function (InventoryRepository $repo, MaterialRepository $materialRepo, LoggerInterface $logger) {
                return new InventoryService($repo, $materialRepo, $logger);
            },
            
            AuthService::class => function (AuthRepository $repo, LoggerInterface $logger) {
                return new AuthService($repo, $logger);
            },
            
            CraftingService::class => function (
                CraftingRepository $craftingRepo,
                RecipeRepository $recipeRepo,
                InventoryRepository $inventoryRepo,
                MaterialRepository $materialRepo,
                LoggerInterface $logger
            ) {
                return new CraftingService($craftingRepo, $recipeRepo, $inventoryRepo, $materialRepo, $logger);
            },
            
            UpgradeService::class => function (UpgradeRepository $repo, LoggerInterface $logger) {
                return new UpgradeService($repo, $logger);
            },
            
            MiniGameService::class => function (MiniGameRepository $repo, LoggerInterface $logger) {
                return new MiniGameService($repo, $logger);
            },
        ]);
        
        return $containerBuilder->build();
    }
}

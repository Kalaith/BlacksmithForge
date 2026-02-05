<?php

namespace App\Services;

use App\Models\Material;
use App\Repositories\MaterialRepository;
use App\Repositories\InventoryRepository;
use App\Repositories\BlacksmithProfileRepository;
use Psr\Log\LoggerInterface;

class MaterialService
{
    private MaterialRepository $repository;
    private InventoryRepository $inventoryRepository;
    private BlacksmithProfileRepository $profileRepository;
    private LoggerInterface $logger;

    public function __construct(
        MaterialRepository $repository,
        InventoryRepository $inventoryRepository,
        BlacksmithProfileRepository $profileRepository,
        LoggerInterface $logger
    )
    {
        $this->repository = $repository;
        $this->inventoryRepository = $inventoryRepository;
        $this->profileRepository = $profileRepository;
        $this->logger = $logger;
    }

    /**
     * Get all materials
     */
    public function getAllMaterials(): array
    {
        try {
            return $this->repository->findAllMaterials();
        } catch (\Exception $e) {
            $this->logger->error('Failed to get all materials: ' . $e->getMessage());
            throw new \RuntimeException('Failed to retrieve materials');
        }
    }

    /**
     * Get material by ID
     */
    public function getMaterialById(int $id): ?Material
    {
        try {
            return $this->repository->findMaterial($id);
        } catch (\Exception $e) {
            $this->logger->error("Failed to get material {$id}: " . $e->getMessage());
            throw new \RuntimeException('Failed to retrieve material');
        }
    }

    /**
     * Create a new material
     */
    public function createMaterial(array $data): Material
    {
        $material = new Material($data);
        
        $errors = $material->validate();
        if (!empty($errors)) {
            throw new \InvalidArgumentException('Validation failed: ' . implode(', ', $errors));
        }

        try {
            $id = $this->repository->createMaterial($material);
            $material->id = $id;
            
            $this->logger->info("Created material with ID {$id}");
            return $material;
        } catch (\Exception $e) {
            $this->logger->error('Failed to create material: ' . $e->getMessage());
            throw new \RuntimeException('Failed to create material');
        }
    }

    /**
     * Update an existing material
     */
    public function updateMaterial(int $id, array $data): ?Material
    {
        $existingMaterial = $this->getMaterialById($id);
        if (!$existingMaterial) {
            return null;
        }

        $material = new Material(array_merge($existingMaterial->toArray(), $data));
        
        $errors = $material->validate();
        if (!empty($errors)) {
            throw new \InvalidArgumentException('Validation failed: ' . implode(', ', $errors));
        }

        try {
            $success = $this->repository->updateMaterial($id, $material);
            if ($success) {
                $material->id = $id;
                $this->logger->info("Updated material with ID {$id}");
                return $material;
            }
            return null;
        } catch (\Exception $e) {
            $this->logger->error("Failed to update material {$id}: " . $e->getMessage());
            throw new \RuntimeException('Failed to update material');
        }
    }

    /**
     * Delete a material
     */
    public function deleteMaterial(int $id): bool
    {
        try {
            $material = $this->getMaterialById($id);
            if (!$material) {
                return false;
            }

            $success = $this->repository->delete($id);
            if ($success) {
                $this->logger->info("Deleted material with ID {$id}");
            }
            return $success;
        } catch (\Exception $e) {
            $this->logger->error("Failed to delete material {$id}: " . $e->getMessage());
            throw new \RuntimeException('Failed to delete material');
        }
    }

    /**
     * Get materials by type
     */
    public function getMaterialsByType(string $type): array
    {
        try {
            return $this->repository->findByType($type);
        } catch (\Exception $e) {
            $this->logger->error("Failed to get materials by type {$type}: " . $e->getMessage());
            throw new \RuntimeException('Failed to retrieve materials by type');
        }
    }

    /**
     * Get materials by rarity
     */
    public function getMaterialsByRarity(string $rarity): array
    {
        try {
            return $this->repository->findByRarity($rarity);
        } catch (\Exception $e) {
            $this->logger->error("Failed to get materials by rarity {$rarity}: " . $e->getMessage());
            throw new \RuntimeException('Failed to retrieve materials by rarity');
        }
    }

    /**
     * Update material quantity
     */
    public function updateMaterialQuantity(int $id, int $quantity): bool
    {
        if ($quantity < 0) {
            throw new \InvalidArgumentException('Quantity cannot be negative');
        }

        try {
            $material = $this->getMaterialById($id);
            if (!$material) {
                return false;
            }

            $success = $this->repository->updateQuantity($id, $quantity);
            if ($success) {
                $this->logger->info("Updated quantity for material {$id} to {$quantity}");
            }
            return $success;
        } catch (\Exception $e) {
            $this->logger->error("Failed to update material quantity {$id}: " . $e->getMessage());
            throw new \RuntimeException('Failed to update material quantity');
        }
    }

    /**
     * Get user's material quantities
     */
    public function getUserMaterials(int $userId): array
    {
        try {
            return $this->inventoryRepository->getUserMaterials($userId);
        } catch (\Exception $e) {
            $this->logger->error("Failed to get user materials for user {$userId}: " . $e->getMessage());
            throw new \RuntimeException('Failed to retrieve user materials');
        }
    }

    /**
     * Purchase materials for user
     */
    public function purchaseMaterial(int $userId, int $materialId, int $quantity): array
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be positive');
        }

        try {
            // Get material details
            $material = $this->getMaterialById($materialId);
            if (!$material) {
                return [
                    'success' => false,
                    'message' => 'Material not found'
                ];
            }

            // Calculate total cost
            $unitCost = $material->properties['cost'] ?? 10;
            $totalCost = $unitCost * $quantity;

            // Get user's current gold (you'll need to implement this in AuthRepository/UserRepository)
            $profile = $this->profileRepository->findByUserId($userId);
            if (!$profile) {
                $profile = $this->profileRepository->createDefaultProfile($userId, 'New Forge');
            }
            $userGold = $profile->coins ?? 0;
            if ($userGold < $totalCost) {
                return [
                    'success' => false,
                    'message' => 'Insufficient gold',
                    'required' => $totalCost,
                    'available' => $userGold
                ];
            }

            // Process the purchase (atomic transaction)
            $result = $this->inventoryRepository->addMaterialToUser($userId, $material->name, $quantity);
            
            if ($result) {
                $this->profileRepository->updateByUserId($userId, [
                    'coins' => $userGold - $totalCost
                ]);
                $this->logger->info("User {$userId} purchased {$quantity} of material {$materialId} for {$totalCost} gold");
                
                return [
                    'success' => true,
                    'quantity_purchased' => $quantity,
                    'cost' => $totalCost,
                    'new_gold' => $userGold - $totalCost,
                    'material' => $material->toArray()
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to complete purchase'
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to purchase material for user {$userId}: " . $e->getMessage());
            throw new \RuntimeException('Failed to process material purchase');
        }
    }

    /**
     * Add materials to user inventory (for crafting rewards, etc.)
     */
    public function addMaterialToUser(int $userId, int $materialId, int $quantity): bool
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be positive');
        }

        try {
            $material = $this->getMaterialById($materialId);
            if (!$material) {
                throw new \RuntimeException('Material not found');
            }
            $success = $this->inventoryRepository->addMaterialToUser($userId, $material->name, $quantity);
            
            if ($success) {
                $this->logger->info("Added {$quantity} of material {$materialId} to user {$userId}");
            }
            
            return $success;
        } catch (\Exception $e) {
            $this->logger->error("Failed to add material to user {$userId}: " . $e->getMessage());
            throw new \RuntimeException('Failed to add material to user');
        }
    }

    /**
     * Consume materials from user inventory (for crafting)
     */
    public function consumeMaterials(int $userId, array $materials): bool
    {
        try {
            // Validate user has enough materials
            $userMaterials = $this->getUserMaterials($userId);
            
            foreach ($materials as $materialName => $requiredQuantity) {
                $available = $userMaterials[$materialName] ?? 0;
                if ($available < $requiredQuantity) {
                    throw new \InvalidArgumentException("Insufficient {$materialName}: need {$requiredQuantity}, have {$available}");
                }
            }

            // Consume the materials
            $success = $this->inventoryRepository->consumeMaterials($userId, $materials);
            
            if ($success) {
                $this->logger->info("Consumed materials for user {$userId}: " . json_encode($materials));
            }
            
            return $success;
        } catch (\Exception $e) {
            $this->logger->error("Failed to consume materials for user {$userId}: " . $e->getMessage());
            throw new \RuntimeException('Failed to consume materials');
        }
    }
}

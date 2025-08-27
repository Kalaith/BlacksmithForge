<?php

namespace App\Services;

use App\Repositories\CustomerRepository;
use App\Repositories\InventoryRepository;
use App\Repositories\AuthRepository;
use Psr\Log\LoggerInterface;

class CustomerService
{
    private CustomerRepository $customerRepository;
    private InventoryRepository $inventoryRepository;
    private AuthRepository $authRepository;
    private LoggerInterface $logger;

    // Customer preference modifiers
    private const PREFERENCE_MODIFIERS = [
        'quality' => [
            'Excellent' => 1.2,
            'Good' => 1.1,
            'Fair' => 1.0,
            'Poor' => 0.9
        ],
        'value' => [
            'threshold' => 0.7, // Items <= 70% of budget get bonus
            'modifier' => 1.1
        ],
        'durability' => [
            'weapon' => 1.15,
            'armor' => 1.25,
            'tool' => 1.1
        ]
    ];

    // Customer types with different behaviors
    private const CUSTOMER_TYPES = [
        [
            'name' => 'Village Guard',
            'budget_range' => [40, 80],
            'preferences' => 'durability',
            'icon' => '🛡️',
            'description' => 'Seeks sturdy, reliable equipment'
        ],
        [
            'name' => 'Traveling Merchant',
            'budget_range' => [20, 50],
            'preferences' => 'value',
            'icon' => '🎒',
            'description' => 'Looks for good deals and cost-effective items'
        ],
        [
            'name' => 'Noble Knight',
            'budget_range' => [80, 150],
            'preferences' => 'quality',
            'icon' => '👑',
            'description' => 'Demands the finest craftsmanship'
        ],
        [
            'name' => 'Apprentice Warrior',
            'budget_range' => [15, 35],
            'preferences' => 'value',
            'icon' => '⚔️',
            'description' => 'New warrior seeking affordable gear'
        ],
        [
            'name' => 'Master Blacksmith',
            'budget_range' => [60, 120],
            'preferences' => 'quality',
            'icon' => '🔨',
            'description' => 'Appreciates exceptional workmanship'
        ]
    ];

    public function __construct(
        CustomerRepository $customerRepository,
        InventoryRepository $inventoryRepository,
        AuthRepository $authRepository,
        LoggerInterface $logger
    ) {
        $this->customerRepository = $customerRepository;
        $this->inventoryRepository = $inventoryRepository;
        $this->authRepository = $authRepository;
        $this->logger = $logger;
    }

    /**
     * Get all available customer types
     */
    public function getAllCustomers(): array
    {
        try {
            return self::CUSTOMER_TYPES;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get all customers: ' . $e->getMessage());
            throw new \RuntimeException('Failed to retrieve customers');
        }
    }

    /**
     * Get customer by ID
     */
    public function getCustomerById(int $customerId): ?array
    {
        try {
            if (isset(self::CUSTOMER_TYPES[$customerId])) {
                return self::CUSTOMER_TYPES[$customerId];
            }
            return null;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get customer by ID: ' . $e->getMessage());
            throw new \RuntimeException('Failed to retrieve customer');
        }
    }

    /**
     * Get current customer for a user
     */
    public function getCurrentCustomerForUser(int $userId): ?array
    {
        try {
            return $this->customerRepository->getCurrentCustomerForUser($userId);
        } catch (\Exception $e) {
            $this->logger->error("Failed to get current customer for user {$userId}: " . $e->getMessage());
            throw new \RuntimeException('Failed to retrieve current customer');
        }
    }

    /**
     * Generate a new customer for a user
     */
    public function generateCustomerForUser(int $userId): array
    {
        try {
            // Check if user already has a current customer
            $currentCustomer = $this->getCurrentCustomerForUser($userId);
            if ($currentCustomer) {
                throw new \RuntimeException('User already has a current customer');
            }

            // Randomly select a customer type
            $customerType = self::CUSTOMER_TYPES[array_rand(self::CUSTOMER_TYPES)];
            
            // Generate random budget within range
            $budget = rand($customerType['budget_range'][0], $customerType['budget_range'][1]);
            
            // Create customer instance
            $customerData = [
                'user_id' => $userId,
                'name' => $customerType['name'],
                'budget' => $budget,
                'preferences' => $customerType['preferences'],
                'icon' => $customerType['icon'],
                'description' => $customerType['description'],
                'created_at' => date('Y-m-d H:i:s'),
                'status' => 'waiting'
            ];

            $customerId = $this->customerRepository->createCustomerForUser($customerData);
            $customerData['id'] = $customerId;

            $this->logger->info("Generated new customer for user {$userId}", [
                'customer_id' => $customerId,
                'customer_name' => $customerType['name'],
                'budget' => $budget
            ]);

            return $customerData;

        } catch (\Exception $e) {
            $this->logger->error("Failed to generate customer for user {$userId}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Calculate selling price with customer preferences
     */
    public function calculateSellingPrice(int $userId, int $itemId, int $customerId): array
    {
        try {
            $customer = $this->customerRepository->getCustomerById($customerId);
            if (!$customer || $customer['user_id'] !== $userId) {
                throw new \RuntimeException('Invalid customer');
            }

            $item = $this->inventoryRepository->getItemById($itemId);
            if (!$item || $item['user_id'] !== $userId) {
                throw new \RuntimeException('Invalid item');
            }

            $basePrice = $item['value'] ?? 0;
            $finalPrice = $basePrice;
            $modifier = 1.0;
            $reason = 'Base price';

            // Apply customer preference modifiers
            $preferences = $customer['preferences'];
            
            if ($preferences === 'quality' && isset($item['quality'])) {
                $qualityModifier = self::PREFERENCE_MODIFIERS['quality'][$item['quality']] ?? 1.0;
                if ($qualityModifier > 1.0) {
                    $modifier = $qualityModifier;
                    $reason = "Quality bonus ({$item['quality']})";
                }
            } elseif ($preferences === 'value') {
                $threshold = $customer['budget'] * self::PREFERENCE_MODIFIERS['value']['threshold'];
                if ($basePrice <= $threshold) {
                    $modifier = self::PREFERENCE_MODIFIERS['value']['modifier'];
                    $reason = 'Value preference bonus';
                }
            } elseif ($preferences === 'durability' && isset($item['type'])) {
                $durabilityModifier = self::PREFERENCE_MODIFIERS['durability'][$item['type']] ?? 1.0;
                if ($durabilityModifier > 1.0) {
                    $modifier = $durabilityModifier;
                    $reason = "Durability bonus ({$item['type']})";
                }
            }

            $finalPrice = (int) floor($basePrice * $modifier);

            // Check if customer can afford it
            $canAfford = $finalPrice <= $customer['budget'];

            return [
                'base_price' => $basePrice,
                'final_price' => $finalPrice,
                'modifier' => $modifier,
                'reason' => $reason,
                'can_afford' => $canAfford,
                'customer_budget' => $customer['budget'],
                'item' => $item,
                'customer' => $customer
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to calculate selling price: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Sell item to customer
     */
    public function sellItemToCustomer(int $userId, int $itemId, int $customerId): array
    {
        try {
            // Get pricing information
            $priceInfo = $this->calculateSellingPrice($userId, $itemId, $customerId);
            
            if (!$priceInfo['can_afford']) {
                throw new \RuntimeException('Customer cannot afford this item');
            }

            // Remove item from inventory
            $this->inventoryRepository->removeItem($userId, $itemId);

            // Add gold to user
            $user = $this->authRepository->findById($userId);
            $newGold = ($user['gold'] ?? 0) + $priceInfo['final_price'];
            $newReputation = ($user['reputation'] ?? 0) + 1;
            
            $this->authRepository->updateUserStats($userId, [
                'gold' => $newGold,
                'reputation' => $newReputation
            ]);

            // Remove customer (they leave after purchase)
            $this->customerRepository->removeCustomerForUser($userId, $customerId);

            // Log the transaction
            $this->customerRepository->logTransaction([
                'user_id' => $userId,
                'customer_id' => $customerId,
                'item_id' => $itemId,
                'sale_price' => $priceInfo['final_price'],
                'base_price' => $priceInfo['base_price'],
                'modifier' => $priceInfo['modifier'],
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $this->logger->info("Item sold successfully", [
                'user_id' => $userId,
                'item_id' => $itemId,
                'customer_id' => $customerId,
                'sale_price' => $priceInfo['final_price']
            ]);

            return [
                'sale_price' => $priceInfo['final_price'],
                'base_price' => $priceInfo['base_price'],
                'modifier' => $priceInfo['modifier'],
                'reason' => $priceInfo['reason'],
                'new_gold' => $newGold,
                'new_reputation' => $newReputation,
                'item' => $priceInfo['item'],
                'customer' => $priceInfo['customer']
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to sell item: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Dismiss current customer
     */
    public function dismissCurrentCustomer(int $userId): void
    {
        try {
            $this->customerRepository->removeCurrentCustomerForUser($userId);
            
            $this->logger->info("Customer dismissed for user {$userId}");
            
        } catch (\Exception $e) {
            $this->logger->error("Failed to dismiss customer for user {$userId}: " . $e->getMessage());
            throw $e;
        }
    }
}

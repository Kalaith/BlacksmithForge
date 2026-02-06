<?php

namespace App\Services;

use App\Repositories\CustomerRepository;
use App\Repositories\InventoryRepository;
use App\Repositories\BlacksmithProfileRepository;
use Psr\Log\LoggerInterface;

class CustomerService
{
    private CustomerRepository $customerRepository;
    private InventoryRepository $inventoryRepository;
    private BlacksmithProfileRepository $profileRepository;
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

    public function __construct(
        CustomerRepository $customerRepository,
        InventoryRepository $inventoryRepository,
        BlacksmithProfileRepository $profileRepository,
        LoggerInterface $logger
    ) {
        $this->customerRepository = $customerRepository;
        $this->inventoryRepository = $inventoryRepository;
        $this->profileRepository = $profileRepository;
        $this->logger = $logger;
    }

    /**
     * Get all available customer types
     */
    public function getAllCustomers(): array
    {
        try {
            return $this->customerRepository->getAllCustomerTypes();
        } catch (\Exception $e) {
            $this->logger->error('Failed to get all customers: ' . $e->getMessage());
            throw new \RuntimeException('Failed to retrieve customers');
        }
    }

    /**
     * Get customer type by ID
     */
    public function getCustomerById(int $customerId): ?array
    {
        try {
            return $this->customerRepository->getCustomerTypeById($customerId);
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

            $customerTypes = $this->customerRepository->getAllCustomerTypes();
            if (count($customerTypes) === 0) {
                throw new \RuntimeException('No customer types available');
            }

            // Randomly select a customer type
            $customerType = $customerTypes[array_rand($customerTypes)];
            
            // Generate random budget within range
            $budgetMin = (int) ($customerType['budget_min'] ?? 0);
            $budgetMax = (int) ($customerType['budget_max'] ?? 0);
            $budget = $budgetMax > $budgetMin ? rand($budgetMin, $budgetMax) : $budgetMin;
            
            // Create customer instance
            $customerData = [
                'user_id' => $userId,
                'type_id' => $customerType['id'],
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
            $customer = $this->getCustomerOrFail($userId, $customerId);
            $item = $this->getItemOrFail($userId, $itemId);

            $basePrice = $item['value'] ?? 0;
            [$modifier, $reason] = $this->getPreferenceModifier($customer, $item, $basePrice);
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

            // Add coins/reputation to profile
            $profile = $this->getProfileOrCreate($userId);
            $newGold = ($profile->coins ?? 0) + $priceInfo['final_price'];
            $newReputation = ($profile->reputation ?? 0) + 1;

            $this->profileRepository->updateByUserId($userId, [
                'coins' => $newGold,
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

    private function getCustomerOrFail(int $userId, int $customerId): array
    {
        $customer = $this->customerRepository->getCustomerById($customerId);
        if (!$customer || ($customer['user_id'] ?? null) !== $userId) {
            throw new \RuntimeException('Invalid customer');
        }
        return $customer;
    }

    private function getItemOrFail(int $userId, int $itemId): array
    {
        $item = $this->inventoryRepository->getItemById($userId, $itemId);
        if (!$item) {
            throw new \RuntimeException('Invalid item');
        }
        return $item;
    }

    private function getProfileOrCreate(int $userId)
    {
        $profile = $this->profileRepository->findByUserId($userId);
        if (!$profile) {
            $profile = $this->profileRepository->createDefaultProfile($userId, 'New Forge');
        }
        return $profile;
    }

    private function getPreferenceModifier(array $customer, array $item, int $basePrice): array
    {
        $modifier = 1.0;
        $reason = 'Base price';
        $preferences = $customer['preferences'] ?? '';

        if ($preferences === 'quality' && isset($item['quality'])) {
            $qualityModifier = self::PREFERENCE_MODIFIERS['quality'][$item['quality']] ?? 1.0;
            if ($qualityModifier > 1.0) {
                $modifier = $qualityModifier;
                $reason = "Quality bonus ({$item['quality']})";
            }
        } elseif ($preferences === 'value') {
            $threshold = ($customer['budget'] ?? 0) * self::PREFERENCE_MODIFIERS['value']['threshold'];
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

        return [$modifier, $reason];
    }
}

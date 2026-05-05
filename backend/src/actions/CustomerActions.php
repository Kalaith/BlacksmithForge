<?php

declare(strict_types=1);

namespace App\Actions;

use App\Services\CustomerService;

class CustomerActions
{
    public function __construct(private readonly CustomerService $customerService)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function getAll(): array
    {
        try {
            $customers = $this->customerService->getAllCustomers();
            return [
                'success' => true,
                'data' => $customers,
                'count' => count($customers),
            ];
        } catch (\Exception $e) {
            return $this->failure($e);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function get(int $id): array
    {
        try {
            return [
                'success' => true,
                'data' => $this->customerService->getCustomerById($id),
            ];
        } catch (\Exception $e) {
            return $this->failure($e);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getCurrentCustomer(int $userId): array
    {
        try {
            return [
                'success' => true,
                'data' => $this->customerService->getCurrentCustomerForUser($userId),
            ];
        } catch (\Exception $e) {
            return $this->failure($e);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function generateCustomer(int $userId): array
    {
        try {
            return [
                'success' => true,
                'data' => $this->customerService->generateCustomerForUser($userId),
                'message' => 'New customer generated',
            ];
        } catch (\Exception $e) {
            return $this->failure($e);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function sellItem(int $userId, int $itemId, int $customerId): array
    {
        try {
            return [
                'success' => true,
                'data' => $this->customerService->sellItemToCustomer($userId, $itemId, $customerId),
                'message' => 'Item sold successfully',
            ];
        } catch (\Exception $e) {
            return $this->failure($e);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getSellingPrice(int $userId, int $itemId, int $customerId): array
    {
        try {
            return [
                'success' => true,
                'data' => $this->customerService->calculateSellingPrice($userId, $itemId, $customerId),
            ];
        } catch (\Exception $e) {
            return $this->failure($e);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function dismissCustomer(int $userId): array
    {
        try {
            $this->customerService->dismissCurrentCustomer($userId);
            return [
                'success' => true,
                'message' => 'Customer dismissed',
            ];
        } catch (\Exception $e) {
            return $this->failure($e);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function failure(\Exception $e): array
    {
        return [
            'success' => false,
            'message' => $e->getMessage(),
        ];
    }
}

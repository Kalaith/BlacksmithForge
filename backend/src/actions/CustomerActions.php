<?php
namespace App\Actions;

use App\Services\CustomerService;
use App\Services\InventoryService;

class CustomerActions {
    
    /**
     * Get all available customers
     */
    public static function getAll() {
        try {
            $customerService = new CustomerService();
            $customers = $customerService->getAllCustomers();
            
            return [
                'success' => true,
                'data' => $customers,
                'count' => count($customers)
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get specific customer by ID
     */
    public static function get($id) {
        try {
            $customerService = new CustomerService();
            $customer = $customerService->getCustomerById($id);
            
            return [
                'success' => true,
                'data' => $customer
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get current customer for a user (if any)
     */
    public static function getCurrentCustomer($userId) {
        try {
            $customerService = new CustomerService();
            $customer = $customerService->getCurrentCustomerForUser($userId);
            
            return [
                'success' => true,
                'data' => $customer
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Generate a new customer for a user
     */
    public static function generateCustomer($userId) {
        try {
            $customerService = new CustomerService();
            $customer = $customerService->generateCustomerForUser($userId);
            
            return [
                'success' => true,
                'data' => $customer,
                'message' => 'New customer generated'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Sell an item to a customer
     */
    public static function sellItem($userId, $itemId, $customerId) {
        try {
            $customerService = new CustomerService();
            $result = $customerService->sellItemToCustomer($userId, $itemId, $customerId);
            
            return [
                'success' => true,
                'data' => $result,
                'message' => 'Item sold successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get selling price for an item with customer preferences
     */
    public static function getSellingPrice($userId, $itemId, $customerId) {
        try {
            $customerService = new CustomerService();
            $priceInfo = $customerService->calculateSellingPrice($userId, $itemId, $customerId);
            
            return [
                'success' => true,
                'data' => $priceInfo
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Dismiss current customer
     */
    public static function dismissCustomer($userId) {
        try {
            $customerService = new CustomerService();
            $customerService->dismissCurrentCustomer($userId);
            
            return [
                'success' => true,
                'message' => 'Customer dismissed'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    // Legacy methods for backward compatibility
    public static function create($data) {
        $customer = new \App\Models\Customer(
            $data['name'] ?? '',
            $data['budget'] ?? 0,
            $data['preferences'] ?? '',
            $data['reputation'] ?? 0,
            $data['icon'] ?? ''
        );
        return ["created" => true, "customer" => $customer];
    }
    
    public static function update($id, $data) {
        $customer = new \App\Models\Customer(
            $data['name'] ?? '',
            $data['budget'] ?? 0,
            $data['preferences'] ?? '',
            $data['reputation'] ?? 0,
            $data['icon'] ?? ''
        );
        return ["updated" => true, "customer" => $customer];
    }
    
    public static function delete($id) {
        return ["deleted" => true];
    }
}

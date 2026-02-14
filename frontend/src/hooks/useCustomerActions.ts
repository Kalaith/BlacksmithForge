import { useState, useCallback, useEffect } from 'react';
import { customersAPI, inventoryAPI } from '../api/api';
import { useAuthContext } from '../providers/GameDataProvider';
import { SellingPriceInfo, SaleResult, CustomerState, InventoryItem } from '../types';

/**
 * Custom hook for customer interactions with backend business logic
 * Handles customer generation, selling, and price calculations on backend
 */
export function useCustomerActions() {
  const { user, isAuthenticated } = useAuthContext();

  const [customerState, setCustomerState] = useState<CustomerState>({
    currentCustomer: null,
    availableCustomers: [],
    loading: false,
    error: null,
  });

  const [inventory, setInventory] = useState<InventoryItem[]>([]);
  const [sellingPrice, setSellingPrice] = useState<SellingPriceInfo | null>(null);

  /**
   * Load current customer for the user
   */
  const loadCurrentCustomer = useCallback(async () => {
    if (!isAuthenticated || !user?.id) {
      setCustomerState(prev => ({
        ...prev,
        currentCustomer: null,
        loading: false,
      }));
      return;
    }

    setCustomerState(prev => ({ ...prev, loading: true, error: null }));

    try {
      const customer = await customersAPI.getCurrentCustomer(user.id);
      setCustomerState(prev => ({
        ...prev,
        currentCustomer: customer,
        loading: false,
      }));
    } catch (error) {
      setCustomerState(prev => ({
        ...prev,
        error: error instanceof Error ? error.message : 'Failed to load current customer',
        loading: false,
      }));
    }
  }, [isAuthenticated, user?.id]);

  /**
   * Load user inventory
   */
  const loadInventory = useCallback(async () => {
    if (!isAuthenticated || !user?.id) {
      setInventory([]);
      return;
    }

    try {
      const userInventory = await inventoryAPI.getUserInventory(user.id);
      setInventory(userInventory);
    } catch (error) {
      console.error('Failed to load inventory:', error);
    }
  }, [isAuthenticated, user?.id]);

  /**
   * Generate a new customer - all logic handled by backend
   */
  const generateCustomer = useCallback(async () => {
    if (!isAuthenticated) {
      setCustomerState(prev => ({
        ...prev,
        error: 'You must be logged in to generate customers',
      }));
      return;
    }

    setCustomerState(prev => ({ ...prev, loading: true, error: null }));

    try {
      const newCustomer = await customersAPI.generateCustomer();
      if (newCustomer) {
        setCustomerState(prev => ({
          ...prev,
          currentCustomer: newCustomer,
          loading: false,
        }));
      } else {
        throw new Error('Failed to generate customer');
      }
    } catch (error) {
      setCustomerState(prev => ({
        ...prev,
        error: error instanceof Error ? error.message : 'Failed to generate customer',
        loading: false,
      }));
    }
  }, [isAuthenticated]);

  /**
   * Get selling price for an item - backend calculates with customer preferences
   */
  const getSellingPrice = useCallback(
    async (itemId: number) => {
      if (!isAuthenticated || !user?.id) return null;
      if (!customerState.currentCustomer) return null;

      try {
        const priceInfo = await customersAPI.getSellingPrice(
          user.id,
          itemId,
          customerState.currentCustomer.id
        );
        setSellingPrice(priceInfo);
        return priceInfo;
      } catch (error) {
        console.error('Failed to get selling price:', error);
        return null;
      }
    },
    [isAuthenticated, user?.id, customerState.currentCustomer]
  );

  /**
   * Sell item to customer - all business logic on backend
   */
  const sellItem = useCallback(
    async (itemId: number): Promise<SaleResult | null> => {
      if (!isAuthenticated) {
        setCustomerState(prev => ({
          ...prev,
          error: 'You must be logged in to sell items',
        }));
        return null;
      }
      if (!customerState.currentCustomer) return null;

      setCustomerState(prev => ({ ...prev, loading: true, error: null }));

      try {
        const saleResult = await customersAPI.sellItem({
          itemId: itemId,
          customerId: customerState.currentCustomer.id,
        });

        if (saleResult) {
          // Clear current customer (they leave after purchase)
          setCustomerState(prev => ({
            ...prev,
            currentCustomer: null,
            loading: false,
          }));

          // Refresh inventory to show item removal
          await loadInventory();

          return saleResult;
        } else {
          throw new Error('Failed to complete sale');
        }
      } catch (error) {
        setCustomerState(prev => ({
          ...prev,
          error: error instanceof Error ? error.message : 'Failed to sell item',
          loading: false,
        }));
        return null;
      }
    },
    [isAuthenticated, customerState.currentCustomer, loadInventory]
  );

  /**
   * Dismiss current customer
   */
  const dismissCustomer = useCallback(async () => {
    if (!isAuthenticated) {
      setCustomerState(prev => ({
        ...prev,
        error: 'You must be logged in to dismiss customers',
      }));
      return;
    }

    setCustomerState(prev => ({ ...prev, loading: true, error: null }));

    try {
      const success = await customersAPI.dismissCustomer();
      if (success) {
        setCustomerState(prev => ({
          ...prev,
          currentCustomer: null,
          loading: false,
        }));
      } else {
        throw new Error('Failed to dismiss customer');
      }
    } catch (error) {
      setCustomerState(prev => ({
        ...prev,
        error: error instanceof Error ? error.message : 'Failed to dismiss customer',
        loading: false,
      }));
    }
  }, [isAuthenticated]);

  /**
   * Clear any errors
   */
  const clearError = useCallback(() => {
    setCustomerState(prev => ({ ...prev, error: null }));
  }, []);

  /**
   * Reset selling price info
   */
  const clearSellingPrice = useCallback(() => {
    setSellingPrice(null);
  }, []);

  // Load data automatically (always in dev mode)
  useEffect(() => {
    loadCurrentCustomer();
    loadInventory();
  }, [loadCurrentCustomer, loadInventory]);

  return {
    // Customer state
    currentCustomer: customerState.currentCustomer,
    availableCustomers: customerState.availableCustomers,
    loading: customerState.loading,
    error: customerState.error,

    // Inventory
    inventory,

    // Selling info
    sellingPrice,

    // Actions
    generateCustomer,
    sellItem,
    handleSell: sellItem,
    dismissCustomer,
    getSellingPrice,
    clearError,
    clearSellingPrice,
    refreshData: () => {
      loadCurrentCustomer();
      loadInventory();
    },

    // Helper computed values
    hasCustomer: customerState.currentCustomer !== null,
    canSell: () => {
      if (!sellingPrice || !customerState.currentCustomer) return false;
      return sellingPrice.can_afford;
    },
  };
}

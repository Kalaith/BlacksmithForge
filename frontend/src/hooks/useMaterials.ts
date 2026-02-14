import { useCallback, useState, useEffect } from 'react';
import { materialsAPI, inventoryAPI } from '../api/api';
import { useAuthContext } from '../providers/GameDataProvider';
import {
  InventoryItem,
  InventoryState,
  MaterialsState,
  UseInventoryReturn,
  InventoryFilter,
  InventorySort,
} from '../types';
import { inventoryUtils } from '../utils/inventoryUtils';

/**
 * Custom hook for materials management with backend integration
 * Fetches material quantities from backend API
 */
export function useMaterials() {
  const { user, isAuthenticated } = useAuthContext();

  const [state, setState] = useState<MaterialsState>({
    materials: {},
    loading: false,
    error: null,
  });

  /**
   * Fetch materials from backend
   */
  const fetchMaterials = useCallback(async () => {
    if (!isAuthenticated || !user?.id) {
      setState(prev => ({ ...prev, materials: {}, loading: false }));
      return;
    }

    setState(prev => ({ ...prev, loading: true }));

    try {
      const materials = await materialsAPI.getUserMaterials(user.id);
      setState({
        materials: materials || {},
        loading: false,
        error: null,
      });
    } catch (error) {
      setState(prev => ({
        ...prev,
        loading: false,
        error: error instanceof Error ? error.message : 'Failed to fetch materials',
      }));
    }
  }, [isAuthenticated, user?.id]);

  /**
   * Purchase materials - delegates to backend
   */
  const purchaseMaterial = useCallback(
    async (materialId: number, quantity: number) => {
      if (!isAuthenticated || !user?.id) {
        setState(prev => ({
          ...prev,
          error: 'You must be logged in to purchase materials',
        }));
        return false;
      }

      try {
        await materialsAPI.purchaseMaterial(materialId, quantity);
        await fetchMaterials(); // Refresh materials after purchase
        return true;
      } catch (error) {
        setState(prev => ({
          ...prev,
          error: error instanceof Error ? error.message : 'Failed to purchase material',
        }));
        return false;
      }
    },
    [isAuthenticated, user?.id, fetchMaterials]
  );

  /**
   * Clear any errors
   */
  const clearError = useCallback(() => {
    setState(prev => ({ ...prev, error: null }));
  }, []);

  // Load materials automatically (always in dev mode)
  useEffect(() => {
    fetchMaterials();
  }, [fetchMaterials]);

  return {
    materials: state.materials,
    loading: state.loading,
    error: state.error,
    purchaseMaterial,
    refreshMaterials: fetchMaterials,
    clearError,
  };
}

/**
 * Custom hook for inventory management with backend integration
 * Fetches inventory items from backend API with filtering and sorting
 */
export function useInventory(): UseInventoryReturn {
  const { user, isAuthenticated } = useAuthContext();

  const [state, setState] = useState<InventoryState>({
    items: [],
    materials: [],
    totalValue: 0,
    totalItems: 0,
    loading: false,
    error: null,
  });

  const [filter, setFilter] = useState<InventoryFilter>({});
  const [sort, setSort] = useState<InventorySort>({
    field: 'name',
    direction: 'asc',
  });

  /**
   * Fetch inventory from backend
   */
  const fetchInventory = useCallback(async () => {
    if (!isAuthenticated || !user?.id) {
      setState(prev => ({ ...prev, items: [], loading: false }));
      return;
    }

    setState(prev => ({ ...prev, loading: true }));

    try {
      const inventory = await inventoryAPI.getUserInventory(user.id);
      const totalValue = inventoryUtils.calculateTotalValue(inventory);

      setState({
        items: inventory || [],
        materials: [], // Materials are handled separately
        totalValue,
        totalItems: inventory?.length || 0,
        loading: false,
        error: null,
      });
    } catch (error) {
      setState(prev => ({
        ...prev,
        loading: false,
        error: error instanceof Error ? error.message : 'Failed to fetch inventory',
      }));
    }
  }, [isAuthenticated, user?.id]);

  /**
   * Remove item from inventory - delegates to backend
   */
  const removeItem = useCallback(
    async (item: InventoryItem) => {
      if (!isAuthenticated || !user?.id) {
        setState(prev => ({
          ...prev,
          error: 'You must be logged in to remove items',
        }));
        return false;
      }

      try {
        await inventoryAPI.removeItem(user.id, item);
        await fetchInventory(); // Refresh inventory after removal
        return true;
      } catch (error) {
        setState(prev => ({
          ...prev,
          error: error instanceof Error ? error.message : 'Failed to remove item',
        }));
        return false;
      }
    },
    [isAuthenticated, user?.id, fetchInventory]
  );

  /**
   * Apply filter to inventory items
   */
  const applyFilter = useCallback((newFilter: InventoryFilter) => {
    setFilter(newFilter);
  }, []);

  /**
   * Apply sorting to inventory items
   */
  const applySorting = useCallback((newSort: InventorySort) => {
    setSort(newSort);
  }, []);

  /**
   * Clear current filter
   */
  const clearFilter = useCallback(() => {
    setFilter({});
  }, []);

  /**
   * Clear any errors
   */
  const clearError = useCallback(() => {
    setState(prev => ({ ...prev, error: null }));
  }, []);

  // Compute filtered and sorted items
  const filteredItems = useCallback(() => {
    let items = state.items;

    // Apply filter if set
    if (Object.keys(filter).length > 0) {
      items = inventoryUtils.filterItems(items, filter);
    }

    // Apply sorting
    items = inventoryUtils.sortItems(items, sort);

    return items;
  }, [state.items, filter, sort]);

  // Load inventory automatically (always in dev mode)
  useEffect(() => {
    fetchInventory();
  }, [fetchInventory]);

  return {
    // State
    inventory: state.items,
    materials: state.materials,
    loading: state.loading,
    error: state.error,

    // Computed values
    totalValue: state.totalValue,
    totalItems: state.totalItems,
    filteredItems: filteredItems(),

    // Actions
    removeItem,
    refreshInventory: fetchInventory,
    clearError,

    // Filtering and sorting
    applyFilter,
    applySorting,
    clearFilter,
  };
}

import { useState, useEffect, useCallback } from 'react';
import api, { materialsAPI, recipesAPI, customersAPI, upgradesAPI } from '../api/api';
import { Material, Recipe, Customer, ForgeUpgrade } from '../types/game.d';
import { InventoryItem } from '../types/inventory';

type AuthUser = {
  id: number;
  username?: string;
  email?: string;
  [key: string]: unknown;
};

type AuthProfile = Record<string, unknown>;
type CraftingHistoryEntry = Record<string, unknown>;
type CraftingMaterialsPayload = Array<Record<string, unknown>>;

// Custom hook for loading game data
export function useGameData() {
  const [materials, setMaterials] = useState<Material[]>([]);
  const [recipes, setRecipes] = useState<Recipe[]>([]);
  const [customers, setCustomers] = useState<Customer[]>([]);
  const [upgrades, setUpgrades] = useState<ForgeUpgrade[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const loadData = useCallback(async () => {
    setLoading(true);
    setError(null);

    try {
      // Check backend health first
      const isHealthy = await api.health.check();
      if (!isHealthy) {
        throw new Error('Backend is not available');
      }

      // Load all game data in parallel
      const [materialsData, recipesData, customersData, upgradesData] = await Promise.all([
        materialsAPI.getAll(),
        recipesAPI.getAll(),
        customersAPI.getAll(),
        upgradesAPI.getAll(),
      ]);

      setMaterials(materialsData);
      setRecipes(recipesData);
      setCustomers(customersData);
      setUpgrades(upgradesData);
    } catch (err) {
      console.error('Failed to load game data:', err);
      setError(err instanceof Error ? err.message : 'Failed to load game data');

      // Fallback to hardcoded data if backend is unavailable
      await loadFallbackData();
    } finally {
      setLoading(false);
    }
  }, []);

  const loadFallbackData = async () => {
    // Import the original gameData as fallback
    try {
      const { MATERIALS, RECIPES, CUSTOMERS, forgeUpgrades } =
        await import('../constants/gameData');
      setMaterials(MATERIALS);
      setRecipes(RECIPES);
      setCustomers(CUSTOMERS);
      setUpgrades(forgeUpgrades);
      console.warn('Using fallback game data due to backend unavailability');
    } catch (fallbackError) {
      console.error('Failed to load fallback data:', fallbackError);
    }
  };

  useEffect(() => {
    loadData();
  }, [loadData]);

  return {
    materials,
    recipes,
    customers,
    upgrades,
    loading,
    error,
    reload: loadData,
  };
}

// Custom hook for materials management
export function useMaterials() {
  const [materials, setMaterials] = useState<Material[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const loadMaterials = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const data = await materialsAPI.getAll();
      setMaterials(data);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to load materials');
    } finally {
      setLoading(false);
    }
  }, []);

  const getMaterialsByType = useCallback(async (type: string) => {
    setLoading(true);
    setError(null);
    try {
      const data = await materialsAPI.getByType(type);
      setMaterials(data);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to load materials by type');
    } finally {
      setLoading(false);
    }
  }, []);

  const getMaterialsByRarity = useCallback(async (rarity: string) => {
    setLoading(true);
    setError(null);
    try {
      const data = await materialsAPI.getByRarity(rarity);
      setMaterials(data);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to load materials by rarity');
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    loadMaterials();
  }, [loadMaterials]);

  return {
    materials,
    loading,
    error,
    loadMaterials,
    getMaterialsByType,
    getMaterialsByRarity,
  };
}

// Custom hook for recipes management
export function useRecipes() {
  const [recipes, setRecipes] = useState<Recipe[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const loadRecipes = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const data = await recipesAPI.getAll();
      setRecipes(data);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to load recipes');
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    loadRecipes();
  }, [loadRecipes]);

  return {
    recipes,
    loading,
    error,
    reload: loadRecipes,
  };
}

// Custom hook for user authentication
export function useAuth() {
  const [user, setUser] = useState<AuthUser | null>(null);
  const [profile, setProfile] = useState<AuthProfile | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const register = useCallback(async (_username: string, _password: string) => {
    setLoading(true);
    setError(null);
    try {
      throw new Error('Registration is handled by WebHatchery login.');
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Registration not available');
      return null;
    } finally {
      setLoading(false);
    }
  }, []);

  const login = useCallback(async (_username: string, _password: string) => {
    setLoading(true);
    setError(null);
    try {
      throw new Error('Login is handled by WebHatchery.');
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Login not available');
      return null;
    } finally {
      setLoading(false);
    }
  }, []);

  const logout = useCallback(async () => {
    setLoading(true);
    try {
      setUser(null);
      setProfile(null);
      localStorage.removeItem('auth-storage');
    } catch (err) {
      console.error('Logout error:', err);
    } finally {
      setLoading(false);
    }
  }, []);

  const loadSession = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const session = await api.auth.session();
      if (session) {
        setUser(session.user);
        setProfile(session.profile);
      } else {
        setUser(null);
        setProfile(null);
      }
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to load session');
      setUser(null);
      setProfile(null);
    } finally {
      setLoading(false);
    }
  }, []);

  // Check for existing user on mount
  useEffect(() => {
    loadSession();
  }, [loadSession]);

  return {
    user,
    profile,
    loading,
    error,
    register,
    login,
    logout,
    refreshSession: loadSession,
    isAuthenticated: !!user,
  };
}

// Custom hook for inventory management
export function useInventory(userId?: number) {
  const [inventory, setInventory] = useState<InventoryItem[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const loadInventory = useCallback(async () => {
    if (!userId) return;

    setLoading(true);
    setError(null);
    try {
      const data = await api.inventory.getUserInventory(userId);
      setInventory(data);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to load inventory');
    } finally {
      setLoading(false);
    }
  }, [userId]);

  const addItem = useCallback(
    async (item: InventoryItem) => {
      if (!userId) return false;

      try {
        const success = await api.inventory.addItem(userId, item);
        if (success) {
          await loadInventory(); // Reload inventory after adding item
        }
        return success;
      } catch (err) {
        setError(err instanceof Error ? err.message : 'Failed to add item');
        return false;
      }
    },
    [userId, loadInventory]
  );

  const removeItem = useCallback(
    async (item: InventoryItem) => {
      if (!userId) return false;

      try {
        const success = await api.inventory.removeItem(userId, item);
        if (success) {
          await loadInventory(); // Reload inventory after removing item
        }
        return success;
      } catch (err) {
        setError(err instanceof Error ? err.message : 'Failed to remove item');
        return false;
      }
    },
    [userId, loadInventory]
  );

  useEffect(() => {
    if (userId) {
      loadInventory();
    }
  }, [userId, loadInventory]);

  return {
    inventory,
    loading,
    error,
    addItem,
    removeItem,
    reload: loadInventory,
  };
}

// Custom hook for crafting
export function useCrafting(userId?: number) {
  const [craftingHistory, setCraftingHistory] = useState<CraftingHistoryEntry[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const loadHistory = useCallback(async () => {
    if (!userId) return;

    setLoading(true);
    setError(null);
    try {
      const data = await api.crafting.getHistory(userId);
      setCraftingHistory(data);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to load crafting history');
    } finally {
      setLoading(false);
    }
  }, [userId]);

  const craft = useCallback(
    async (recipeId: number, materials: CraftingMaterialsPayload) => {
      if (!userId) return null;

      setLoading(true);
      setError(null);
      try {
        const result = await api.crafting.craft({ recipeId, materials });
        if (result) {
          // Reload crafting history after successful craft
          await loadHistory();
        }
        return result;
      } catch (err) {
        setError(err instanceof Error ? err.message : 'Crafting failed');
        return null;
      } finally {
        setLoading(false);
      }
    },
    [userId, loadHistory]
  );

  useEffect(() => {
    if (userId) {
      loadHistory();
    }
  }, [userId, loadHistory]);

  return {
    craftingHistory,
    loading,
    error,
    craft,
    reload: loadHistory,
  };
}

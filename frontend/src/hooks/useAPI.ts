import { useState, useEffect, useCallback } from 'react';
import api, { materialsAPI, recipesAPI, customersAPI, upgradesAPI } from '../api/api';
import { Material, Recipe, Customer, ForgeUpgrade } from '../types/game.d';
import { InventoryItem } from '../types/inventory';
import {
  clearGuestSession,
  getFrontpageToken,
  getGuestSession,
  saveGuestSession,
} from '../auth/storage';
import { useAuthStore } from '../stores/authStore';

type CraftingHistoryEntry = Record<string, unknown>;
type CraftingMaterialsPayload = Array<Record<string, unknown>>;

export function useGameData(enabled: boolean) {
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
      const isHealthy = await api.health.check();
      if (!isHealthy) {
        throw new Error('Backend is not available');
      }

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
      if (import.meta.env.VITE_ENABLE_FALLBACK === 'true') {
        await loadFallbackData();
      }
    } finally {
      setLoading(false);
    }
  }, []);

  const loadFallbackData = async () => {
    try {
      const { MATERIALS, RECIPES, CUSTOMERS, forgeUpgrades } = await import('../constants/gameData');
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
    if (enabled) {
      loadData();
    }
  }, [enabled, loadData]);

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

export function useAuth() {
  const user = useAuthStore(state => state.user);
  const profile = useAuthStore(state => state.profile);
  const loginUrl = useAuthStore(state => state.loginUrl);
  const authMode = useAuthStore(state => state.authMode);
  const setSession = useAuthStore(state => state.setSession);
  const setGuestSession = useAuthStore(state => state.setGuestSession);
  const setStoredLoginUrl = useAuthStore(state => state.setLoginUrl);
  const clearSession = useAuthStore(state => state.clearSession);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const logout = useCallback(async () => {
    setLoading(true);
    try {
      clearGuestSession();
      clearSession();
    } catch (err) {
      console.error('Logout error:', err);
    } finally {
      setLoading(false);
    }
  }, [clearSession]);

  const loadSession = useCallback(async () => {
    setLoading(true);
    setError(null);

    try {
      const params = new URLSearchParams(window.location.search);
      const frontpageToken = getFrontpageToken();
      const guestSession = getGuestSession();

      if (guestSession?.token && frontpageToken) {
        const linked = await api.auth.linkGuest(guestSession.token, frontpageToken);
        if (linked) {
          clearGuestSession();
          setSession(linked.user, linked.profile, 'frontpage');
          const nextQuery = params.toString();
          window.history.replaceState({}, document.title, `${window.location.pathname}${nextQuery ? `?${nextQuery}` : ''}${window.location.hash}`);
          setLoading(false);
          return;
        }
      }

      if (guestSession?.token) {
        const session = await api.auth.session(guestSession.token);
        if (session) {
          const storedGuestSession = { token: guestSession.token, user: session.user, profile: session.profile };
          saveGuestSession(storedGuestSession);
          setSession(session.user, session.profile, 'guest');
          setLoading(false);
          return;
        }
      }

      if (frontpageToken) {
        const session = await api.auth.session(frontpageToken);
        if (session) {
          setSession(session.user, session.profile, 'frontpage');
          setLoading(false);
          return;
        }
      }

      clearSession();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to load session');
      clearSession();
    } finally {
      setLoading(false);
    }
  }, [clearSession, setSession]);

  useEffect(() => {
    loadSession();
  }, [loadSession]);

  useEffect(() => {
    const handleLoginRequired = (event: Event) => {
      const customEvent = event as CustomEvent<{ loginUrl?: string }>;
      setStoredLoginUrl(customEvent.detail?.loginUrl ?? null);
    };

    window.addEventListener('webhatchery:login-required', handleLoginRequired as EventListener);
    return () => window.removeEventListener('webhatchery:login-required', handleLoginRequired as EventListener);
  }, [setStoredLoginUrl]);

  const continueAsGuest = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const session = await api.auth.guestSession();
      if (!session) {
        throw new Error('Failed to create guest session');
      }

      const guestSession = {
        token: session.token,
        user: session.user,
        profile: session.profile,
      };
      saveGuestSession(guestSession);
      setGuestSession(guestSession);
      setSession(session.user, session.profile, 'guest');
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to create guest session');
    } finally {
      setLoading(false);
    }
  }, [setGuestSession, setSession]);

  const getLinkAccountUrl = useCallback(() => {
    const baseLoginUrl = loginUrl || import.meta.env.VITE_WEB_HATCHERY_LOGIN_URL;
    if (!baseLoginUrl) {
      throw new Error('Missing required environment variable: VITE_WEB_HATCHERY_LOGIN_URL');
    }

    const url = new URL(baseLoginUrl, window.location.origin);
    url.searchParams.set('return_to', window.location.href);

    return url.toString();
  }, [loginUrl]);

  return {
    user,
    profile,
    loading,
    error,
    logout,
    refreshSession: loadSession,
    continueAsGuest,
    getLinkAccountUrl,
    authMode,
    isAuthenticated: !!user,
  };
}

export function useInventory() {
  const isAuthenticated = useAuthStore(state => state.user !== null);
  const [inventory, setInventory] = useState<InventoryItem[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const loadInventory = useCallback(async () => {
    if (!isAuthenticated) return;

    setLoading(true);
    setError(null);
    try {
      const data = await api.inventory.getUserInventory();
      setInventory(data);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to load inventory');
    } finally {
      setLoading(false);
    }
  }, [isAuthenticated]);

  const addItem = useCallback(
    async (item: InventoryItem) => {
      if (!isAuthenticated) return false;

      try {
        const success = await api.inventory.addItem(item);
        if (success) {
          await loadInventory();
        }
        return success;
      } catch (err) {
        setError(err instanceof Error ? err.message : 'Failed to add item');
        return false;
      }
    },
    [isAuthenticated, loadInventory]
  );

  const removeItem = useCallback(
    async (item: InventoryItem) => {
      if (!isAuthenticated) return false;

      try {
        const success = await api.inventory.removeItem(item);
        if (success) {
          await loadInventory();
        }
        return success;
      } catch (err) {
        setError(err instanceof Error ? err.message : 'Failed to remove item');
        return false;
      }
    },
    [isAuthenticated, loadInventory]
  );

  useEffect(() => {
    if (isAuthenticated) {
      loadInventory();
    }
  }, [isAuthenticated, loadInventory]);

  return {
    inventory,
    loading,
    error,
    addItem,
    removeItem,
    reload: loadInventory,
  };
}

export function useCrafting() {
  const isAuthenticated = useAuthStore(state => state.user !== null);
  const [craftingHistory, setCraftingHistory] = useState<CraftingHistoryEntry[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const loadHistory = useCallback(async () => {
    if (!isAuthenticated) return;

    setLoading(true);
    setError(null);
    try {
      const data = await api.crafting.getHistory();
      setCraftingHistory(data);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to load crafting history');
    } finally {
      setLoading(false);
    }
  }, [isAuthenticated]);

  const craft = useCallback(
    async (recipeId: number, materials: CraftingMaterialsPayload) => {
      if (!isAuthenticated) return null;

      setLoading(true);
      setError(null);
      try {
        const result = await api.crafting.craft({ recipeId, materials });
        if (result) {
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
    [isAuthenticated, loadHistory]
  );

  useEffect(() => {
    if (isAuthenticated) {
      loadHistory();
    }
  }, [isAuthenticated, loadHistory]);

  return {
    craftingHistory,
    loading,
    error,
    craft,
    reload: loadHistory,
  };
}

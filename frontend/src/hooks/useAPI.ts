import { useState, useEffect, useCallback } from 'react';
import api, { materialsAPI, recipesAPI, customersAPI, upgradesAPI } from '../api/api';
import { Material, Recipe, Customer, ForgeUpgrade } from '../types/game.d';
import { InventoryItem } from '../types/inventory';
import {
  AuthProfile,
  AuthUser,
  clearGuestSession,
  getFrontpageToken,
  getGuestSession,
  saveGuestSession,
} from '../auth/storage';

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

const migrateGuestLocalState = (guestUserId: number, targetUserId: number): void => {
  const keyPairs = [
    [`bf_materials_${guestUserId}`, `bf_materials_${targetUserId}`],
    [`bf_inventory_${guestUserId}`, `bf_inventory_${targetUserId}`],
    [`bf_current_customer_${guestUserId}`, `bf_current_customer_${targetUserId}`],
  ];

  keyPairs.forEach(([guestKey, targetKey]) => {
    const guestRaw = localStorage.getItem(guestKey);
    if (!guestRaw) {
      return;
    }

    const targetRaw = localStorage.getItem(targetKey);
    if (!targetRaw) {
      localStorage.setItem(targetKey, guestRaw);
    } else {
      try {
        const guestParsed = JSON.parse(guestRaw);
        const targetParsed = JSON.parse(targetRaw);

        if (Array.isArray(guestParsed) && Array.isArray(targetParsed)) {
          localStorage.setItem(targetKey, JSON.stringify([...targetParsed, ...guestParsed]));
        } else if (
          guestParsed &&
          typeof guestParsed === 'object' &&
          targetParsed &&
          typeof targetParsed === 'object'
        ) {
          localStorage.setItem(targetKey, JSON.stringify({ ...guestParsed, ...targetParsed }));
        }
      } catch {
        localStorage.setItem(targetKey, guestRaw);
      }
    }

    localStorage.removeItem(guestKey);
  });
};

export function useAuth() {
  const [user, setUser] = useState<AuthUser | null>(null);
  const [profile, setProfile] = useState<AuthProfile | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [loginUrl, setLoginUrl] = useState<string | null>(null);
  const [authMode, setAuthMode] = useState<'frontpage' | 'guest' | null>(null);

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
      setAuthMode(null);
      clearGuestSession();
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
      const params = new URLSearchParams(window.location.search);
      const linkingGuestUserId = Number(params.get('guest_user_id') || '0');
      const frontpageToken = getFrontpageToken();
      const guestSession = getGuestSession();

      if (linkingGuestUserId > 0 && frontpageToken) {
        const linked = await api.auth.linkGuest(linkingGuestUserId, frontpageToken);
        if (linked) {
          migrateGuestLocalState(linkingGuestUserId, Number(linked.user.id));
          clearGuestSession();
          setUser(linked.user);
          setProfile(linked.profile);
          setAuthMode('frontpage');
          params.delete('guest_user_id');
          const nextQuery = params.toString();
          window.history.replaceState({}, document.title, `${window.location.pathname}${nextQuery ? `?${nextQuery}` : ''}${window.location.hash}`);
          setLoading(false);
          return;
        }
      }

      if (guestSession?.token) {
        const session = await api.auth.session(guestSession.token);
        if (session) {
          saveGuestSession({ token: guestSession.token, user: session.user, profile: session.profile });
          setUser(session.user);
          setProfile(session.profile);
          setAuthMode('guest');
          setLoading(false);
          return;
        }
      }

      if (frontpageToken) {
        const session = await api.auth.session(frontpageToken);
        if (session) {
          setUser(session.user);
          setProfile(session.profile);
          setAuthMode('frontpage');
          setLoading(false);
          return;
        }
      }

      setUser(null);
      setProfile(null);
      setAuthMode(null);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to load session');
      setUser(null);
      setProfile(null);
      setAuthMode(null);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    loadSession();
  }, [loadSession]);

  useEffect(() => {
    const handleLoginRequired = (event: Event) => {
      const customEvent = event as CustomEvent<{ loginUrl?: string }>;
      setLoginUrl(customEvent.detail?.loginUrl ?? null);
    };

    window.addEventListener('webhatchery:login-required', handleLoginRequired as EventListener);
    return () => window.removeEventListener('webhatchery:login-required', handleLoginRequired as EventListener);
  }, []);

  const continueAsGuest = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const session = await api.auth.guestSession();
      if (!session) {
        throw new Error('Failed to create guest session');
      }

      saveGuestSession({
        token: session.token,
        user: session.user,
        profile: session.profile,
      });
      setUser(session.user);
      setProfile(session.profile);
      setAuthMode('guest');
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to create guest session');
    } finally {
      setLoading(false);
    }
  }, []);

  const getLinkAccountUrl = useCallback(() => {
    const baseLoginUrl = loginUrl || import.meta.env.VITE_WEB_HATCHERY_LOGIN_URL;
    if (!baseLoginUrl) {
      throw new Error('Missing required environment variable: VITE_WEB_HATCHERY_LOGIN_URL');
    }

    const url = new URL(baseLoginUrl, window.location.origin);
    url.searchParams.set('return_to', window.location.href);

    if (user?.is_guest && user.id) {
      url.searchParams.set('guest_user_id', String(user.id));
    }

    return url.toString();
  }, [loginUrl, user]);

  return {
    user,
    profile,
    loading,
    error,
    register,
    login,
    logout,
    refreshSession: loadSession,
    continueAsGuest,
    getLinkAccountUrl,
    authMode,
    isAuthenticated: !!user,
  };
}

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
          await loadInventory();
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
          await loadInventory();
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

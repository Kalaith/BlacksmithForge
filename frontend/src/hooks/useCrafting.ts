import { useState, useEffect, useCallback, useMemo } from 'react';
import { craftingAPI, inventoryAPI } from '../api/api';
import { useAuthContext, useGameDataContext } from '../providers/GameDataProvider';
import {
  Recipe,
  CraftingState,
  CraftingValidation,
  HammerHitResult,
  CraftingResult,
} from '../types';

const maxHammerClicksDefault = 4;

/**
 * Custom hook for managing crafting workflow with backend business logic
 * This hook is purely for state management and API communication
 */
export function useCrafting(selectedRecipeName: string | null) {
  const { user, isAuthenticated } = useAuthContext();
  const { recipes, loading: dataLoading } = useGameDataContext();

  // Local state for UI only
  const [craftingState, setCraftingState] = useState<CraftingState>({
    hammerClicks: 0,
    hammerAccuracy: 0,
    craftingStarted: false,
    result: null,
    loading: false,
    error: null,
    sessionId: null,
    maxHammerClicks: maxHammerClicksDefault,
  });

  // Backend validation and user data
  const [validation, setValidation] = useState<CraftingValidation | null>(null);

  // Find the selected recipe
  const recipe = useMemo(() => {
    if (!selectedRecipeName) return null;
    return recipes.find((r: Recipe) => r.name === selectedRecipeName) || null;
  }, [recipes, selectedRecipeName]);
  const recipeId = recipe?.id ?? null;

  const setCraftingPartial = useCallback((updates: Partial<CraftingState>) => {
    setCraftingState(prev => ({ ...prev, ...updates }));
  }, []);

  const setError = useCallback(
    (message: string) => {
      setCraftingPartial({ error: message, loading: false });
    },
    [setCraftingPartial]
  );

  /**
   * Load user inventory - simple state fetch
   */
  const loadUserData = useCallback(async () => {
    if (!isAuthenticated || !user?.id) return;

    try {
      await inventoryAPI.getUserInventory(user.id);
    } catch (error) {
      console.error('Failed to load user data:', error);
    }
  }, [user?.id, isAuthenticated]);

  /**
   * Validate crafting ability with backend
   */
  const validateCrafting = useCallback(async () => {
    if (!isAuthenticated || !user?.id || !recipeId) return;

    try {
      const validationResult = await craftingAPI.validateCrafting(user.id, recipeId);
      setValidation(validationResult);
    } catch (error) {
      console.error('Failed to validate crafting:', error);
      setValidation(null);
    }
  }, [user?.id, recipeId, isAuthenticated]);

  /**
   * Start crafting session - all business logic handled by backend
   */
  const handleStartCrafting = useCallback(async () => {
    if (!isAuthenticated || !user?.id || !recipeId || !validation?.can_craft) return;

    setCraftingState(prev => ({
      ...prev,
      craftingStarted: true,
      loading: true,
      error: null,
      hammerClicks: 0,
      hammerAccuracy: 0,
      result: null,
      maxHammerClicks: maxHammerClicksDefault,
    }));

    try {
      const sessionData = await craftingAPI.startCrafting({
        recipeId: recipeId,
      });

      if (sessionData?.session_id) {
        setCraftingPartial({
          sessionId: sessionData.session_id,
          maxHammerClicks: sessionData.max_hammer_clicks ?? maxHammerClicksDefault,
          loading: false,
        });
      } else {
        throw new Error('Failed to start crafting session');
      }
    } catch (error) {
      setCraftingPartial({
        loading: false,
        error: error instanceof Error ? error.message : 'Failed to start crafting',
        craftingStarted: false,
      });
    }
  }, [user?.id, recipeId, validation?.can_craft, isAuthenticated, setCraftingPartial]);

  /**
   * Complete crafting - all quality calculation and rewards handled by backend
   */
  const handleCompleteCrafting = useCallback(
    async (totalAccuracy: number) => {
      if (!craftingState.sessionId || !isAuthenticated || !user?.id) return;

      setCraftingPartial({ loading: true });

      try {
        const completionResult: CraftingResult = await craftingAPI.completeCrafting({
          craftingSessionId: craftingState.sessionId,
          totalAccuracy: totalAccuracy,
        });

        if (completionResult?.success) {
          setCraftingPartial({
            result: completionResult,
            loading: false,
          });

          // Refresh user data to show new items
          await loadUserData();

          // Refresh validation to update material counts
          await validateCrafting();
        } else {
          throw new Error('Failed to complete crafting');
        }
      } catch (error) {
        setError(error instanceof Error ? error.message : 'Failed to complete crafting');
      }
    },
    [
      craftingState.sessionId,
      isAuthenticated,
      user?.id,
      loadUserData,
      validateCrafting,
      setCraftingPartial,
      setError,
    ]
  );

  /**
   * Reset crafting state
   */
  const resetCrafting = useCallback(() => {
    setCraftingState({
      hammerClicks: 0,
      hammerAccuracy: 0,
      craftingStarted: false,
      result: null,
      loading: false,
      error: null,
      sessionId: null,
      maxHammerClicks: maxHammerClicksDefault,
    });
  }, []);

  /**
   * Handle hammer hit - business logic on backend
   */
  const handleHammer = useCallback(async () => {
    if (!craftingState.craftingStarted || !craftingState.sessionId || !isAuthenticated || !user?.id)
      return;

    try {
      // Simulate hit success/failure on frontend for immediate feedback
      const hitSuccess = Math.random() > 0.25;

      // Send to backend for authoritative processing
      const hitResult: HammerHitResult = await craftingAPI.processHammerHit({
        craftingSessionId: craftingState.sessionId,
        accuracy: hitSuccess,
      });

      if (hitResult) {
        setCraftingPartial({
          hammerClicks: hitResult.hammer_clicks,
          hammerAccuracy: hitResult.total_accuracy,
          maxHammerClicks: hitResult.max_clicks ?? craftingState.maxHammerClicks,
        });

        // If crafting is complete, finalize it
        if (hitResult.is_complete) {
          await handleCompleteCrafting(hitResult.total_accuracy);
        }
      }
    } catch (error) {
      setError(error instanceof Error ? error.message : 'Hammer hit failed');
    }
  }, [
    craftingState.craftingStarted,
    craftingState.sessionId,
    craftingState.maxHammerClicks,
    user?.id,
    isAuthenticated,
    setCraftingPartial,
    handleCompleteCrafting,
    setError,
  ]);

  // Load data when dependencies change
  useEffect(() => {
    if (isAuthenticated && user?.id) {
      loadUserData();
    }
  }, [isAuthenticated, user?.id, loadUserData]);

  // Validate crafting when recipe or user changes
  useEffect(() => {
    if (isAuthenticated && user?.id && recipeId) {
      validateCrafting();
    }
  }, [isAuthenticated, user?.id, recipeId, validateCrafting]);

  return {
    // Recipe and validation data
    recipe,
    validation,
    canCraft: validation?.can_craft || false,

    // Crafting state
    hammerClicks: craftingState.hammerClicks,
    hammerAccuracy: craftingState.hammerAccuracy,
    craftingStarted: craftingState.craftingStarted,
    result: craftingState.result,
    loading: craftingState.loading || dataLoading,
    error: craftingState.error,

    // Actions
    handleStartCrafting,
    handleHammer,
    resetCrafting,

    // Helper computed values
    isComplete: craftingState.result !== null,
    maxHammerClicks: craftingState.maxHammerClicks,
    progressPercentage:
      craftingState.maxHammerClicks > 0
        ? (craftingState.hammerClicks / craftingState.maxHammerClicks) * 100
        : 0,
  };
}

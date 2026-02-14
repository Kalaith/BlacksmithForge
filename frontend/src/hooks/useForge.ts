import { useCallback, useState, useEffect, useMemo } from 'react';
import { craftingAPI } from '../api/api';
import { useAuthContext, useGameDataContext } from '../providers/GameDataProvider';
import { ForgeState, Recipe } from '../types';

/**
 * Custom hook for forge management with backend validation
 * Handles forge state and recipe selection with backend-driven logic
 */
export function useForge() {
  const { user, isAuthenticated } = useAuthContext();
  const { recipes, loading: dataLoading } = useGameDataContext();

  const [forgeState, setForgeState] = useState<ForgeState>({
    forgeLit: false,
    selectedRecipe: null,
    availableRecipes: [],
    materials: {},
    loading: false,
    error: null,
  });

  const [recipeValidation, setRecipeValidation] = useState<Record<string, boolean>>({});
  const recipesWithIds = useMemo(() => recipes.filter(recipe => recipe.id), [recipes]);

  const setForgePartial = useCallback((updates: Partial<ForgeState>) => {
    setForgeState(prev => ({ ...prev, ...updates }));
  }, []);

  /**
   * Light the forge - simple state management
   */
  const handleLightForge = useCallback(() => {
    setForgePartial({ forgeLit: true, error: null });
  }, [setForgePartial]);

  /**
   * Select a recipe - no business logic, just UI state
   */
  const handleSelectRecipe = useCallback(
    (recipeName: string) => {
      setForgePartial({ selectedRecipe: recipeName, error: null });
    },
    [setForgePartial]
  );

  /**
   * Validate recipes with backend - checks materials and requirements
   */
  const validateRecipes = useCallback(async () => {
    if (!isAuthenticated || !user?.id || recipesWithIds.length === 0) {
      setRecipeValidation({});
      setForgePartial({ availableRecipes: [], loading: false });
      return;
    }

    setForgePartial({ loading: true });

    try {
      const validationPromises = recipesWithIds.map(async (recipe: Recipe) => {
        try {
          const validation = await craftingAPI.validateCrafting(user.id, recipe.id as number);
          return {
            recipeName: recipe.name,
            canCraft: validation?.can_craft || false,
          };
        } catch {
          return {
            recipeName: recipe.name,
            canCraft: false,
          };
        }
      });

      const validationResults = await Promise.all(validationPromises);

      const validationMap: Record<string, boolean> = {};
      validationResults.forEach(result => {
        validationMap[result.recipeName] = result.canCraft;
      });

      setRecipeValidation(validationMap);
      setForgePartial({
        loading: false,
        availableRecipes: validationResults.filter(r => r.canCraft).map(r => r.recipeName),
      });
    } catch (error) {
      setForgePartial({
        loading: false,
        error: error instanceof Error ? error.message : 'Failed to validate recipes',
      });
    }
  }, [isAuthenticated, user?.id, recipesWithIds, setForgePartial]);

  /**
   * Get crafting ability for a specific recipe - uses backend validation
   */
  const getCanCraft = useCallback(
    (recipeName: string) => {
      return recipeValidation[recipeName] || false;
    },
    [recipeValidation]
  );

  /**
   * Refresh forge data
   */
  const refreshForgeData = useCallback(() => {
    validateRecipes();
  }, [validateRecipes]);

  /**
   * Clear any errors
   */
  const clearError = useCallback(() => {
    setForgePartial({ error: null });
  }, [setForgePartial]);

  /**
   * Reset forge state
   */
  const resetForge = useCallback(() => {
    setForgeState({
      forgeLit: false,
      selectedRecipe: null,
      availableRecipes: [],
      materials: {},
      loading: false,
      error: null,
    });
    setRecipeValidation({});
  }, []);

  // Validate recipes when user or recipes change (or when recipes are available in dev mode)
  useEffect(() => {
    if (recipesWithIds.length > 0) {
      validateRecipes();
    }
  }, [recipesWithIds.length, validateRecipes]);

  return {
    // Forge state
    forgeLit: forgeState.forgeLit,
    selectedRecipe: forgeState.selectedRecipe,
    availableRecipes: forgeState.availableRecipes,
    unlockedRecipes: forgeState.availableRecipes,
    materials: forgeState.materials,
    loading: forgeState.loading || dataLoading,
    error: forgeState.error,
    recipes: recipesWithIds,

    // Recipe validation
    recipeValidation,

    // Actions
    handleLightForge,
    handleSelectRecipe,
    getCanCraft,
    refreshForgeData,
    clearError,
    resetForge,

    // Helper computed values
    hasSelectedRecipe: forgeState.selectedRecipe !== null,
    canCraftSelected: forgeState.selectedRecipe ? getCanCraft(forgeState.selectedRecipe) : false,
    availableRecipeCount: forgeState.availableRecipes.length,
  };
}

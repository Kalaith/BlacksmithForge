import { CraftingResult, CraftingValidation, HammerHitResult } from '../../types';
import { apiClient } from '../client';
import { BackendRecipe } from '../backendTypes';
import { transformBackendRecipe } from '../transforms';

type BackendCraftingValidation = Omit<CraftingValidation, 'recipe'> & {
  recipe: BackendRecipe | null;
};

const requireData = <T>(success: boolean, data: T | undefined, message?: string): T => {
  if (!success || data === undefined) {
    throw new Error(message || 'Backend request failed');
  }
  return data;
};

const transformValidation = (validation: BackendCraftingValidation): CraftingValidation => ({
  ...validation,
  recipe: validation.recipe ? transformBackendRecipe(validation.recipe) : null,
});

export const craftingAPI = {
  async validateCrafting(recipeId: number): Promise<CraftingValidation> {
    const response = await apiClient.get<BackendCraftingValidation>(`/crafting/validate/${recipeId}`);
    return transformValidation(requireData(response.success, response.data, response.message));
  },

  async startCrafting(payload: {
    recipeId: number;
  }): Promise<{ session_id: number; max_hammer_clicks: number }> {
    const response = await apiClient.post<{ session_id: number; max_hammer_clicks: number }>(
      '/crafting/start',
      { recipe_id: payload.recipeId }
    );
    return requireData(response.success, response.data, response.message);
  },

  async processHammerHit(payload: {
    craftingSessionId: number;
    accuracy: boolean;
  }): Promise<HammerHitResult> {
    const response = await apiClient.post<HammerHitResult>('/crafting/hammer-hit', {
      crafting_session_id: payload.craftingSessionId,
      accuracy: payload.accuracy,
    });
    return requireData(response.success, response.data, response.message);
  },

  async completeCrafting(payload: {
    craftingSessionId: number;
    totalAccuracy: number;
  }): Promise<CraftingResult> {
    const response = await apiClient.post<CraftingResult>('/crafting/complete', {
      crafting_session_id: payload.craftingSessionId,
      total_accuracy: payload.totalAccuracy,
    });
    return requireData(response.success, response.data, response.message);
  },

  async getHistory(): Promise<Array<Record<string, unknown>>> {
    const response = await apiClient.get<Array<Record<string, unknown>>>('/crafting/history');
    return response.success ? response.data ?? [] : [];
  },

  async craft(payload: {
    recipeId: number;
    materials: Array<Record<string, unknown>>;
  }): Promise<Record<string, unknown> | null> {
    const response = await apiClient.post<Record<string, unknown>>('/crafting/craft', {
      recipe_id: payload.recipeId,
      materials_used: payload.materials,
    });
    return response.success ? response.data ?? null : null;
  },
};

import { RECIPES } from '../../constants/gameData';
import { CraftingResult, CraftingValidation, HammerHitResult } from '../../types';
import { materialsAPI } from './materials';

const sessions = new Map<number, { clicks: number; accuracy: number; maxClicks: number }>();

const qualityFromAccuracy = (accuracy: number): CraftingResult['quality'] => {
  if (accuracy >= 80) return 'Excellent';
  if (accuracy >= 60) return 'Good';
  if (accuracy >= 40) return 'Fair';
  return 'Poor';
};

export const craftingAPI = {
  async validateCrafting(userId: number, recipeId: number): Promise<CraftingValidation> {
    const recipe = RECIPES.find((r, i) => (r.id ?? i + 1) === recipeId) ?? null;
    const userMaterials = await materialsAPI.getUserMaterials(userId);

    if (!recipe) {
      return {
        can_craft: false,
        recipe: null,
        user_materials: userMaterials,
        missing_materials: [],
        message: 'Recipe not found',
      };
    }

    const missing = Object.entries(recipe.materials)
      .map(([name, required]) => {
        const available = userMaterials[name] ?? 0;
        return {
          name,
          required,
          available,
          missing: Math.max(0, required - available),
        };
      })
      .filter(m => m.missing > 0);

    return {
      can_craft: missing.length === 0,
      recipe,
      user_materials: userMaterials,
      missing_materials: missing,
      message: missing.length === 0 ? 'Ready to craft' : 'Missing materials',
    };
  },

  async startCrafting(payload: {
    recipeId: number;
  }): Promise<{ session_id: number; max_hammer_clicks: number }> {
    const sessionId = Date.now();
    sessions.set(sessionId, { clicks: 0, accuracy: 0, maxClicks: 4 });
    return { session_id: sessionId, max_hammer_clicks: 4 };
  },

  async processHammerHit(payload: {
    craftingSessionId: number;
    accuracy: boolean;
  }): Promise<HammerHitResult> {
    const session = sessions.get(payload.craftingSessionId) ?? {
      clicks: 0,
      accuracy: 0,
      maxClicks: 4,
    };
    const increase = payload.accuracy ? 25 : 8;
    const nextClicks = session.clicks + 1;
    const nextAccuracy = Math.min(100, session.accuracy + increase);
    sessions.set(payload.craftingSessionId, {
      clicks: nextClicks,
      accuracy: nextAccuracy,
      maxClicks: session.maxClicks,
    });

    return {
      hit_success: payload.accuracy,
      accuracy_increase: increase,
      total_accuracy: nextAccuracy,
      hammer_clicks: nextClicks,
      max_clicks: session.maxClicks,
      is_complete: nextClicks >= session.maxClicks,
      remaining_clicks: Math.max(0, session.maxClicks - nextClicks),
    };
  },

  async completeCrafting(payload: {
    craftingSessionId: number;
    totalAccuracy: number;
  }): Promise<CraftingResult> {
    const quality = qualityFromAccuracy(payload.totalAccuracy);
    sessions.delete(payload.craftingSessionId);
    return {
      success: true,
      quality,
      item: {
        name: 'Forged Item',
        icon: '⚒️',
        quality,
        value: Math.max(10, payload.totalAccuracy * 2),
        type: 'weapon',
      },
      message: `Crafting completed with ${quality} quality`,
    };
  },

  async getHistory(_userId: number): Promise<Array<Record<string, unknown>>> {
    return [];
  },

  async craft(payload: {
    recipeId: number;
    materials: Array<Record<string, unknown>>;
  }): Promise<Record<string, unknown> | null> {
    void payload;
    return { success: true };
  },
};

/**
 * Forge state interface
 */
export interface ForgeState {
  forgeLit: boolean;
  selectedRecipe: string | null;
  availableRecipes: string[];
  materials: Record<string, number>;
  loading: boolean;
  error: string | null;
}

/**
 * Recipe validation result from backend
 */
export interface RecipeValidation {
  can_craft: boolean;
  missing_materials?: Array<{
    material: string;
    required: number;
    available: number;
  }>;
  reason?: string;
}

/**
 * Hook return type for useForge
 */
export interface UseForgeReturn {
  // State
  forgeLit: boolean;
  selectedRecipe: string | null;
  availableRecipes: string[];
  materials: Record<string, number>;
  loading: boolean;
  error: string | null;

  // Recipe validation
  recipeValidation: Record<string, boolean>;

  // Actions
  handleLightForge: () => void;
  handleSelectRecipe: (recipeName: string) => void;
  getCanCraft: (recipeName: string) => boolean;
  refreshForgeData: () => void;
  clearError: () => void;
  resetForge: () => void;

  // Computed values
  hasSelectedRecipe: boolean;
  canCraftSelected: boolean;
  availableRecipeCount: number;
}

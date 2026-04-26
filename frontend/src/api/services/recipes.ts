import { apiClient } from '../client';
import { BackendRecipe, Recipe } from '../backendTypes';
import { transformBackendRecipe } from '../transforms';

const requireData = <T>(success: boolean, data: T | undefined, message?: string): T => {
  if (!success || data === undefined) {
    throw new Error(message || 'Backend request failed');
  }
  return data;
};

const uniqueRecipes = (recipes: Recipe[]): Recipe[] =>
  Array.from(new Map(recipes.map(recipe => [recipe.name, recipe])).values());

export const recipesAPI = {
  async getAll(): Promise<Recipe[]> {
    const response = await apiClient.get<BackendRecipe[]>('/recipes');
    return uniqueRecipes(
      requireData(response.success, response.data, response.message).map(transformBackendRecipe)
    );
  },

  async getById(id: number): Promise<Recipe | null> {
    const response = await apiClient.get<BackendRecipe>(`/recipes/${id}`);
    if (!response.success || !response.data) {
      return null;
    }
    return transformBackendRecipe(response.data);
  },

  async create(recipe: Partial<BackendRecipe>): Promise<Recipe | null> {
    const response = await apiClient.post<BackendRecipe>('/recipes', recipe);
    if (!response.success || !response.data) {
      return null;
    }
    return transformBackendRecipe(response.data);
  },
};

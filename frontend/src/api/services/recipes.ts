import { apiClient } from '../client';
import { BackendRecipe, Recipe } from '../backendTypes';
import { transformBackendRecipe } from '../transforms';

export const recipesAPI = {
  async getAll(): Promise<Recipe[]> {
    try {
      const response = await apiClient.get<BackendRecipe[]>('/recipes');
      if (response.success && response.data) {
        return response.data.map(transformBackendRecipe);
      }
      return [];
    } catch (error) {
      console.error('Failed to fetch recipes:', error);
      return [];
    }
  },

  async getById(id: number): Promise<Recipe | null> {
    try {
      const response = await apiClient.get<BackendRecipe>(`/recipes/${id}`);
      if (response.success && response.data) {
        return transformBackendRecipe(response.data);
      }
      return null;
    } catch (error) {
      console.error('Failed to fetch recipe:', error);
      return null;
    }
  },

  async create(recipe: Partial<BackendRecipe>): Promise<Recipe | null> {
    try {
      const response = await apiClient.post<BackendRecipe>('/recipes', recipe);
      if (response.success && response.data) {
        return transformBackendRecipe(response.data);
      }
      return null;
    } catch (error) {
      console.error('Failed to create recipe:', error);
      return null;
    }
  },
};

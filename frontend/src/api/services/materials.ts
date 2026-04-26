import { Material } from '../../types/game.d';
import { BackendMaterial } from '../backendTypes';
import { apiClient } from '../client';
import { transformBackendMaterial } from '../transforms';

const requireData = <T>(success: boolean, data: T | undefined, message?: string): T => {
  if (!success || data === undefined) {
    throw new Error(message || 'Backend request failed');
  }
  return data;
};

const uniqueByName = (materials: Material[]): Material[] =>
  Array.from(new Map(materials.map(material => [material.name, material])).values());

export const materialsAPI = {
  async getAll(): Promise<Material[]> {
    const response = await apiClient.get<BackendMaterial[]>('/materials');
    return uniqueByName(
      requireData(response.success, response.data, response.message).map(transformBackendMaterial)
    );
  },

  async getByType(type: string): Promise<Material[]> {
    const response = await apiClient.get<BackendMaterial[]>(`/materials/type/${encodeURIComponent(type)}`);
    return uniqueByName(
      requireData(response.success, response.data, response.message).map(transformBackendMaterial)
    );
  },

  async getByRarity(rarity: string): Promise<Material[]> {
    const response = await apiClient.get<BackendMaterial[]>(
      `/materials/rarity/${encodeURIComponent(rarity)}`
    );
    return uniqueByName(
      requireData(response.success, response.data, response.message).map(transformBackendMaterial)
    );
  },

  async getUserMaterials(userId: number): Promise<Record<string, number>> {
    const response = await apiClient.get<Record<string, number>>(`/materials/user/${userId}`);
    return requireData(response.success, response.data, response.message);
  },

  async purchaseMaterial(materialId: number, quantity: number): Promise<boolean> {
    const response = await apiClient.post<unknown>('/materials/purchase', {
      material_id: materialId,
      quantity,
    });
    return response.success;
  },
};

import { InventoryItem } from '../../types/inventory';
import { apiClient } from '../client';

export const inventoryAPI = {
  async getUserInventory(userId: number): Promise<InventoryItem[]> {
    const response = await apiClient.get<InventoryItem[]>(`/inventory/${userId}`);
    return response.success ? response.data ?? [] : [];
  },

  async addItem(userId: number, item: InventoryItem): Promise<boolean> {
    const response = await apiClient.post<{ item_id: number | string }>(
      `/inventory/${userId}/add`,
      item
    );
    return response.success;
  },

  async removeItem(userId: number, item: InventoryItem): Promise<boolean> {
    const response = await apiClient.post<unknown>(`/inventory/${userId}/remove`, {
      item_id: item.id,
    });
    return response.success;
  },
};

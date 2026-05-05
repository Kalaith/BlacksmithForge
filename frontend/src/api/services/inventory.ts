import { InventoryItem } from '../../types/inventory';
import { apiClient } from '../client';

export const inventoryAPI = {
  async getUserInventory(): Promise<InventoryItem[]> {
    const response = await apiClient.get<InventoryItem[]>('/inventory');
    return response.success ? response.data ?? [] : [];
  },

  async addItem(item: InventoryItem): Promise<boolean> {
    const response = await apiClient.post<{ item_id: number | string }>('/inventory/add', item);
    return response.success;
  },

  async removeItem(item: InventoryItem): Promise<boolean> {
    const response = await apiClient.post<unknown>('/inventory/remove', {
      item_id: item.id,
    });
    return response.success;
  },
};
